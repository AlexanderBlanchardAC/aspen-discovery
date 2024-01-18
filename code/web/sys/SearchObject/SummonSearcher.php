<?php

require_once ROOT_DIR . '/sys/SearchObject/BuildQuery.php';
require_once ROOT_DIR . '/sys/Summon/SummonSettings.php';
require_once ROOT_DIR . '/sys/Pager.php';
require_once ROOT_DIR . '/sys/SearchObject/BaseSearcher.php';

class SearchObject_SummonSearcher extends SearchObject_BaseSearcher{

    static $instance;
	/** @var SummonSettings */
    private $summonBaseApi ='http://api.summon.serialssolutions.com';

	/**Build URL */
    private $sessionId;
    private $version = '2.0.0';
    private $service = 'search';
    private $responseType = "json";

    private static $searchOptions;
   
    private $curl_connection;

	/**Track query time info */
    protected $queryStartTime = null;
	protected $queryEndTime = null;
	protected $queryTime = null;

    // STATS
	protected $resultsTotal = 0;

	protected $searchTerms;

	protected $lastSearchResults;

    // Module and Action for building search results 
	protected $resultsModule = 'Search';
	protected $resultsAction = 'Results';

    	/** @var string */
	protected $searchSource = 'local';
    protected $searchType = 'basic';

/** Values for the options array*/
	protected $holdings = false;
	protected $didYouMean = false;
	protected $language = 'en';
	protected $idsToFetch = array();
	/**@var int */
	protected $maxTopics = 1;
	protected $groupFilters = array();
	protected $rangeFilters = array();
	protected $openAccessFilter = false;
	protected $highlight = false;
	protected $expand = false;
	protected $sortOptions = array();
	/**
	 * @var string
	 */
	protected $defaultSort = 'relevance';
	protected $query;
	protected $filters = array();
	/**
	 * @var int
	 */
	protected $limit= 20;
	/**
	 * @var int
	 */
	protected $page = 1;
	/**
	 * @var int
	 */
	protected $maxRecDb = 2;
	protected $bookMark;
	protected $debug = false;
	protected $journalTitle = false;
	protected $lightWeightRes = false;
	protected $sort = null;
	  /**
	 * @var string mixed
	 */
	private $searchIndex = 'Everything';
	/**Facets, filters and limiters */
	//Values for the main facets - each has an array of available values
	protected $facets = [
		'Author,or',
		'ContentType,or,1,30',
		'SubjectTerms,or,1,30',	
		'Discipline,or,1,30',
		'Language,or,1,30',
		'DatabaseName,or,1,30',
		'SourceType,or,1,30',	
	];
	protected $facetFields;
	//Options to filter results by - checkbox, single value
	protected $limitOptions = [
		'Full Text Online',
		'Is Scholarly',
		'Peer Reviewed',
		'Open Access',
		'Available in Library Collection',
	];

    public function __construct() {
        //Initialize properties with default values
        $this->searchSource = 'summon';
        $this->searchType = 'summon';
        $this->resultsModule = 'Summon';
        $this->resultsAction = 'Results';
	}
     
    /**
	 * Initialise the object from the global
	 *  search parameters in $_REQUEST.
	 * @access  public
	 * @param string $searchSource
	 * @return  boolean
	 */
	public function init($searchSource = null) {
		//********************
		// Check if we have a saved search to restore -- if restored successfully,
		// our work here is done; if there is an error, we should report failure;
		// if restoreSavedSearch returns false, we should proceed as normal.
		$restored = $this->restoreSavedSearch();
		if ($restored === true) {
			//there is a saved search that can be reused
			return true;
		} elseif ($restored instanceof Exception) {
			//there is an error with hte restored search
			return false;
		}
		//Carry out a new search
		//********************
		// Initialize standard search parameters
		$this->initView();
		$this->initPage();
		$this->initSort();
		$this->initFilters();
		$this->initLimiters();

		//********************
		// Basic Search logic
		if (!$this->initBasicSearch()) {
			$this->initAdvancedSearch();
		}

		// If a query override has been specified, log it here
		if (isset($_REQUEST['q'])) {
			$this->query = $_REQUEST['q'];
		}
		return true;
	}

	/**
	 * Create an instance of the Summon Searcher
	 * @return SearchObject_SummonSearcher
	 */
	 public static function getInstance() {
	if (SearchObject_SummonSearcher::$instance == null) {
		SearchObject_SummonSearcher::$instance = new SearchObject_SummonSearcher();
		}
		return SearchObject_SummonSearcher::$instance;
	}

	/**
	 * Retreive settings for institution's summon connector
	*/
	private function getSettings() {
		global $library;
		if (!str_contains( $library->summonSettingsId, -1 )) {
			$summonSettings = new SummonSettings();
			$summonSettings->id = $library->summonSettingsId;
			if (!$summonSettings->find(true)) {
				$summonSettings = null;
			}
			return $summonSettings;
		}
		AspenError::raiseError(new AspenError('There are no Summon Settings set for this library system.'));
	}

    public function getCurlConnection() {
		if ($this->curl_connection == null) {
            $this->curl_connection = curl_init();
			curl_setopt($this->curl_connection, CURLOPT_CONNECTTIMEOUT, 15);
			curl_setopt($this->curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
			curl_setopt($this->curl_connection, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curl_connection, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curl_connection, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($this->curl_connection, CURLOPT_TIMEOUT, 30);
			curl_setopt($this->curl_connection, CURLOPT_RETURNTRANSFER, TRUE);
		}
		return $this->curl_connection;
	}

	public function getHeaders() {
		$headers = array(
			'Accept' => 'application/'.$this->responseType,
			'x-summon-date' => gmdate('D, d M Y H:i:s T'),
			'Host' => 'api.summon.serialssolutions.com'
		);
		return $headers;
	}

	/**
	 * Use Institution's Summon API credentials to authenticate and allow connection with the Summon API
	*/
    public function authenticate($settings, $queryString) {
		$headers = $this->getHeaders();
		$data = implode("\n", $headers). "\n/$this->version/search\n" . urldecode($queryString) . "\n";
				$hmacHash = $this->hmacsha1($settings->summonApiPassword, $data);
				$headers['Authorization'] = "Summon $settings->summonApiId;$hmacHash";
                if (!is_null($this->sessionId)){
                    $headers['x-summon-session-id'] = $this->sessionId;
                } 
		return $headers;
    }

	public function getSort() {
		$this->sortOptions = array(
			'Relevance',
			'Reverse',
			'Date(newest)',
			'Date(oldest)',
		);
		foreach ($this->sortOptions as $sortOption) {
			$sortOption = $sortOption . ':' . $this->facetFields;
		}
	}

	//Build an array of options that will be passed into the final query string that will be sent to the Summon API
	public function getOptions () {
		$options = array(
			's.q' => $this->query,
			//set to default at top of page
			's.ps' => $this->limit,
			//set to default at top of page
			's.pn' => $this->page,
			//set to default -  false at top of page
			's.ho' => $this->holdings ? 'true' : 'false',
			//set to default -  false at top of page
			's.dym' => $this->didYouMean ? 'true' : 'false',
			//set to default - en at top of page
			's.l' => $this->language,
			//set to an empty array
			's.fids' =>$this->idsToFetch,
			//set in array 
			's.ff' =>$this->facets,
			//empty array,
			's.fvf' => $this->filters,
			//set to default 1 at top of page
			's.rec.topic.max' => $this->maxTopics,
			//empty array
			's.fvgf' => $this->groupFilters,
			//empty arrray
			's.rf' => $this->rangeFilters,
			//uses options defined in array 
			's.sort' => $this->getSort(),
			//false by default
			's.exp' => $this->expand ? 'true' : 'false',
			//false by default
			's.oaf' => $this->openAccessFilter ? 'true' : 'false',
			//to bookmark an item - top of page
			's.bookMark' => $this->bookMark,
			//false by default
			's.debug' => $this->debug ? 'true' : 'false',
			//false by default - recommend journals
			's.rec.jt' => $this->journalTitle ? 'true' : 'false',
			//false by default
			's.light' => $this->lightWeightRes ? 'true' : 'false',
			//2 by default - max db reccomendations
			's.rec.db.max' => $this->maxRecDb,
			//allows access to records
			's.role' =>  'authenticated',			
		);
		return $options;
	}

	/**
	 * Use the data that is returned when from the API and process it to assign it to variables
	 */
	public function processData($recordData) {
			$recordData = $this->process($recordData); 
			if (is_array($recordData)){

				$this->sessionId = $recordData['sessionId'];
				$this->lastSearchResults = $recordData['documents'];
				$this->page = $recordData['query']['pageNumber'];
				$this->resultsTotal = $recordData['recordCount'];
				$this->filters = $recordData['query']['facetValueFilters'];
				$this->facetFields= $recordData['facetFields'];	
			}
		return $recordData;
	}

    /**
	 * Return an array of data summarising the results of a search.
	 *
	 * @access  public
	 * @return  array   summary of results
	 */
	public function getResultSummary() {
		$summary = [];
		$summary['page'] = $this->page;
		$summary['perPage'] = $this->limit;
		$summary['resultTotal'] = (int)$this->resultsTotal;
		// 1st record is easy, work out the start of this page
		$summary['startRecord'] = (($this->page - 1) * $this->limit) + 1;
		// Last record needs more care
		if ($this->resultsTotal < $this->limit) {
			// There are less records returned than one page, then use total results
			$summary['endRecord'] = $this->resultsTotal;
		} elseif (($this->page * $this->limit) > $this->resultsTotal) {
			// The end of the current page runs past the last record, use total results
			$summary['endRecord'] = $this->resultsTotal;
		} else {
			// Otherwise use the last record on this page
			$summary['endRecord'] = $this->page * $this->limit;
		}
		return $summary;
	}

	 /** Return a url for use by pagination template
	 *
	 * @access  public
	 * @return  string   URL of a new search
	 */
	public function renderLinkPageTemplate() {
		// Stash our old data for a minute
		$oldPage = $this->page;
		// Add the page template
		$this->page = '%d';
		// Get the new url
		$url = $this->renderSearchUrl();
		// Restore the old data
		$this->page = $oldPage;
		// Return the URL
		return $url;
	}

    /**
	 * Use the record driver to build an array of HTML displays from the search
	 * results. Called by results.php.
	 *
	 * @access  public
	 * @return  array   Array of HTML chunks for individual records.
	 */
	public function getResultRecordHTML() {
		global $interface;
		$html = [];
		if (isset($this->lastSearchResults)) {
			for ($x = 0; $x < count($this->lastSearchResults); $x++) {
				$current = &$this->lastSearchResults[$x];
				$interface->assign('recordIndex', $x + 1);
				$interface->assign('resultIndex', $x + 1 + (($this->page - 1) * $this->limit));

				require_once ROOT_DIR . '/RecordDrivers/SummonRecordDriver.php';
				$record = new SummonRecordDriver($current);
				if ($record->isValid()) {
					$interface->assign('recordDriver', $record);
					$html[] = $interface->fetch($record->getSearchResult());
				} else {
					$html[] = "Unable to find record";
				}
			}
		} $this->addToHistory();
		return $html;
	}

    /**
	 * Use the record driver to build an array of HTML displays from the search
	 * results.
	 *
	 * @access  public
	 * @return  array   Array of HTML chunks for individual records.
	 */
	public function getCombinedResultHTML() {
		global $interface;
		$html = [];
		if (isset($this->lastSearchResults)) {
			foreach($this->lastSearchResults as $key=>$value){
				$interface->assign('recordIndex', $key + 1);
				$interface->assign('resultIndex', $key + 1 + (($this->page - 1) * $this->limit));

				require_once ROOT_DIR . '/RecordDrivers/SummonRecordDriver.php';
				$record = new SummonRecordDriver($value);
				if ($record->isValid()) {
					$interface->assign('recordDriver', $record);
					$html[] = $interface->fetch($record->getCombinedResult());
				} else {
					$html[] = "Unable to find record";
				}
			}
		} else {
			$html[] = "Unable to find record";
		}
		return $html;
	} 

	//Assign properties to each of the sort options
	public function getSortList() {
		$sortOptions = $this->sortOptions;
		$list = [];
		if ($sortOptions != null) {
			foreach ($sortOptions as $sort => $label) {
				$list[$sort] = [
					'sortUrl' => $this->renderLinkWithSort($sort),
					'desc' => $label,
					'selected' => ($sort == $this->sort),
				];
			 }
		}
		return $list;
	}

	/**
	 * Return a url for the current search with a new sort
	 *
	 * @access  public
	 * @param string $newSort A field to sort by
	 * @return  string   URL of a new search
	 */
	public function renderLinkWithSort($newSort) {
		// Stash our old data for a minute
		$oldSort = $this->sort;
		// Add the new sort
		$this->sort = $newSort;
		// Get the new url
		$url = $this->renderSearchUrl();
		// Restore the old data
		$this->sort = $oldSort;
		// Return the URL
		return $url;
	}

	/**
	 * Called in Results.php
	 */
	public function getFacetSet() {
		$availableFacets = [];
		$this->filters = [];
		if (isset($this->facetFields)) {
			foreach ($this->facetFields as $facetField) {
				$facetId = $facetField['displayName'];
				$availableFacets[$facetId] = [
					'collapseByDefault' => true,
					'multiSelect' =>true,
					'label' =>$facetId,
					'valuesToShow' =>5,
				];
				if ($facetId == 'ContentType') {
					$availableFacets[$facetId]['collapseByDefault'] = false;
				}
				$list = [];
				foreach ($facetField['counts'] as $value) {
					$facetValue = $value['value'];
					$isApplied = array_key_exists($facetId, $this->filterList);
					$facetSettings = [
						'value' => $facetValue,
						'display' =>$facetValue,
						'count' =>$value['count'],
						'isApplied' => $isApplied,
					];
					if ($isApplied) {
						$facetSettings['removalUrl'] = $this->renderLinkWithoutFilter($facetId . ':' . $facetValue);
					} else {
						$facetSettings['url'] = $this->renderSearchUrl() . '&filter[]=' . $facetId . ':' . urlencode($facetValue);
					}
					$list[] = $facetSettings;
				}
				$availableFacets[$facetId]['list'] = $list;
			}
		}
		return $availableFacets;
	}

	public function retrieveRecord ($id) {
		foreach ($this->lastSearchResults as $record) {
			if ($id == $record['ID'][0]) {
				return $record;
			}
		}
		return null;
	}
 
	public function addFacetValueFilter() {
		$filter = $this->getFilters();
         if(is_array($filter)) {
			foreach ($filter as $eachFilter) {
				$this->filters .=$eachFilter;
		 	} 
		} else {
			$this->filters[] = $filter;
		}
	}
	
	public function getFilters() {
	  foreach($this->filterList as $filter){
		return $filter;
	  }
	}
	/*
	* Called by Results.php - Lists checkbox filter options
	TODO - Checkbox not staying ticked although moved to applied list - change to Summon filters
	* 
	*/
	public function getLimitList() {
		$limitList = [];
	    foreach ($this->limitOptions as $limitOption) {
			$isApplied = array_key_exists($limitOption, $this->filterList);
			$limitList[$limitOption] = [
				'value' => $limitOption,
				'display' =>$limitOption,
				'isApplied' => $isApplied,
			];
		}	
		return $limitList;
	}
	
	/**
	 * Generate an HMAC hash for authentication
	 *
	 * @param string $key  Hash key
	 * @param string $data Data to hash
	 *
	 * @return string      Generated hash
 	*/
	protected function hmacsha1($key, $data) {
		$blocksize=64;
		$hashfunc='sha1';
		if (strlen($key)>$blocksize) {
			$key=pack('H*', $hashfunc($key));
		}
		$key=str_pad($key, $blocksize, chr(0x00));
		$ipad=str_repeat(chr(0x36), $blocksize);
		$opad=str_repeat(chr(0x5c), $blocksize);
		$hmac = pack(
			'H*', $hashfunc(
				($key^$opad).pack(
					'H*', $hashfunc(
						($key^$ipad).$data
					)
				)
			)
		);
		return base64_encode($hmac);
	}

	/**
	 * Send a fully built query string to the API with user authentication
	 * @throws Exception
	 * @return object API response
	 */
	public function sendRequest() {
		$baseUrl = $this->summonBaseApi . '/' .$this->version . '/' .$this->service;
		$settings = $this->getSettings();
		$this->startQueryTimer();
		$query = array();
		$options = $this->getOptions();
		foreach ($options as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $additionalValue) {
					$query[] = "$key=$additionalValue";
				}
			} elseif (!is_null($value)) {
				$value = urlencode($value);
				$query[] = "$key=$value";
			}
		}
		//Summon query must be sent in alphabetical order, otherwise it will return an error
		asort($query);
		$queryString = implode('&', $query);
		$headers = $this->authenticate($settings, $queryString);
		$recordData = $this->httpRequest($baseUrl, $queryString, $headers);
		if (!empty($recordData)){
			$recordData = $this->processData($recordData); 
			$this->stopQueryTimer();
		}
		return $recordData;
	}

    public function process($input) {
        if (SearchObject_SummonSearcher::$searchOptions == null) {
            if ($this->responseType != 'json') {
                return $input;
            }
            SearchObject_SummonSearcher::$searchOptions = json_decode($input, true);
            if (!SearchObject_SummonSearcher::$searchOptions) {
                SearchObject_SummonSearcher::$searchOptions = array(
                    'recordCount' => 0,
                    'documents' => array(),
                    'errors' => array(
                        array(
                            'code' => 'PHP-Internal',
                            'message' => 'Cannot decode JSON response: ' . $input
                        )
                    )
                );
            }
               // Detect errors
            if (isset(SearchObject_SummonSearcher::$searchOptions['errors']) && is_array(SearchObject_SummonSearcher::$searchOptions['errors'])) {
                foreach (SearchObject_SummonSearcher::$searchOptions['errors'] as $current) {
                    $errors[] = "{$current['code']}: {$current['message']}";
                }
                $msg = 'Unable to process query<br />Summon returned: ' .
                    implode('<br />', $errors);
                throw new Exception($msg);
            }
            if (SearchObject_SummonSearcher::$searchOptions) {
                return SearchObject_SummonSearcher::$searchOptions;
            } else {
                return null;
            }
        } else {
            return SearchObject_SummonSearcher::$searchOptions;
        }
    }

    /**
	 * Send HTTP request with headers modified to meet Summon API requirements
	 */
    protected function httpRequest($baseUrl, $queryString, $headers) {
		foreach ($headers as $key =>$value) {
			$modified_headers[] = $key.": ".$value;
		}
        $curlConnection = $this->getCurlConnection();
        $curlOptions = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => "{$baseUrl}?{$queryString}",
            CURLOPT_HTTPHEADER => $modified_headers
        );
        curl_setopt_array($curlConnection, $curlOptions);
        $result = curl_exec($curlConnection);
        if ($result === false) {
            throw new Exception("Error in HTTP Request.");
        }
        // curl_close($curlConnection);
        return $result;
    }

	/**
	 * Start the timer to work out how long a query takes.  Complements
	 * stopQueryTimer().
	 *
	 * @access protected
	 */
	protected function startQueryTimer() {
		// Get time before the query
		$time = explode(" ", microtime());
		$this->queryStartTime = $time[1] + $time[0];
	}

	/**
	 * End the timer to work out how long a query takes.  Complements
	 * startQueryTimer().
	 *
	 * @access protected
	 */
	protected function stopQueryTimer() {
		$time = explode(" ", microtime());
		$this->queryEndTime = $time[1] + $time[0];
		$this->queryTime = $this->queryEndTime - $this->queryStartTime;
	}

	/**
	 * Work out how long the query took
	 */
	public function getQuerySpeed() {
		return $this->queryTime;
	}

	 /**
	  * Search options specific to Summon
	  */
     public function getSearchIndexes() {
		return [
			"Everything" => translate([
				'text' => "Everything",
				'isPublicFacing' => true,
				'inAttribute' => true,
			]),
            'Books' => translate([
                'text' => "Books",
				'isPublicFacing' => true,
				'inAttribute' => true,
            ]),
            'Articles' => translate([
                'text' => "Articles",
                'isPublicFacing' => true,
                'inAttribute' => true,
            ])
		];
     }

	 //Used in Union/Ajax - getSummonResults
    public function getDefaultIndex() {
		return $this->searchIndex;
	}

    public function setSearchTerm($searchTerm) {
		if (strpos($searchTerm, ':') !== false) {
			[
				$searchIndex,
				$term,
			] = explode(':', $searchTerm, 2);
			$this->setSearchTerms([
				'lookfor' => $term,
				'index' => $searchIndex,
			]);
		} else {
			$this->setSearchTerms([
				'lookfor' => $searchTerm,
				'index' => $this->getDefaultIndex(),
			]);
		}
	}

    public function getIndexError() {
		// TODO: Implement getIndexError() method.
	}

	public function buildRSS($result = null) {
		// TODO: Implement buildRSS() method.
	}

	public function buildExcel($result = null) {
		// TODO: Implement buildExcel() method.
	}

	public function getResultRecordSet() {
		// TODO: Implement getResultRecordSet() method.
	}

	function getSearchName() {
		return $this->searchSource;
	}

	function loadValidFields() {
		// TODO: Implement loadValidFields() method.
	}

	function loadDynamicFields() {
		// TODO: Implement loadDynamicFields() method.
	}

    public function getEngineName() {
		return 'summon';
	}

	function getSearchesFile() {
		return false;
	}

	public function getSessionId() {
		return $this->sessionId;
	}

	public function getresultsTotal(){
		return $this->resultsTotal;
	}

	public function processSearch($returnIndexErrors = false, $recommendations = false, $preventQueryModification = false) { 
    }

	public function __destruct() {
		if ($this->curl_connection) {
			curl_close($this->curl_connection);
		}
	}

}