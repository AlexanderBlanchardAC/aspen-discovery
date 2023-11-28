<?php

require_once ROOT_DIR . '/RecordDrivers/RecordInterface.php';
require_once dirname(__FILE__) . '/Exception.php';

class SummonRecordDriver extends RecordInterface{
    	/** @var SimpleXMLElement */

    // const IDENTIFIER_ID =1;
    // const IDENTIFIER_BOOKMARK = 2;
    private $recordData;

    // protected $debug = false;
    // protected $host = 'http://api.summon.serialssolutions.com';
    // protected $version = '2.0.0';
    // public $apiKey;
    // public $apiId;
    // protected $sessionId = false;
    // protected $authedUser = false;
    // protected $responseType = "json";
    /**
     * @param array|File_MARC_Record||string   $recordData     Data to construct the driver from
	 * @access  public
     */

    public function __construct($recordData) {
        // $this->apiId = $apiId;
        // $this->apiKey = $apiKey;
        // $legalOptions = array( 
        //     'authedUser', 'debug', 'host', 'sessionId', 'version', 'responseType'
        // );
        // foreach ($legalOptions as $option) {
        //     if (isset($options[$option])) {
        //         $this->$option = $options[$option];
        //     }
        // }
        if (is_string($recordData)) {
			if (!empty($recordData)) {
				/** @var SearchObject_SummonSearcher $summonSearcher */
				$summonSearcher = SearchObjectFactory::initSearchObject("Summon");
				[
					$dbId,
					$uniqueIdField,
					$uniqueId,
				] = explode(':', $recordData);
				$this->recordData = $summonSearcher->retrieveRecord($dbId, $uniqueIdField, $uniqueId);
			} else {
				$this->recordData = null;
			}
		} else {
			$this->recordData = $recordData;
		}

    }

    // protected function debugPrint($msg) {
    //     if ($this->debug) {
    //         echo "<pre>{$msg}</pre>\n";
    //     }
    // }

    public function isValid() {
		return is_object($this->recordData);
	}

    // public function getRecord($id, $raw = false, $idType = self::IDENTIFIER_ID) {
    //     $this->debugPrint("Get Record: $id");

    //     //query params
    //     $options = $idType === self::IDENTIFIER_BOOKMARK
    //         ? array('s.bookMark' => $id)
    //         : array('s.q' => sprintf('ID:"%s"', $id));
    //     $options['s.role'] = $this->authedUser ? 'authenticated' : 'none';
    //     return $this->call($options, 'search', 'GET', $raw);
    // }
    public function getBookcoverUrl($size = 'small', $absolutePath = false) {
		global $configArray;

		$recordCover = $this->getRecordCoverUrl();
		if (!empty($recordCover)) {
			$imageHeaders = get_headers($recordCover);

			if ($imageHeaders && substr($imageHeaders[0], 9, 3) == 200) {
				return $recordCover;
			}
		}
		if ($absolutePath) {
			$bookCoverUrl = $configArray['Site']['url'];
		} else {
			$bookCoverUrl = '';
		}
		$bookCoverUrl .= "/bookcover.php?id={$this->getUniqueID()}&size={$size}&type=summon";
		return $bookCoverUrl;
	}

    public function getRecordCoverUrl() {
		$header = $this->getChildByTagName($this->recordData, 'header');
		if ($header != null) {
			$controlInfo = $this->getChildByTagName($header, 'controlInfo');
			if ($controlInfo != null) {
				$imgInfo = $this->getChildByTagName($controlInfo, 'img');
				if ($imgInfo != null) {
					return (string)$imgInfo->attributes()['src'];
				}
			}
		}
		return null;
	}

    /**
	 * Overridden because we are linking straight to Summon
	 * @param bool $unscoped
	 * @return string
	 */
	public function getLinkUrl($unscoped = false) {
		return '/Summon/AccessOnline?id=' . $this->getUniqueID();
	}

	/**
	 * Overridden because we are linking straight to Summon
	 * @return string
	 */
	public function getAbsoluteUrl() {
		return $this->getRecordUrl();
	}

	public function getRecordUrl() {
		//global $configArray;
		//return '/Summon/Home?id=' . urlencode($this->getUniqueID());
		return $this->recordData->plink;
	}

    /** @noinspection PhpUnused */
	public function getSummonUrl() {
		return $this->recordData->PLink;
	}

	public function getModule(): string {
		return 'Summonhost';
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
		$interface->assign('summPublishers', $this->getPublishers());
		$interface->assign('summPublicationDates', $this->getPublicationDates());
		$interface->assign('summPublicationPlaces', $this->getPublicationPlaces());

		//Check to see if there are lists the record is on
		if ($showListsAppearingOn) {
			require_once ROOT_DIR . '/sys/UserLists/UserList.php';
			$appearsOnLists = UserList::getUserListsForRecord('Summon', $this->getId());
			$interface->assign('appearsOnLists', $appearsOnLists);
		}

		$interface->assign('summDescription', $this->getDescription());

		$interface->assign('bookCoverUrl', $this->getBookcoverUrl('small'));
		$interface->assign('bookCoverUrlMedium', $this->getBookcoverUrl('medium'));

		require_once ROOT_DIR . '/sys/Summon/SummonRecordUsage.php';
		$recordUsage = new SummonRecordUsage();
		global $aspenUsage;
		$recordUsage->instance = $aspenUsage->instance;
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

		$id = $this->getUniqueID();
		$interface->assign('summId', $id);


		$interface->assign('summUrl', $this->getLinkUrl());
		$interface->assign('summTitle', $this->getTitle());

		//Get cover image size
		global $interface;
		$appliedTheme = $interface->getAppliedTheme();

		$interface->assign('bookCoverUrl', $this->getBookcoverUrl('small'));

		if ($appliedTheme != null && $appliedTheme->browseCategoryImageSize == 1) {
			$interface->assign('bookCoverUrlMedium', $this->getBookcoverUrl('large'));
		} else {
			$interface->assign('bookCoverUrlMedium', $this->getBookcoverUrl('medium'));
		}
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
		$header = $this->getChildByTagName($this->recordData, 'header');
		if ($header != null) {
			$controlInfo = $this->getChildByTagName($header, 'controlInfo');
			if ($controlInfo != null) {
				$artInfo = $this->getChildByTagName($controlInfo, 'artinfo');
				if ($artInfo != null) {
					$tig = $this->getChildByTagName($artInfo, 'tig');
					if ($tig != null) {
						$atl = $this->getChildByTagName($tig, 'atl');
						return (string)$atl;
					}
				}
			}
		}
		return 'Unknown';
	}

	private function getChildByTagName(?SimpleXMLElement $parentNode, string $name): ?SimpleXMLElement {
		if ($parentNode != null) {
			foreach ($parentNode->children() as $child) {
				if ($child->getName() == $name) {
					return $child;
				}
			}
		}
		return null;
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
		if (!is_null($this->recordData)) {
			$header = $this->getChildByTagName($this->recordData, 'header');
			if ($header != null) {
				return $header->attributes()['shortDbName'] . ':' . $header->attributes()['uiTag'] . ':' . $header->attributes()['uiTerm'];
			}
		}
		return "";
	}

	public function getId() {
		return $this->getUniqueID();
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
		$header = $this->getChildByTagName($this->recordData, 'header');
		if ($header != null) {
			$controlInfo = $this->getChildByTagName($header, 'controlInfo');
			if ($controlInfo != null) {
				$artInfo = $this->getChildByTagName($controlInfo, 'artinfo');
				if ($artInfo != null) {
					$ab = $this->getChildByTagName($artInfo, 'ab');
					if ($ab != null) {
						return (string)$ab;
					}
				}
			}
		}
		return '';
	}

	public function getMoreDetailsOptions() {
		// TODO: Implement getMoreDetailsOptions() method.
	}

	public function getFormats() {
		$header = $this->getChildByTagName($this->recordData, 'header');
		if ($header != null) {
			$controlInfo = $this->getChildByTagName($header, 'controlInfo');
			if ($controlInfo != null) {
				$artInfo = $this->getChildByTagName($controlInfo, 'artinfo');
				if ($artInfo != null) {
					$ougenre = $this->getChildByTagName($artInfo, 'ougenre');
					if ($ougenre != null) {
						return (string)$ougenre;
					}
					$pubType = $this->getChildByTagName($artInfo, 'pubtype');
					$docType = $this->getChildByTagName($artInfo, 'doctype');
					if ($docType != null) {
						if (!empty($pubType)) {
							return (string)$pubType . ' - ' . (string)$docType;
						} else {
							return (string)$docType;
						}
					} elseif ($pubType != null) {
						return (string)$pubType;
					}
				}
			}
		}
		return "Unknown";
	}

	public function getCleanISSN() {
		return '';
	}

	public function getSourceDatabase() {
		$header = $this->getChildByTagName($this->recordData, 'header');
		if ($header != null) {
			return $header->attributes()['longDbName'];
		}
		return '';
	}

	public function getPrimaryAuthor() {
		return $this->getAuthor();
	}

	public function getAuthor() {
		$header = $this->getChildByTagName($this->recordData, 'header');
		if ($header != null) {
			$controlInfo = $this->getChildByTagName($header, 'controlInfo');
			if ($controlInfo != null) {
				$artInfo = $this->getChildByTagName($controlInfo, 'artinfo');
				if ($artInfo != null) {
					$tig = $this->getChildByTagName($artInfo, 'aug');
					if ($tig != null) {
						$atl = $this->getChildByTagName($tig, 'au');
						return (string)$atl;
					}
				}
				$illusInfo = $this->getChildByTagName($controlInfo, 'illusinfo');
				if ($illusInfo != null) {
					return $illusInfo->attributes()['type'];
				}
			}
		}

		return "";
	}

	public function getExploreMoreInfo() {
		return [];
	}

	public function getAllSubjectHeadings() {
		$subjectHeadings = [];
		$header = $this->getChildByTagName($this->recordData, 'header');
		if ($header != null) {
			$controlInfo = $this->getChildByTagName($header, 'controlInfo');
			if ($controlInfo != null) {
				$artInfo = $this->getChildByTagName($controlInfo, 'artinfo');
				if ($artInfo != null) {
					$su = $this->getChildByTagName($artInfo, 'su');
					if ($su != null) {
						foreach ($su->children() as $child) {
							$subjectHeadings[] = (string)$child;
						}
					}
				}
			}
		}
		return $subjectHeadings;
	}

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

	private function getPublishers() {
		$publishers = [];
		$header = $this->getChildByTagName($this->recordData, 'header');
		if ($header != null) {
			$controlInfo = $this->getChildByTagName($header, 'controlInfo');
			if ($controlInfo != null) {
				$pubInfo = $this->getChildByTagName($controlInfo, 'pubinfo');
				if ($pubInfo != null) {
					$publisher = $this->getChildByTagName($pubInfo, 'pub');
					if ($publisher != null) {
						$publishers[] = (string)$publisher;
					}
				}
			}
		}
		return $publishers;
	}

	private function getPublicationPlaces() {
		$publicationPlaces = [];
		$header = $this->getChildByTagName($this->recordData, 'header');
		if ($header != null) {
			$controlInfo = $this->getChildByTagName($header, 'controlInfo');
			if ($controlInfo != null) {
				$pubInfo = $this->getChildByTagName($controlInfo, 'pubinfo');
				if ($pubInfo != null) {
					$place = $this->getChildByTagName($pubInfo, 'place');
					if ($place != null) {
						$publicationPlaces[] = (string)$place;
					}
				}
			}
		}
		return $publicationPlaces;
	}

	private function getPublicationDates() {
		$publicationDates = [];
		$header = $this->getChildByTagName($this->recordData, 'header');
		if ($header != null) {
			$controlInfo = $this->getChildByTagName($header, 'controlInfo');
			if ($controlInfo != null) {
				$pubInfo = $this->getChildByTagName($controlInfo, 'pubinfo');
				if ($pubInfo != null) {
					$dt = $this->getChildByTagName($pubInfo, 'dt');
					if ($dt != null) {
						$publicationDates[] = $dt->attributes()['day'] . '/' . $dt->attributes()['month'] . '/' . $dt->attributes()['year'];
					}
				}
			}
		}
		return $publicationDates;
	}

	protected $_actions = null;

	public function getRecordActions() {
		if ($this->_actions === null) {
			$this->_actions = [];
			$this->_actions[] = [
				'title' => translate([
					'text' => 'Access Online',
					'isPublicFacing' => true,
				]),
				'url' => '/Summon/AccessOnline?id=' . $this->getUniqueID(),
				'requireLogin' => true,
				'type' => 'summon_access_online',
			];
		}
		return $this->_actions;
	}


    // public function getExploreMoreInfo() {
    //     return [];
    // }

    // public function getTitle() {
    //     $title = $this->getShortTitle();
    //     $sub = $this->getSubTitle();
    //     $title = empty($sub) ? $title : "{$title}: {$sub}";
    //    return $title;
    // }

    // public function getShortTitle() {
    //     return isset($this->fields['Title']) ? $this->fields['Title'][0] : ' ';
    // }

    // public function getBookcoverUrl($size = 'small', $absolutePath = false)
    // {
    //     return [];
    // }

    // public function getModule(): string {
	// 	return 'Summon';
	// }

    // /**
	//  * Assign necessary Smarty variables and return a template name to
	//  * load in order to display the full record information on the Staff
	//  * View tab of the record view page.
	//  *
	//  * @access  public
	//  * @return  string              Name of Smarty template file to display.
	//  */
	// public function getStaffView() {
	// 	return null;
	// }

    // 	/**
	//  * Return the unique identifier of this record within the Solr index;
	//  * useful for retrieving additional information (like tags and user
	//  * comments) from the external MySQL database.
	//  *
	//  * @access  public
	//  * @return  string              Unique identifier.
	//  */
	// public function getUniqueID() {
	// 	if (isset($this->recordData)) {
	// 		return (string)$this->recordData->Header->DbId . ':' . (string)$this->recordData->Header->An;
	// 	}else{
	// 		return null;
	// 	}
	// }

    // public function getDescription() {
	// 	if (!empty($this->recordData->Items)) {
	// 		/** @var stdClass $item */
	// 		foreach ($this->recordData->Items as $item) {
	// 			if ($item->Name == 'Abstract') {
	// 				return strip_tags($item->Data);
	// 			}
	// 		}
	// 	}
	// 	return '';
	// }

    // public function getMoreDetailsOptions() {
	// 	// TODO: Implement getMoreDetailsOptions() method.
	// }


    // public function query($query, $returnErr = false, $raw = false) {
    //     //query params
    //     $options = $query->getOptionsArray();
    //     $options['s.role'] = $this->authedUser ? 'authenticated' : 'none';

    //     if (isset($options['s.fvf']) && is_array($options['s.fvf']) && in_array('ContentType, Newspaper Article, true', $options['s.fvf'])
    //         && in_array('ContentType, Newspaper Article', $options['s.fvf'])
    //     ) {
    //         return array(
    //             'recordCount' => 0,
    //             'documents' => array()
    //         );
    //     }
    //     $this->debugPrint('Query: ' . print_r($options, true));

    //     try {
    //         $result = $this->call($options, 'search', 'GET', $raw);
    //     } catch (SerialsSolutions_Summon_Exception $e) {
    //         if ($returnErr) {
    //             return array (
    //                 'recordCount' => 0,
    //                 'documents' => array(),
    //                 'errors' => $e->getMessage()
    //             );
    //         } else {
    //             $this->handleFatalError($e);
    //         }
    //     }
    //     return $result;
    // }

    // protected function call($params = array(), $service = 'search', $method = 'GET', $raw = false) {
    //     $baseUrl = $this->host . '/' . $this->version . '/' . $service;

    //     //query
    //     $query = array();
    //     foreach ($params as $function => $value) {
    //         if (is_array($value)) {
    //             foreach ($value as $additional) {
    //                 $additional = urlencode($additional);
    //                 $query[] = "$function = $additional";
    //             }
    //         } elseif (!is_null($value)) {
    //             $value = urlencode($value);
    //             $query[] = "$function=$value";
    //         }
    //     }
    //     asort($query);
    //     $queryString = implode('&', $query);

    //     //headers
    //     $headers = array(
    //         'Accept' => 'application/'.$this->responseType,
    //         'x-summon-date' => gmdate('D, d M Y H:i:s T'),
    //         'Host' => 'api.summon.serialsolutions.com'
    //     );
    //     $data = implode("\n", $headers) . "\n/$this->version/$service\n" . urldecode($queryString) . "\n";
    //     $hmacHash = $this->hmacsha1($this->apiKey, $data);
    //     $headers['Authorization'] = "Summon $this->apiId; $hmacHash";
    //     if ($this->sessionId) {
    //         $headers['x-summon-session-id'] = $this->sessionId;
    //     }

    //     //request
    //     $result = $this->httpRequest($baseUrl, $method, $queryString, $headers);
    //     if (!$raw) {
    //         $result = $this->process($result);
    //     }
    //     return $result;
    // }


    // protected function process($input) {
    //     if ($this->responseType !== "json") {
    //         return $input;
    //     }
    //     $result = json_decode($input, true);

    //     if (!$result) {
    //         $result = array(
    //             'recordCount' => 0,
    //             'documents' => array(),
    //             'errors' => array(
    //                 array(
    //                     'code' => 'PHP=Internal',
    //                     'message' => 'Cannot decode JSON response: ' . $input
    //                 )
    //             )
    //         );
    //     }

    //     //errors
    //     if (isset($result['errors']) && is_array($result['errors'])) {
    //         foreach ($result['errors'] as $current) {
    //             $errors[] = "{$current['code']}: {$current['message']}";
    //         }
    //         $msg = 'Unable to process query<br />Summon returned: '
    //             .implode('<br />', $errors);
    //         throw new SerialsSolutions_Summon_Exception($msg);
    //     }
    //     return $result;
    // }
    // protected function hmacsha1($key, $data) {
    //     $blocksize = 64;
    //     $hashfunc = 'sha1';
    //     if (strlen($key) >$blocksize) {
    //         $key = pack('H*', $hashfunc($key));
    //     }
    //     $key = str_pad($key, $blocksize, chr(0x00));
    //     $ipad = str_repeat(chr(0x36), $blocksize);
    //     $opad = str_repeat(chr(0x5c), $blocksize);
    //     $hmac = pack(
    //         'H*', $hashfunc(
    //             ($key^$opad).pack(
    //                 'H*', $hashfunc(
    //                     ($key^$ipad).$data
    //                 )
    //             )
    //         )
    //     );
    //     return base64_encode($hmac);
    // }

    // abstract public function handleFatalError($e);

    //  abstract protected function httpRequest($baseUrl, $method, $queryString, $headers);
}