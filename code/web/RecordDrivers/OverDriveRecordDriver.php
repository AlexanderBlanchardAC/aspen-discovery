<?php

require_once ROOT_DIR . '/RecordDrivers/RecordInterface.php';
require_once ROOT_DIR . '/RecordDrivers/GroupedWorkSubDriver.php';

class OverDriveRecordDriver extends GroupedWorkSubDriver {
	private $id;
	//This will be either blank or kindle for now
	private $subSource;
	/** @var OverDriveAPIProduct */
	private $overDriveProduct;
	/** @var  OverDriveAPIProductMetaData */
	private $overDriveMetaData;
	private $valid;
	private $isbns = null;
	private $upcs = null;
	private $items;

	/**
	 * The Grouped Work that this record is connected to
	 * @var  GroupedWork
	 */
	protected $groupedWork;
	protected $groupedWorkDriver = null;

	/**
	 * Constructor.  We build the object using all the data retrieved
	 * from the (Solr) index.  Since we have to
	 * make a search call to find out which record driver to construct,
	 * we will already have this data available, so we might as well
	 * just pass it into the constructor.
	 *
	 * @param string $recordId The id of the record within OverDrive.
	 * @param GroupedWork $groupedWork ;
	 * @access  public
	 */
	public function __construct($recordId, $groupedWork = null) {
		if (is_string($recordId)) {
			//The record is the identifier for the overdrive title
			//Check to see if we have a subSource
			if (strpos($recordId, ':') > 0) {
				[
					$this->subSource,
					$recordId,
				] = explode(':', $recordId);
			}
			$this->id = $recordId;
			require_once ROOT_DIR . '/sys/OverDrive/OverDriveAPIProduct.php';
			$this->overDriveProduct = new OverDriveAPIProduct();
			$this->overDriveProduct->overdriveId = $recordId;
			if ($this->overDriveProduct->find(true)) {
				$this->valid = true;
			} else {
				$this->valid = false;
			}
		} else {
			$this->valid = false;
		}
		if ($this->valid) {
			parent::__construct($groupedWork);
		}
	}

	public function getIdWithSource() {
		return 'overdrive:' . $this->id;
	}

	public function getModule(): string {
		return 'OverDrive';
	}

	public function getRecordType() {
		return 'overdrive';
	}

	/**
	 * Load the grouped work that this record is connected to.
	 */
	public function loadGroupedWork() {
		require_once ROOT_DIR . '/sys/Grouping/GroupedWorkPrimaryIdentifier.php';
		require_once ROOT_DIR . '/sys/Grouping/GroupedWork.php';
		$groupedWork = new GroupedWork();
		$query = "SELECT grouped_work.* FROM grouped_work INNER JOIN grouped_work_primary_identifiers ON grouped_work.id = grouped_work_id WHERE type='overdrive' AND identifier = '" . $this->getUniqueID() . "'";
		$groupedWork->query($query);

		if ($groupedWork->getNumResults() == 1) {
			$groupedWork->fetch();
			$this->groupedWork = clone $groupedWork;
		}
	}

	public function getPermanentId() {
		return $this->getGroupedWorkId();
	}

	public function getGroupedWorkId() {
		if (!isset($this->groupedWork)) {
			$this->loadGroupedWork();
		}
		if ($this->groupedWork) {
			return $this->groupedWork->permanent_id;
		} else {
			return null;
		}

	}

	public function isValid() {
		return $this->valid;
	}

	/**
	 * Assign necessary Smarty variables and return a template name to
	 * load in order to display holdings extracted from the base record
	 * (i.e. URLs in MARC 856 fields).  This is designed to supplement,
	 * not replace, holdings information extracted through the ILS driver
	 * and displayed in the Holdings tab of the record view page.  Returns
	 * null if no data is available.
	 *
	 * @access  public
	 * @return  array              Name of Smarty template file to display.
	 */
	public function getHoldings() {
		return $this->getItems();
	}

	public function getStatusSummary() {
		$holdings = $this->getHoldings();
		$availability = $this->getAvailability();
		$readerName = new OverDriveDriver();
		$readerName = $readerName->getReaderName();

		$holdPosition = 0;

		$availableCopies = 0;
		$totalCopies = 0;
		$onOrderCopies = 0;
		$checkedOut = 0;
		$onHold = 0;
		$wishListSize = 0;
		$numHolds = 0;
		if ($availability != null) {
			$availableCopies += $availability->copiesAvailable;
			$totalCopies += $availability->copiesOwned;
			$numHolds = $availability->numberOfHolds;
		}

		//Load status summary
		$statusSummary = [];
		$statusSummary['recordId'] = $this->id;
		$statusSummary['totalCopies'] = $totalCopies;
		$statusSummary['onOrderCopies'] = $onOrderCopies;
		$statusSummary['accessType'] = 'overdrive';
		$statusSummary['isOverDrive'] = false;
		$statusSummary['alwaysAvailable'] = false;
		$statusSummary['class'] = 'checkedOut';
		$statusSummary['available'] = false;
		$statusSummary['status'] = 'Not Available';

		$statusSummary['availableCopies'] = $availableCopies;
		$statusSummary['isOverDrive'] = true;
		if ($totalCopies >= 999999) {
			$statusSummary['alwaysAvailable'] = true;
		}
		if ($availableCopies > 0) {
			$statusSummary['status'] = "Available from " . $readerName;
			$statusSummary['available'] = true;
			$statusSummary['class'] = 'available';
		} else {
			$statusSummary['status'] = 'Checked Out';
			$statusSummary['available'] = false;
			$statusSummary['class'] = 'checkedOut';
			$statusSummary['isOverDrive'] = true;
		}


		//Determine which buttons to show
		$statusSummary['holdQueueLength'] = $numHolds;
		$statusSummary['showPlaceHold'] = $availableCopies == 0;
		$statusSummary['showCheckout'] = $availableCopies > 0;
		$statusSummary['showAddToWishlist'] = false;
		$statusSummary['showAccessOnline'] = false;

		$statusSummary['onHold'] = $onHold;
		$statusSummary['checkedOut'] = $checkedOut;
		$statusSummary['holdPosition'] = $holdPosition;
		$statusSummary['numHoldings'] = count($holdings);
		$statusSummary['wishListSize'] = $wishListSize;

		return $statusSummary;
	}

	public function getSeries() {
		$seriesData = $this->getGroupedWorkDriver()->getSeries();
		if ($seriesData == null) {
			$seriesName = isset($this->getOverDriveMetaData()->getDecodedRawData()->series) ? $this->getOverDriveMetaData()->getDecodedRawData()->series : null;
			if ($seriesName != null) {
				$seriesData = [
					'seriesTitle' => $seriesName,
					'fromNovelist' => false,
				];
			}
		}
		return $seriesData;
	}

	/**
	 * Assign necessary Smarty variables and return a template name to
	 * load in order to display the full record information on the Staff
	 * View tab of the record view page.
	 *
	 * @access  public
	 * @return  string              Name of Smarty template file to display.
	 */
	public function getStaffView() {
		global $interface;

		$this->getGroupedWorkDriver()->assignGroupedWorkStaffView();

		$interface->assign('bookcoverInfo', $this->getBookcoverInfo());

		$groupedWorkDriver = $this->getGroupedWorkDriver();
		if ($groupedWorkDriver != null) {
			if ($groupedWorkDriver->isValid()) {
				$interface->assign('hasValidGroupedWork', true);
				$this->getGroupedWorkDriver()->assignGroupedWorkStaffView();

				require_once ROOT_DIR . '/sys/Grouping/NonGroupedRecord.php';
				$nonGroupedRecord = new NonGroupedRecord();
				$nonGroupedRecord->source = $this->getRecordType();
				$nonGroupedRecord->recordId = $this->id;
				if ($nonGroupedRecord->find(true)) {
					$interface->assign('isUngrouped', true);
					$interface->assign('ungroupingId', $nonGroupedRecord->id);
				} else {
					$interface->assign('isUngrouped', false);
				}
			} else {
				$interface->assign('hasValidGroupedWork', false);
			}
		} else {
			$interface->assign('hasValidGroupedWork', false);
		}

		$overDriveAPIProduct = new OverDriveAPIProduct();
		$overDriveAPIProduct->overdriveId = strtolower($this->id);
		if ($overDriveAPIProduct->find(true)) {
			$interface->assign('overDriveProduct', $overDriveAPIProduct);
			require_once ROOT_DIR . '/sys/OverDrive/OverDriveAPIProductMetaData.php';
			$overDriveAPIProductMetaData = new OverDriveAPIProductMetaData();
			$overDriveAPIProductMetaData->productId = $overDriveAPIProduct->id;
			if ($overDriveAPIProductMetaData->find(true)) {
				$overDriveMetadata = $overDriveAPIProductMetaData->rawData;
				//Replace http links to content reserve with https so we don't get mixed content warnings
				$overDriveMetadata = str_replace('http://images.contentreserve.com', 'https://images.contentreserve.com', $overDriveMetadata);
				$overDriveMetadata = json_decode($overDriveMetadata);
				$interface->assign('overDriveMetaDataRaw', $overDriveMetadata);
			}
		}

		$readerName = new OverDriveDriver();
		$readerName = $readerName->getReaderName();
		$interface->assign('readerName', $readerName);

		return 'RecordDrivers/OverDrive/staff.tpl';
	}

	/**
	 * The Table of Contents extracted from the record.
	 * Returns null if no Table of Contents is available.
	 *
	 * @access  public
	 * @return  array              Array of elements in the table of contents
	 */
	public function getTableOfContents() {
		return null;
	}

	/**
	 * Return the unique identifier of this record within the Solr index;
	 * useful for retrieving additional information (like tags and user
	 * comments) from the external MySQL database.
	 *
	 * @access  public
	 * @return  string              Unique identifier.
	 */
	public function getUniqueID() {
		return $this->id;
	}

	function getLanguage() {
		$metaData = $this->getOverDriveMetaData()->getDecodedRawData();
		$languages = [];
		if (isset($metaData->languages)) {
			foreach ($metaData->languages as $language) {
				$languages[] = $language->name;
			}
		}
		return $languages;
	}

	private $availability = null;

	/**
	 * @return OverDriveAPIProductAvailability
	 */
	function getAvailability() {
		global $library;
		if ($this->availability == null) {
			if ($library->getOverdriveScope() != null) {
				require_once ROOT_DIR . '/sys/OverDrive/OverDriveAPIProductAvailability.php';
				$availability = new OverDriveAPIProductAvailability();
				$availability->productId = $this->overDriveProduct->id;

				$availability->settingId = $library->getOverdriveScope()->settingId;
				//Only include shared collection if include digital collection is on
				$libraryScopingId = $this->getLibraryScopingId();
				//OverDrive availability now returns correct counts for the library including shared items for each library.
				// Just get the correct availability for with either the library (if available) or the shared collection
				$availability->whereAdd("libraryId = $libraryScopingId OR libraryId = -1");
				$availability->orderBy("libraryId DESC");
				if ($availability->find(true)) {
					$this->availability = clone $availability;
				}
			}
		}
		return $this->availability;
	}

	public function getLibraryScopingId() {
		//For econtent, we need to be more specific when restricting copies
		//since patrons can't use copies that are only available to other libraries.
		$searchLibrary = Library::getSearchLibrary();
		$searchLocation = Location::getSearchLocation();
		$activeLibrary = Library::getActiveLibrary();
		global $locationSingleton;
		$activeLocation = $locationSingleton->getActiveLocation();
		$homeLibrary = Library::getPatronHomeLibrary();

		//Load the holding label for the branch where the user is physically.
		if (!is_null($homeLibrary)) {
			return $homeLibrary->libraryId;
		} elseif (!is_null($activeLocation)) {
			$activeLibrary = Library::getLibraryForLocation($activeLocation->locationId);
			return $activeLibrary->libraryId;
		} elseif (isset($activeLibrary)) {
			return $activeLibrary->libraryId;
		} elseif (!is_null($searchLocation)) {
			return $searchLocation->libraryId;
		} elseif (isset($searchLibrary)) {
			return $searchLibrary->libraryId;
		} else {
			return -1;
		}
	}

	public function getDescriptionFast() {
		$metaData = $this->getOverDriveMetaData();
		return $metaData->fullDescription;
	}

	public function getDescription() {
		$metaData = $this->getOverDriveMetaData();
		return $metaData->fullDescription;
	}

	/**
	 * Return the first valid ISBN found in the record (favoring ISBN-10 over
	 * ISBN-13 when possible).
	 *
	 * @return  mixed
	 */
	public function getCleanISBN() {
		require_once ROOT_DIR . '/sys/ISBN.php';

		// Get all the ISBNs and initialize the return value:
		$isbns = $this->getISBNs();
		$isbn13 = false;

		// Loop through the ISBNs:
		foreach ($isbns as $isbn) {
			// Strip off any unwanted notes:
			if ($pos = strpos($isbn, ' ')) {
				$isbn = substr($isbn, 0, $pos);
			}

			// If we find an ISBN-10, return it immediately; otherwise, if we find
			// an ISBN-13, save it if it is the first one encountered.
			$isbnObj = new ISBN($isbn);
			if ($isbn10 = $isbnObj->get10()) {
				return $isbn10;
			}
			if (!$isbn13) {
				$isbn13 = $isbnObj->get13();
			}
		}
		return $isbn13;
	}

	/**
	 * Get an array of all ISBNs associated with the record (may be empty).
	 *
	 * @access  protected
	 * @return  array
	 */
	public function getISBNs() {
		//Load ISBNs for the product
		if ($this->isbns == null) {
			require_once ROOT_DIR . '/sys/OverDrive/OverDriveAPIProductIdentifiers.php';
			$overDriveIdentifiers = new OverDriveAPIProductIdentifiers();
			$overDriveIdentifiers->type = 'ISBN';
			$overDriveIdentifiers->productId = $this->overDriveProduct->id;
			$this->isbns = [];
			$overDriveIdentifiers->find();
			while ($overDriveIdentifiers->fetch()) {
				$this->isbns[] = $overDriveIdentifiers->value;
			}
		}
		return $this->isbns;
	}

	public function getOCLCNumber() {
		return '';
	}

	/**
	 * Get an array of all UPCs associated with the record (may be empty).
	 *
	 * @access  protected
	 * @return  array
	 */
	public function getUPCs() {
		//Load UPCs for the product
		if ($this->upcs == null) {
			require_once ROOT_DIR . '/sys/OverDrive/OverDriveAPIProductIdentifiers.php';
			$overDriveIdentifiers = new OverDriveAPIProductIdentifiers();
			$overDriveIdentifiers->type = 'UPC';
			$overDriveIdentifiers->productId = $this->overDriveProduct->id;
			$this->upcs = [];
			$overDriveIdentifiers->find();
			while ($overDriveIdentifiers->fetch()) {
				$this->upcs[] = $overDriveIdentifiers->value;
			}
		}
		return $this->upcs;
	}

	public function getSubjects() {
		return $this->getOverDriveMetaData()->getDecodedRawData()->subjects;
	}

	/**
	 * Get the full title of the record.
	 *
	 * @return  string
	 */
	public function getTitle() {
		return $this->overDriveProduct->title;
	}

	/**
	 * Get the full title of the record.
	 *
	 * @return  string
	 */
	public function getSortableTitle() {
		return $this->overDriveProduct->title;
	}

	public function getShortTitle() {
		return $this->overDriveProduct->title;
	}

	public function getSubtitle() {
		return $this->overDriveProduct->subtitle;
	}

	/**
	 * Get an array of all the formats associated with the record.
	 *
	 * @access  protected
	 * @return  array
	 */
	public function getFormats() {
		$formats = [];
		if ($this->subSource == 'kindle') {
			$formats[] = 'Kindle';
		} else {
			$readerName = new OverDriveDriver();
			$readerName = $readerName->getReaderName();
			foreach ($this->getItems() as $item) {
				switch ($item->textId) {
					case "ebook-overdrive":
						$formats[] = "$readerName Read";
						break;
					case "audiobook-wma":
						$formats[] = "$readerName WMA Audiobook";
						break;
					case "audiobook-mp3":
						$formats[] = "$readerName MP3 Audiobook";
						break;
					case "music-wma":
						$formats[] = "$readerName Music";
						break;
					case "video-streaming":
					case "music-wmv":
						$formats[] = "$readerName Video";
						break;
					case "video-wmv-mobile":
						$formats[] = "$readerName Video (mobile)";
						break;
					case "magazine-overdrive":
						$formats[] = "$readerName Magazine";
						break;
					default:
						$formats[] = $item->name;
						break;
				}
			}
		}
		return $formats;
	}

	/**
	 * Get an array of all the format categories associated with the record.
	 *
	 * @return  array
	 */
	public function getFormatCategory() {
		$formats = [];
		foreach ($this->getItems() as $item) {
			$formats[] = $item->name;
		}
		return $formats;
	}

	/**
	 * @return OverDriveAPIProductFormats[]
	 */
	public function getItems() {
		if ($this->items == null) {
			require_once ROOT_DIR . '/sys/OverDrive/OverDriveAPIProductFormats.php';
			$overDriveFormats = new OverDriveAPIProductFormats();
			$this->items = [];
			if ($this->valid) {
				$overDriveFormats->productId = $this->overDriveProduct->id;
				if ($this->subSource == 'kindle') {
					$overDriveFormats->textId = 'ebook-kindle';
				}
				$overDriveFormats->find();
				while ($overDriveFormats->fetch()) {
					$this->items[] = clone $overDriveFormats;
				}
			}

			global $timer;
			$timer->logTime("Finished getItems for OverDrive record {$this->overDriveProduct->id}");
		}
		return $this->items;
	}

	public function getAuthor() {
		return $this->overDriveProduct->primaryCreatorName;
	}

	public function getPrimaryAuthor() {
		return $this->overDriveProduct->primaryCreatorName;
	}

	public function getContributors() {
		$contributors = [];
		$rawData = $this->getOverDriveMetaData()->getDecodedRawData();
		foreach ($rawData->creators as $creator) {
			$contributors[$creator->fileAs] = $creator->fileAs;
		}
		return $contributors;
	}

	private $detailedContributors = null;

	public function getDetailedContributors() {
		if ($this->detailedContributors == null) {
			$this->detailedContributors = [];
			$rawData = $this->getOverDriveMetaData()->getDecodedRawData();
			foreach ($rawData->creators as $creator) {
				if (!array_key_exists($creator->fileAs, $this->detailedContributors)) {
					$this->detailedContributors[$creator->fileAs] = [
						'name' => $creator->fileAs,
						'title' => '',
						'roles' => [],
					];
				}
				$this->detailedContributors[$creator->fileAs]['roles'][] = $creator->role;
			}
		}
		return $this->detailedContributors;
	}

	public function getBookcoverUrl($size = 'small', $absolutePath = false) {
		global $configArray;
		if ($absolutePath) {
			$bookCoverUrl = $configArray['Site']['url'];
		} else {
			$bookCoverUrl = '';
		}
		$bookCoverUrl .= '/bookcover.php?size=' . $size;
		$bookCoverUrl .= '&id=' . $this->id;
		$bookCoverUrl .= '&type=overdrive';
		return $bookCoverUrl;
	}

	public function getCoverUrl($size = 'small') {
		return $this->getBookcoverUrl($size);
	}

	private function getOverDriveMetaData() {
		if ($this->overDriveMetaData == null) {
			require_once ROOT_DIR . '/sys/OverDrive/OverDriveAPIProductMetaData.php';
			$this->overDriveMetaData = new OverDriveAPIProductMetaData();
			$this->overDriveMetaData->productId = $this->overDriveProduct->id;
			$this->overDriveMetaData->find(true);
		}
		return $this->overDriveMetaData;
	}

	public function getRatingData() {
		require_once ROOT_DIR . '/services/API/WorkAPI.php';
		$workAPI = new WorkAPI();
		$groupedWorkId = $this->getGroupedWorkId();
		if ($groupedWorkId == null) {
			return null;
		} else {
			return $workAPI->getRatingData($this->getGroupedWorkId());
		}
	}

	public function getMoreDetailsOptions() {
		global $interface;

		$isbn = $this->getCleanISBN();

		/** @var OverDriveAPIProductFormats[] $holdings */
		$holdings = $this->getHoldings();
		$availability = $this->getAvailability();
		$interface->assign('availability', $availability);
		if ($availability != null) {
			$numberOfHolds = $availability->numberOfHolds;
			$interface->assign('numberOfHolds', $numberOfHolds);
			$showAvailability = true;
			$showAvailabilityOther = true;
		}else{
			$showAvailability = false;
			$showAvailabilityOther = false;
		}
		$interface->assign('showAvailability', $showAvailability);
		$interface->assign('showAvailabilityOther', $showAvailabilityOther);
		$showOverDriveConsole = false;
		$showAdobeDigitalEditions = false;
		$readerName = new OverDriveDriver();
		$readerName = $readerName->getReaderName();
		foreach ($holdings as $item) {
			switch ($item->textId) {
				case "ebook-overdrive":
					$formatName = "$readerName Read";
					break;
				case "audiobook-wma":
					$formatName = "$readerName WMA Audiobook";
					break;
				case "audiobook-mp3":
					$formatName = "$readerName MP3 Audiobook";
					break;
				case "music-wma":
					$formatName = "$readerName Music";
					break;
				case "video-streaming":
				case "music-wmv":
					$formatName = "$readerName Video";
					break;
				case "video-wmv-mobile":
					$formatName = "$readerName Video (mobile)";
					break;
				case "magazine-overdrive":
					$formatName = "$readerName Magazine";
					break;
				default:
					$formatName = $item->name;
					break;
			}
			$item->name = $formatName;
			if (in_array($item->textId, [
				'ebook-epub-adobe',
				'ebook-pdf-adobe',
			])) {
				$showAdobeDigitalEditions = true;
			} elseif (in_array($item->textId, [
				'video-wmv',
				'music-wma',
				'music-wma',
				'audiobook-wma',
				'audiobook-mp3',
			])) {
				$showOverDriveConsole = true;
			}
		}
		$interface->assign('showOverDriveConsole', $showOverDriveConsole);
		$interface->assign('showAdobeDigitalEditions', $showAdobeDigitalEditions);

		$interface->assign('holdings', $holdings);

		//Load more details options
		$moreDetailsOptions = $this->getBaseMoreDetailsOptions($isbn);
		$moreDetailsOptions['formats'] = [
			'label' => 'Formats',
			'body' => $interface->fetch('OverDrive/view-formats.tpl'),
			'openByDefault' => true,
		];
		//Other editions if applicable (only if we aren't the only record!)
		$relatedRecords = $this->getGroupedWorkDriver()->getRelatedRecords();
		if (count($relatedRecords) > 1) {
			$interface->assign('relatedManifestations', $this->getGroupedWorkDriver()->getRelatedManifestations());
			$interface->assign('workId', $this->getGroupedWorkDriver()->getPermanentId());
			$moreDetailsOptions['otherEditions'] = [
				'label' => 'Other Editions and Formats',
				'body' => $interface->fetch('GroupedWork/relatedManifestations.tpl'),
				'hideByDefault' => false,
			];
		}

		$moreDetailsOptions['moreDetails'] = [
			'label' => 'More Details',
			'body' => $interface->fetch('OverDrive/view-more-details.tpl'),
		];
		$moreDetailsOptions['citations'] = [
			'label' => 'Citations',
			'body' => $interface->fetch('Record/cite.tpl'),
		];
		$moreDetailsOptions['copyDetails'] = [
			'label' => 'Copy Details',
			'body' => $interface->fetch('OverDrive/view-copies.tpl'),
		];
		if ($interface->getVariable('showStaffView')) {
			$moreDetailsOptions['staff'] = [
				'label' => 'Staff View',
				'onShow' => "AspenDiscovery.OverDrive.getStaffView('{$this->id}');",
				'body' => '<div id="staffViewPlaceHolder">' . translate([
						'text' => 'Loading Staff View.',
						'isPublicFacing' => true,
					]) . '</div>',
			];
		}

		return $this->filterAndSortMoreDetailsOptions($moreDetailsOptions);
	}

	public function getRecordUrl() {
		$id = $this->getUniqueID();
		if ($this->subSource) {
			$linkUrl = "/OverDrive/{$this->subSource}:" . $id . '/Home';
		} else {
			$linkUrl = "/OverDrive/" . $id . '/Home';
		}
		return $linkUrl;
	}

	function getPublishers() {
		$publishers = [];
		if (isset($this->overDriveMetaData->publisher)) {
			$publishers[] = $this->overDriveMetaData->publisher;
		}
		return $publishers;
	}

	function getPublicationDates() {
		$publicationDates = [];
		if (isset($this->getOverDriveMetaData()->getDecodedRawData()->publishDateText)) {
			$publishDate = $this->getOverDriveMetaData()->getDecodedRawData()->publishDateText;
			$publishYear = substr($publishDate, -4);
			$publicationDates[] = $publishYear;
		}
		return $publicationDates;
	}

	function getPlacesOfPublication() {
		return [];
	}

	/**
	 * Get an array of publication detail lines combining information from
	 * getPublicationDates(), getPublishers() and getPlacesOfPublication().
	 *
	 * @access  public
	 * @return  array
	 */
	function getPublicationDetails() {
		$places = $this->getPlacesOfPublication();
		$placesOfPublication = $this->getPlacesOfPublication();
		$names = $this->getPublishers();
		$dates = $this->getPublicationDates();

		$i = 0;
		$returnVal = [];
		while (isset($places[$i]) || isset($placesOfPublication[$i]) || isset($names[$i]) || isset($dates[$i])) {
		// while (isset($places[$i]) || isset($names[$i]) || isset($dates[$i])) {
			// Put all the pieces together, and do a little processing to clean up
			// unwanted whitespace.
			$publicationInfo = (isset($places[$i]) ? $places[$i] . ' ' : '') . (isset($placesOfPublication[$i]) ? $placesOfPublication[$i] . ' ': '') . (isset($names[$i]) ? $names[$i] . ' ' : '') . (isset($dates[$i]) ? (', ' . $dates[$i] . '.') : '');
			// $publicationInfo = (isset($places[$i]) ? $places[$i] . ' ' : '') . (isset($names[$i]) ? $names[$i] . ' ' : '') . (isset($dates[$i]) ? $dates[$i] : '');
			$returnVal[] = trim(str_replace('  ', ' ', $publicationInfo));
			$i++;
		}

		return $returnVal;
	}

	public function getEditions() {
		$edition = isset($this->getOverDriveMetaData()->getDecodedRawData()->edition) ? $this->getOverDriveMetaData()->getDecodedRawData()->edition : null;
		if (is_array($edition)) {
			return $edition;
		} elseif (is_null($edition)) {
			return [];
		} else {
			return [$edition];
		}
	}

	public function getStreetDate() {
		return isset($this->overDriveMetaData->getDecodedRawData()->publishDateText) ? $this->overDriveMetaData->getDecodedRawData()->publishDateText : null;
	}

	public function getGroupedWorkDriver() {
		require_once ROOT_DIR . '/RecordDrivers/GroupedWorkDriver.php';
		if ($this->groupedWorkDriver == null) {
			$this->groupedWorkDriver = new GroupedWorkDriver($this->getPermanentId());
		}
		return $this->groupedWorkDriver;
	}

	protected $_actions = null;

	public function getRecordActions($relatedRecord, $variationId, $isAvailable, $isHoldable, $volumeData = null) {
		if ($this->_actions === null) {
			$this->_actions = [];
			//Check to see if OverDrive circulation is enabled
			require_once ROOT_DIR . '/Drivers/OverDriveDriver.php';
			$overDriveDriver = OverDriveDriver::getOverDriveDriver();
			$readerName = $overDriveDriver->getReaderName();
			if (!$overDriveDriver->isCirculationEnabled()) {
				$overDriveMetadata = $this->getOverDriveMetaData();
				$crossRefId = $overDriveMetadata->getDecodedRawData()->crossRefId;
				$productUrl = $overDriveDriver->getProductUrl($crossRefId);
				if (!empty($productUrl)) {
					$this->_actions[] = [
						'title' => translate([
							'text' => 'Access Online',
							'isPublicFacing' => true,
						]),
						'url' => $overDriveDriver->getProductUrl($crossRefId),
						'target' => 'blank',
						'requireLogin' => false,
						'type' => 'overdrive_access_online',
					];
				}
			} else {
				//Check to see if the title is on hold or checked out to the patron.
				$loadDefaultActions = true;
				if (UserAccount::isLoggedIn()) {
					$user = UserAccount::getActiveUserObj();
					$this->_actions = array_merge($this->_actions, $user->getCirculatedRecordActions('overdrive', $this->id));
					$loadDefaultActions = count($this->_actions) == 0;
				}

				if ($loadDefaultActions) {
					if ($isAvailable) {
						$this->_actions[] = [
							'title' => translate([
								'text' => "Check Out %1%",
								1 => $readerName,
								"isPublicFacing" => true,
							]),
							'onclick' => "return AspenDiscovery.OverDrive.checkOutTitle('{$this->id}');",
							'requireLogin' => false,
							'type' => 'overdrive_checkout',
						];
					} else {
						$this->_actions[] = [
							'title' => translate([
								'text' => 'Place Hold %1%',
								1 => $readerName,
								'isPublicFacing' => true,
							]),
							'onclick' => "return AspenDiscovery.OverDrive.placeHold('{$this->id}');",
							'requireLogin' => false,
							'type' => 'overdrive_hold',
						];
					}
				}
			}

			$this->_actions = array_merge($this->_actions, $this->getPreviewActions());
		}
		return $this->_actions;
	}

	function getPreviewActions() {
		$items = $this->getItems();
		$previewLinks = [];
		require_once ROOT_DIR . '/sys/Utils/StringUtils.php';
		$actions = [];
		foreach ($items as $item) {
			if (!empty($item->sampleUrl_1) && !in_array($item->sampleUrl_1, $previewLinks) && !StringUtils::endsWith($item->sampleUrl_1, '.epub') && !StringUtils::endsWith($item->sampleUrl_1, '.wma')) {
				$previewLinks[] = $item->sampleUrl_1;
				$actions[] = [
					'title' => translate([
						'text' => 'Preview ' . ucwords($item->sampleSource_1),
						'isPublicFacing' => true,
						'isAdminEnteredData' => true,
					]),
					'onclick' => "return AspenDiscovery.OverDrive.showPreview('{$this->id}', '{$item->id}', '1');",
					'requireLogin' => false,
					'type' => 'overdrive_sample',
					'btnType' => 'btn-info',
					'formatId' => $item->id,
					'sampleNumber' => 1,
				];
			}
			if (!empty($item->sampleUrl_2) && !in_array($item->sampleUrl_2, $previewLinks) && !StringUtils::endsWith($item->sampleUrl_2, '.epub') && !StringUtils::endsWith($item->sampleUrl_2, '.wma')) {
				$previewLinks[] = $item->sampleUrl_2;
				$actions[] = [
					'title' => translate([
						'text' => 'Preview ' . ucwords($item->sampleSource_2),
						'isPublicFacing' => true,
						'isAdminEnteredData' => true,
					]),
					'onclick' => "return AspenDiscovery.OverDrive.showPreview('{$this->id}', '{$item->id}', '2');",
					'requireLogin' => false,
					'type' => 'overdrive_sample',
					'btnType' => 'btn-info',
					'formatId' => $item->id,
					'sampleNumber' => 2,
				];
			}
		}
		return $actions;
	}

	function getNumHolds(): int {
		$availability = $this->getAvailability();
		if ($availability == null) {
			return 0;
		} else {
			return $availability->numberOfHolds;
		}
	}

	public function getSemanticData() {
		// Schema.org
		// Get information about the record
		require_once ROOT_DIR . '/RecordDrivers/LDRecordOffer.php';
		$relatedRecord = $this->getRelatedRecord();
		if ($relatedRecord != null) {
			$linkedDataRecord = new LDRecordOffer($relatedRecord);
			$semanticData [] = [
				'@context' => 'http://schema.org',
				'@type' => $linkedDataRecord->getWorkType(),
				'name' => $this->getTitle(),
				'creator' => $this->getAuthor(),
				'bookEdition' => $this->getEditions(),
				'isAccessibleForFree' => true,
				'image' => $this->getBookcoverUrl('medium', true),
				"offers" => $linkedDataRecord->getOffers(),
			];

			global $interface;
			$interface->assign('og_title', $this->getTitle());
			$interface->assign('og_description', $this->getDescriptionFast());
			$interface->assign('og_type', $this->getGroupedWorkDriver()->getOGType());
			$interface->assign('og_image', $this->getBookcoverUrl('medium', true));
			$interface->assign('og_url', $this->getAbsoluteUrl());
			return $semanticData;
		} else {
			//AspenError::raiseError('OverDrive Record did not have an associated record in grouped work ' . $this->getPermanentId());
			return null;
		}
	}

	function getRelatedRecord() {
		$id = strtolower('overdrive:' . $this->id);
		return $this->getGroupedWorkDriver()->getRelatedRecord($id);
	}

	/**
	 * Get an array of all ISSNs associated with the record (may be empty).
	 *
	 * @access  public
	 * @return  array
	 */
	public function getISSNs() {
		return [];
	}
}