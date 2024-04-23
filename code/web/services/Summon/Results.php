<?php
require_once ROOT_DIR . '/ResultsAction.php';
class Summon_Results extends ResultsAction {
	function launch() {
		global $interface;
		global $timer;
		global $aspenUsage;
		global $library;

		if (!isset($_REQUEST['lookfor']) || empty($_REQUEST['lookfor'])) {
			$_REQUEST['lookfor'] = '*';
		}

		$aspenUsage->summonSearches++;

		//Include Search Engine
		/** @var SearchObject_SummonSearcher $searchObject */
		$searchObject = SearchObjectFactory::initSearchObject("Summon");
		$timer->logTime('Include search engine');

		// Hide Covers when the user has set that setting on the Search Results Page
		$this->setShowCovers();

		$searchObject->init();
		$result = $searchObject->sendRequest();
		if ($result instanceof AspenError) {
			global $serverName;
			$logSearchError = true;
			if ($logSearchError) {
				try {
					require_once ROOT_DIR . '/sys/SystemVariables.php';
					$systemVariables = new SystemVariables();
					if ($systemVariables->find(true) && !empty($systemVariables->searchErrorEmail)) {
						require_once ROOT_DIR . '/sys/Email/Mailer.php';
						$mailer = new Mailer();
						$emailErrorDetails = $_SERVER['REQUEST_URI'] . "\n" . $result->getMessage();
						$mailer->send($systemVariables->searchErrorEmail, "$serverName Error processing Summon search", $emailErrorDetails);
					}
				} catch (Exception $e) {
					//This happens when the table has not been created
				}
			}

			$interface->assign('searchError', $result);
			$this->display('searchError.tpl', 'Error in Search');
			return;
		}
		$dateFilters = [
			'publishDate',
			'publicationDate',
		];
		foreach ($dateFilters as $dateFilter) {
			if ((isset($_REQUEST[$dateFilter . 'yearfrom']) && !empty($_REQUEST[$dateFilter . 'yearfrom'])) || (isset($_REQUEST[$dateFilter . 'yearto']) && !empty($_REQUEST[$dateFilter . 'yearto']))) {
				$queryParams = $_GET;
				$yearFrom = preg_match('/^\d{2,4}$/', $_REQUEST[$dateFilter . 'yearfrom']) ? $_REQUEST[$dateFilter . 'yearfrom'] : '*';
				$yearTo = preg_match('/^\d{2,4}$/', $_REQUEST[$dateFilter . 'yearto']) ? $_REQUEST[$dateFilter . 'yearto'] : '*';
				if (strlen($yearFrom) == 2) {
					$yearFrom = '19' . $yearFrom;
				} elseif (strlen($yearFrom) == 3) {
					$yearFrom = '0' . $yearFrom;
				}
				if (strlen($yearTo) == 2) {
					$yearTo = '19' . $yearTo;
				} elseif (strlen($yearFrom) == 3) {
					$yearTo = '0' . $yearTo;
				}
				if ($yearTo != '*' && $yearFrom != '*' && $yearTo < $yearFrom) {
					$tmpYear = $yearTo;
					$yearTo = $yearFrom;
					$yearFrom = $tmpYear;
				}
				// unset($queryParams['module']);
				// unset($queryParams['action']);
				// unset($queryParams[$dateFilter . 'yearfrom']);
				// unset($queryParams[$dateFilter . 'yearto']);
				if (!isset($queryParams['sort'])) {
					$queryParams['sort'] = 'year';
				}
				$queryParamStrings = [];
				foreach ($queryParams as $paramName => $queryValue) {
					if (is_array($queryValue)) {
						foreach ($queryValue as $arrayValue) {
							if (strlen($arrayValue) > 0) {
								$queryParamStrings[] = $paramName . '[]=' . urlencode($arrayValue);
							}
						}
					} else {
						if (strlen($queryValue)) {
							$queryParamStrings[] = $paramName . '=' . urlencode($queryValue);
						}
					}
				}
				if ($yearFrom != '*' || $yearTo != '*') {
					$queryParamStrings[] = "&filter[]=$dateFilter:[$yearFrom+TO+$yearTo]";
				}
				$queryParamString = join('&', $queryParamStrings);
				// header("Location: /Search/Results?$queryParamString");
				// exit;
				$interface->assign('dateFilter', $queryParamString);
				$interface->assign('testvariable-from', $yearFrom);
				$interface->assign('testvariable-to', $yearTo);
			}
		}

		$displayQuery = $searchObject->displayQuery();
		$pageTitle = $displayQuery;
		if (strlen($pageTitle) > 20) {
			$pageTitle = substr($pageTitle, 0, 20) . '...';
		}

		$interface->assign('lookfor', $displayQuery);

		// Big one - our results //
		$recordSet = $searchObject->getResultRecordHTML();
		$interface->assign('recordSet', $recordSet);
		$timer->logTime('load result records');

		$interface->assign('sortList', $searchObject->getSortList());
		$interface->assign('searchIndex', $searchObject->getSearchIndex());

		$summary = $searchObject->getResultSummary();
		$interface->assign('recordCount', $summary['resultTotal']);
		$interface->assign('recordStart', $summary['startRecord']);
		$interface->assign('recordEnd', $summary['endRecord']);

		$appliedFacets = $searchObject->getFilterList();
		$interface->assign('filterList', $appliedFacets);
		$limitList = $searchObject->getLimitList();
		$interface->assign('limitList', $limitList);
		$facetSet = $searchObject->getFacetSet();
		$interface->assign('sideFacetSet', $facetSet);

		//Figure out which counts to show.
		$facetCountsToShow = $library->getGroupedWorkDisplaySettings()->facetCountsToShow;
		$interface->assign('facetCountsToShow', $facetCountsToShow);

		if ($summary['resultTotal'] > 0) {
			$link = $searchObject->renderLinkPageTemplate();
			$options = [
				'totalItems' => $summary['resultTotal'],
				'fileName' => $link,
				'perPage' => $summary['perPage'],
			];
			$pager = new Pager($options);
			$interface->assign('pageLinks', $pager->getLinks());
		}

		$searchObject->close();
		$interface->assign('savedSearch', $searchObject->isSavedSearch());
		$interface->assign('searchId', $searchObject->getSearchId());

		// Save the ID of this search to the session so we can return to it easily:
		$_SESSION['lastSearchId'] = $searchObject->getSearchId();

		// Save the URL of this search to the session so we can return to it easily:
		$_SESSION['lastSearchURL'] = $searchObject->renderSearchUrl();

		//Setup explore more
		$showExploreMoreBar = true;
		if (isset($_REQUEST['page']) && $_REQUEST['page'] > 1) {
			$showExploreMoreBar = false;
		}
		$exploreMore = new ExploreMore();
		$exploreMoreSearchTerm = $exploreMore->getExploreMoreQuery();
		$interface->assign('exploreMoreSection', 'summon');
		$interface->assign('showExploreMoreBar', $showExploreMoreBar);
		$interface->assign('exploreMoreSearchTerm', $exploreMoreSearchTerm);

	

		$displayTemplate = 'Summon/list-list.tpl'; // structure for regular results
		$interface->assign('subpage', $displayTemplate);
		$interface->assign('sectionLabel', 'Summon');

		$interface->assign('hasSearchableFacets', $searchObject->hasSearchableFacets());

		$sidebar = $searchObject->getResultTotal() > 0 ? 'Summon/results-sidebar.tpl' : '';
		$this->display($summary['resultTotal'] > 0 ? 'list.tpl' : 'list-none.tpl', $pageTitle, $sidebar, false);
	}

	function getBreadcrumbs(): array {
		return parent::getResultsBreadcrumbs('Articles & Databases');
	}
}