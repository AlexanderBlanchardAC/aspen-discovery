<?php 

require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/sys/Community/Campaign.php';


class Community_UsageGraphs extends Admin_Admin {
    function launch() {
        global $interface;
        $title = 'Campaign Usage Graph';
        $stat = $_REQUEST['stat'];
        $instanceName = ' ';


        $interface->assign('graphTitle', $title);
        $interface->assign('section', 'Campaigns');
        $interface->assign('showCSVExportButton', true);   
        $this->assignGraphSpecificTitle($stat);
        $this->getAndSetInterfaceDataSeries($stat, $instanceName);  
        $interface->assign('stat', $stat);
        $interface->assign('propName', 'exportToCSV');
        $this->display('../Community/usage-graph.tpl', $title);
    }

    function getBreadcrumbs(): array {
        $breadcrumbs = [];
        return $breadcrumbs;
    }

    function getActiveAdminSection(): string
    {
        return 'community';
    }

    function canView(): bool
    {
        return true;
    }

    public function buildCSV() {
        global $interface;

		$stat = $_REQUEST['stat'] ?? null;
		if (!empty($_REQUEST['instance'])) {
			$instanceName = $_REQUEST['instance'];
		} else {
			$instanceName = '';
		}

        if (empty($stat)) {
            echo "Error: Misising required 'stat' parameter. ";
            exit();
        }

		$this->getAndSetInterfaceDataSeries($stat, $instanceName);
		$dataSeries = $interface->getVariable('dataSeries');

        if (empty($dataSeries)) {
            $filename = "CampaignUsageData_{$stat}_NoData.csv";
            header("Content-Type: text/csv; charset=utf-8");
            header("Content-Disposition: attachment;filename={$filename}");
            $fp = fopen('php://output', 'w');
            fputcsv($fp, ['No data available for the selected parameters.']);
            fclose($fp);
            exit();
        }

		$filename = "CampaignUsageData_{$stat}.csv";
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: text/csv; charset=utf-8');
		header("Content-Disposition: attachment;filename={$filename}");
		$fp = fopen('php://output', 'w');


		$graphTitles = array_keys($dataSeries);
		$numGraphTitles = count($dataSeries);

		// builds the header for each section of the table in the CSV - column headers: Dates, and the title of the graph
		for($i = 0; $i < $numGraphTitles; $i++) {
			$dataSerie = $dataSeries[$graphTitles[$i]];
			$numRows = count($dataSerie['data']);
			$dates = array_keys($dataSerie['data']);
			$header = ['Dates', $graphTitles[$i]];
			fputcsv($fp, $header);

			if( empty($numRows)) {
				fputcsv($fp, ['no data found!']);
			}
			// builds each subsequent data row - aka the column value
			for($j = 0; $j < $numRows; $j++) {
				$date = $dates[$j];
				$value = $dataSerie['data'][$date];
				$row = [$date, $value];
				fputcsv($fp, $row);
			}
		}
        fclose($fp);
		exit();
    }

    private function getAndSetInterfaceDataSeries($stat, $instanceName) {
        global $interface;
        $dataSeries = [];
        $columnLabels = [];

        if ($stat == 'allCampaigns') {
            $campaigns = new Campaign();

            $campaigns->selectAdd();
            $campaigns->selectAdd('SUM(enrollmentCounter) as sumEnrollmentCounter');
            $campaigns->selectAdd('SUM(unEnrollmentCounter) as sumUnenrollmentCounter');
            $campaigns->find();
        

            $completedCount = 0;

            while ($campaigns->fetch()) {
                $campaignId = $campaigns->id;
                $campaign = new Campaign();
                $campaign->id = $campaignId;
                if ($campaign->find(true)) {
                    $completedCount += $campaign->getCompletedUsersCount();
                }
            
                $curPeriod = date('m-Y');
                $columnLabels[] = $curPeriod;
                

                $dataSeries['Enrollments'] = [
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'data' => [$curPeriod => $campaigns->sumEnrollmentCounter ?? 0],
                ];

                $dataSeries['Unenrollments'] = [
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'data' => [$curPeriod => $campaigns->sumUnenrollmentCounter ?? 0],
                ];

                $dataSeries['Completed'] = [
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'data' => [$curPeriod => $completedCount ?? 0],
                ];
            }
        } elseif (is_numeric($stat)) {
            $campaign = new Campaign();
            $campaign->id = $stat;

            if ($campaign->find(true)) {
                $completedCount = $campaign->getCompletedUsersCount();

                $curPeriod = date('m-Y');
                $columnLabels[] = $curPeriod;

                $dataSeries['Enrollments'] = [
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'data' => [$curPeriod => $campaign->enrollmentCounter ?? 0],
                ];

                $dataSeries['Unenrollments'] = [
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'data' => [$curPeriod => $campaign->unenrollmentCounter ?? 0],
                ];

                $dataSeries['Completed'] = [
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'data' => [$curPeriod => $completedCount ?? 0],
                ];
            } else {
                $interface->assign('error', 'Campaign not found');
            }
        }

        $interface->assign('columnLabels', $columnLabels);
        $interface->assign('dataSeries', $dataSeries);
        $interface->assign('translateDataSeries', true);
        $interface->assign('translateColumnLabels', false);

    }

    public function assignGraphSpecificTitle($stat) {
        global $interface;
        $title = $interface->getVariable('graphTitle');
        switch ($stat) {
            case 'allCampaigns':
                $title .= ' - All Campaigns';
                break;
            case is_numeric($stat):
                $campaign = new Campaign();
                $campaign->id = $stat;
                if ($campaign->find(true)) {
                    $title .= ' - ' . $campaign->name;
                } else {
                    $title .= ' - Campaign Not Found';
                }
                break;
            default:
                $title .= ' ';
                break;
        }
            $interface->assign('graphTitle', $title); 
    }
}