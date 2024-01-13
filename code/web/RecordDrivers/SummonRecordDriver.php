<?php

require_once ROOT_DIR . '/RecordDrivers/RecordInterface.php';


class SummonRecordDriver extends RecordInterface {
	private $lastSearchResults;
	private $record;

	/**
	 * Constructor.  We build the object using all the data retrieved
	 * from the (Solr) index.  Since we have to
	 * make a search call to find out which record driver to construct,
	 * we will already have this data available, so we might as well
	 * just pass it into the constructor.
	 *
	 * @param array|File_MARC_Record||string   $recordData     Data to construct the driver from
	 * @access  public
	 */
	public function __construct($record) {
		$this->record= $record;
		// foreach ($this->lastSearchResults as $record) {

		// }
	}

	

	public function isValid() {
		return true;
	}


	public function getBookcoverUrl($size='large', $absolutePath =false) {
		if ($size == 'medium' || $size == 'small') {
			if (!empty($this->record['thumbnail_m'])) {
				return $this->record['thumbnail_m'][0];
			} return $this->getBookcoverUrl('large');
		}
		if ($size == 'large') {
			if (!empty($this->record['thumbnail_l'])) {
				return $this->record['thumbnail_l'][0];
			} 
		} return null;
	}

	/**
	 * @param bool $unscoped
	 * @return string
	 */
	public function getLinkUrl($unscoped = false) {
		return $this->getRecordUrl();
	}

	/**
	 * @return string
	 */
	public function getAbsoluteUrl() {
		return $this->getRecordUrl();
	}

 	public function getRecordUrl() {
		if (isset($this->record['link'])) {
			return $this->record['link'];
		} else {
			return null;
		}
	}

	public function getUniqueID() {
		if (isset($this->record['DBID'])) {
			return $this->record['DBID'][0];
		} else {
			return null;
		}
	}
	
	public function getModule(): string {
		return 'Summon';
	}

	public function getSearchResult($view = 'list', $showListsAppearingOn = true) {
		if ($view == 'covers') { // Displaying Results as bookcover tiles
			return $this->getBrowseResult();
		}

		global $interface;

		$id = $this->getUniqueID();
		$interface->assign('summId', $id);
		$interface->assign('summShortId', $id);
		$interface->assign('module', $this->getModule());

		$formats = $this->getFormats();
		$interface->assign('summFormats', $formats);

		$interface->assign('summUrl', $this->getLinkUrl());
		$interface->assign('summTitle', $this->getTitle());
		$interface->assign('summAuthor', $this->getAuthor());
		$interface->assign('summSourceDatabase', $this->getSourceDatabase());
		$interface->assign('summHasFullText', $this->hasFullText());

		//Check to see if there are lists the record is on
		if ($showListsAppearingOn) {
			require_once ROOT_DIR . '/sys/UserLists/UserList.php';
			$appearsOnLists = UserList::getUserListsForRecord('summon', $this->getId());
			$interface->assign('appearsOnLists', $appearsOnLists);
		}

		$interface->assign('summDescription', $this->getDescription());

		$interface->assign('bookCoverUrl', $this->getBookcoverUrl('small'));
		$interface->assign('bookCoverUrlMedium', $this->getBookcoverUrl('medium'));

		require_once ROOT_DIR . '/sys/Summon/SummonRecordUsage.php';
		$recordUsage = new SummonRecordUsage();
		global $aspenUsage;
		$recordUsage->instance = $aspenUsage->getInstance();
		$recordUsage->summonId = $this->getUniqueID();
		$recordUsage->year = date('Y');
		$recordUsage->month = date('n');
		if ($recordUsage->find(true)) {
			$recordUsage->timesViewedInSearch++;
			$recordUsage->update();
		} else {
			$recordUsage->timesViewedInSearch = 1;
			$recordUsage->timesUsed = 0;
			$recordUsage->insert();
		}

		return 'RecordDrivers/Summon/result.tpl';
	}

	public function getBrowseResult() {
		global $interface;

		$interface->assign('summId', $this->getUniqueID());


		$interface->assign('summUrl', $this->getLinkUrl());
		$interface->assign('summTitle', $this->getTitle());

		//Get cover image size
		global $interface;
		$appliedTheme = $interface->getAppliedTheme();

		$interface->assign('bookCoverUrl', $this->getBookcoverUrl('medium'));

		// if ($appliedTheme != null && $appliedTheme->browseCategoryImageSize == 1) {
		// 	$interface->assign('bookCoverUrlMedium', $this->getBookcoverUrl('large'));
		// } else {
		// 	$interface->assign('bookCoverUrlMedium', $this->getBookcoverUrl('medium'));
		// }

		return 'RecordDrivers/Summon/browse_result.tpl';
	}

	/**
	 * Assign necessary Smarty variables and return a template name to
	 * load in order to display a summary of the item suitable for use in
	 * search results.
	 *
	 * @access  public
	 * @return  string              Name of Smarty template file to display.
	 */
	public function getCombinedResult() {
		global $interface;

		$id = $this->getUniqueID();
		$interface->assign('summId', $id);
		$interface->assign('summShortId', $id);
		$interface->assign('module', $this->getModule());

		$formats = $this->getFormats();
		$interface->assign('summFormats', $formats);

		$interface->assign('summUrl', $this->getLinkUrl());
		$interface->assign('summTitle', $this->getTitle());
		$interface->assign('summAuthor', $this->getAuthor());
		$interface->assign('summSourceDatabase', $this->getSourceDatabase());
		$interface->assign('summHasFullText', $this->hasFullText());

		$interface->assign('summDescription', $this->getDescription());

		$interface->assign('bookCoverUrl', $this->getBookcoverUrl('small'));
		$interface->assign('bookCoverUrlMedium', $this->getBookcoverUrl('medium'));

		return 'RecordDrivers/Summon/combinedResult.tpl';
	}

	public function getSpotlightResult(CollectionSpotlight $collectionSpotlight, string $index) {
		global $interface;
		$interface->assign('showRatings', $collectionSpotlight->showRatings);

		$interface->assign('key', $index);

		if ($collectionSpotlight->coverSize == 'small') {
			$imageUrl = $this->getBookcoverUrl('small');
		} else {
			$imageUrl = $this->getBookcoverUrl('medium');
		}

		$interface->assign('title', $this->getTitle());
		$interface->assign('author', $this->getAuthor());
		$interface->assign('description', $this->getDescription());
		$interface->assign('shortId', $this->getUniqueID());
		$interface->assign('id', $this->getUniqueID());
		$interface->assign('titleURL', $this->getLinkUrl());
		$interface->assign('imageUrl', $imageUrl);

		if ($collectionSpotlight->showRatings) {
			$interface->assign('ratingData', null);
			$interface->assign('showNotInterested', false);
		}

		$result = [
			'title' => $this->getTitle(),
			'author' => $this->getAuthor(),
		];
		if ($collectionSpotlight->style == 'text-list') {
			$result['formattedTextOnlyTitle'] = $interface->fetch('CollectionSpotlight/formattedTextOnlyTitle.tpl');
		} elseif ($collectionSpotlight->style == 'horizontal-carousel') {
			$result['formattedTitle'] = $interface->fetch('CollectionSpotlight/formattedHorizontalCarouselTitle.tpl');
		} else {
			$result['formattedTitle'] = $interface->fetch('CollectionSpotlight/formattedTitle.tpl');
		}

		return $result;
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
		return null;
	}

	/**
	 * Get the full title of the record.
	 *
	 * @return  string
	 */
	public function getTitle() {
		if (isset($this->record['Title'])) {
			$title=$this->record['Title'][0];
		} else {
			$title='Unknown Title';
		}
		return $title;

			
	
		// if (isset($this->recordData->RecordInfo->BibRecord->BibEntity)) {
		// 	if (isset($this->recordData->RecordInfo->BibRecord->BibEntity->Titles)) {
		// 		return $this->recordData->RecordInfo->BibRecord->BibEntity->Titles[0]->TitleFull;
		// 	}
		// }
		// if (isset($this->recordData->RecordInfo->BibRecord->BibRelationships->IsPartOfRelationships)) {
		// 	foreach ($this->recordData->RecordInfo->BibRecord->BibRelationships->IsPartOfRelationships as $relationship) {
		// 		if (isset($relationship->BibEntity->Titles)) {
		// 			return $relationship->BibEntity->Titles[0]->TitleFull;
		// 		}
		// 	}
		// }
	
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



	public function getId() {
		return $this->getUniqueID();
	}

	/**
	 * Does this record have searchable full text in the index?
	 *
	 * Note: As of this writing, searchable full text is not a VuFind feature,
	 *       but this method will be useful if/when it is eventually added.
	 *
	 * @access  public
	 * @return  bool
	 */
	public function hasFullText() {
		if(isset($this->record['hasFullText'])){
			return $this->record['hasFullText'];
		}
		return false;
	}

	public function getFullText() {
	/* 	$fullText = (string)$this->recordData->FullText->Text->Value;
		$fullText = html_entity_decode($fullText);
		$fullText = preg_replace('/<anid>.*?<\/anid>/', '', $fullText); */
		return null;
	}

	/**
	 * Does this record have reviews available?
	 *
	 * @access  public
	 * @return  bool
	 */
	public function hasReviews() {
		return false;
	}

	public function getDescription() {
		if(isset($this->record['Abstract'][0])) {
			$description = $this->record['Abstract'][0];
		} else {
			$description = '';
		}
		return $description;
	}
		
	public function getMoreDetailsOptions() {
		// TODO: Implement getMoreDetailsOptions() method.
	}

	public function getFormats() {
		if(isset($this->record['ContentType'][0])){
			$sourceType = (string)$this->record['ContentType'][0];
		} else {
			$sourceType = 'Unknown Source';
		}
		return $sourceType;
	}

	public function getCleanISSN() {
		return '';
	}

	public function getSourceDatabase() {
		if(isset($this->record['DatabaseTitle'][0])) {
			$databaseTitle = $this->record['DatabaseTitle'][0];
		} else {
			$databaseTitle = '';
		}
		return $databaseTitle;
	}

	public function getPrimaryAuthor() {
		return $this->getAuthor();
	}

	public function getAuthor() {
		if(isset($this->record['Author'][0])) {
			$author=$this->record['Author'][0];
		} else {
			$author='Unknown Title';
		}
		return $author;
	}


	public function getExploreMoreInfo() {
		return [];
	}

	// public function getAllSubjectHeadings() {
	// 	$subjectHeadings = [];
	// 	if (count(@$this->recordData->RecordInfo->BibRecord->BibEntity->Subjects) != 0) {
	// 		foreach ($this->recordData->RecordInfo->BibRecord->BibEntity->Subjects->Subject as $subject) {
	// 			$subjectHeadings[] = (string)$subject->SubjectFull;
	// 		}
	// 	}
	// 	return $subjectHeadings;
	// }

	public function getPermanentId() {
		return $this->getUniqueID();
	}

	/**
	 * Assign necessary Smarty variables and return a template name to
	 * load in order to display a summary of the item suitable for use in
	 * user's favorites list.
	 *
	 * @access  public
	 * @param int $listId ID of list containing desired tags/notes (or
	 *                              null to show tags/notes from all user's lists).
	 * @param bool $allowEdit Should we display edit controls?
	 * @return  string              Name of Smarty template file to display.
	 */
	public function getListEntry($listId = null, $allowEdit = true) {
		$this->getSearchResult('list');

		//Switch template
		return 'RecordDrivers/Summon/listEntry.tpl';
	}

	/**
	 * Assign necessary Smarty variables and return a template name
	 * to load in order to display the requested citation format.
	 * For legal values, see getCitationFormats().  Returns null if
	 * format is not supported.
	 *
	 * @param string $format Citation format to display.
	 * @access  public
	 * @return  string              Name of Smarty template file to display.
	 */
	public function getCitation($format) {
		require_once ROOT_DIR . '/sys/CitationBuilder.php';

		// Build author list:
		$authors = [];
		$primary = $this->getAuthor();
		if (!empty($primary)) {
			$authors[] = $primary;
		}

		//$pubPlaces = $this->getPlacesOfPublication();
		$details = [
			'authors' => $authors,
			'title' => $this->getTitle(),
			'subtitle' => '',
			//'pubPlace' => count($pubPlaces) > 0 ? $pubPlaces[0] : null,
			'pubName' => null,
			'pubDate' => null,
			'edition' => null,
			'format' => $this->getFormats(),
		];

		// Build the citation:
		$citation = new CitationBuilder($details);
		switch ($format) {
			case 'APA':
				return $citation->getAPA();
			case 'AMA':
				return $citation->getAMA();
			case 'ChicagoAuthDate':
				return $citation->getChicagoAuthDate();
			case 'ChicagoHumanities':
				return $citation->getChicagoHumanities();
			case 'MLA':
				return $citation->getMLA();
		}
		return '';
	}
}
