<?php
require_once ROOT_DIR . '/JSON_Action.php';
require_once ROOT_DIR . '/sys/Community/Campaign.php';
require_once ROOT_DIR . '/sys/Community/UserCampaign.php';
require_once ROOT_DIR . '/sys/Community/CampaignMilestoneUsersProgress.php';
require_once ROOT_DIR . '/sys/UserAccount.php';


class Community_AJAX extends JSON_Action {
    function campaignRewardGivenUpdate() {
        $userId = $_GET['userId'];
        $campaignId = $_GET['campaignId'];
        $userCampaign = new UserCampaign();
        $userCampaign->userId = $userId;
        $userCampaign->campaignId = $campaignId;

        if ($userCampaign->find(true)) {
            $userCampaign->rewardGiven = 1;
            if ($userCampaign->update()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update reward status.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User campaign record not found.']);
        }
        exit;
    }

    function milestoneRewardGivenUpdate() {
        ob_start();

        try {
            $userId = $_GET['userId'];
            $milestoneId = $_GET['milestoneId'];

            $campaignMilestoneProgress = new CampaignMilestoneUsersProgress();
            #TODO: Add a campaignId check
            $campaignMilestoneProgress->userId = $userId;
            $campaignMilestoneProgress->ce_milestone_id = $milestoneId;

            if ($campaignMilestoneProgress->find(true)) {
                $campaignMilestoneProgress->rewardGiven = 1;

                if ($campaignMilestoneProgress->update()) {
                    ob_end_clean();
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Failed to update reward status');
                }
            } else {
                throw new Exception('Milestone progress record not found.');
            }

        } catch(Exception $e) {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    function filterCampaigns() {

        $campaignId = isset($_REQUEST['campaignId']) ? intval($_REQUEST['campaignId']) : 0;
        $userId = isset($_REQUEST['userId']) ? intval($_REQUEST['userId']) : 0;

        $response = [];

        if ($campaignId > 0) {

            $campaign = Campaign::getCampaignById($campaignId);
            if ($campaign) {

                $html = '<div class="dashboardCategory row" style="border: 1px solid #3174AF; padding: 0 10px 10px 10px; margin-bottom: 10px;">';
                $html .= '<div class="col-sm-12">';
                $html .= "<h2 class=\"dashboardCategoryLabel\"><a href=\"/Community/CampaignTable?id={$campaignId}\">" . htmlspecialchars($campaign->name) . "</a></h2>";
                $html .= '<div style="border-bottom: 2px solid #3174AF; padding: 10px; margin-bottom: 10px;">';
                $html .= '<div class="dashboardLabel">Number of Patrons Enrolled:</div>';
                $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->currentEnrollments) . '</div>';
                $html .= '<div class="dashboardLabel">Total Number of Enrollments:</div>';
                $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->enrollmentCounter) . '</div>';
                $html .= '<div class="dashboardLabel">Total Number of Unenrollments:</div>';
                $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->unenrollmentCounter) . '</div>';
                $html .= '</div>'; 
                $html .= '</div>'; 
                $html .= '</div>'; 

                $response['html'] = $html;
                $response['success'] = true;
            } else {
                $response['message'] = 'Campaign not found';
            }   
        } elseif ($userId > 0) {
            $userCampaigns = Campaign::getUserEnrolledCampaigns($userId);

            if (!empty($userCampaigns)) {
                foreach ($userCampaigns as $campaign) {
                    $html = '<div class="dashboardCategory row" style="border: 1px solid #3174AF; padding: 0 10px 10px 10px; margin-bottom: 10px;">';
                    $html .= '<div class="col-sm-12">';
                    $html .= "<h5 style=\"font-weight:bold;\"><a href=\"/Community/CampaignTable?id={$campaign->id}\">" .htmlspecialchars($campaign->name) . "</a></h5>";
                    $html .= '<div style="border-bottom: 2px solid #3174AF; padding: 10px; margin-bottom: 10px;">';
                    $html .= '<div class="dashboardLabel">Number of Patrons Enrolled: </div>';
                    $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->currentEnrollments) . '</div>';
                    $html .= '<div class="dashboardLabel">Number of Enrollments: </div>';
                    $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->enrollmentCounter) . '</div>';
                    $html .= '<div class="dashboardLabel">Number of UnEnrollments: </div>';
                    $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->unenrollmentCounter) . '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                }

                $response['html'] = $html;
                $response['success'] = true;
            } else {
                $response['message'] = 'No campaigns found for this user.';
            }
        } else {
            $response['message'] = 'No valid campaign or user specified.';
        }

        

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    public function exportUsageData() {
        require_once ROOT_DIR . '/services/Community/UsageGraphs.php';
        $campaignUsageGraph = new Community_UsageGraphs();
        $campaignUsageGraph->buildCSV();
    }

    public function filterLeaderboardCampaigns() {
        require_once ROOT_DIR . '/sys/Community/Campaign.php';

        $campaignId = $_GET['campaignId'] ?? null;
        $response = [];
        $campaign = new Campaign();
        try {
            if ($campaignId) {
                $leaderboard = $campaign->getLeaderboardByCampaign($campaignId);
                if ($leaderboard) {
                    if (isset($leaderboard['message'])) {
                        $response['success'] = true;
                        $response['message'] = $leaderboard['message'];
                    } else {
                        $html ='<table><thead><tr><th>User</th><th>Rank</th><th>Completed Milestones</th></tr></thead><tbody>';
                        foreach ($leaderboard as $entry) {
                        $html .= "<tr><td>{$entry['user']}</td><td>{$entry['rankDisplayed']}</td><td>{$entry['completedMilestones']}</td></tr>";
                        }
                        $html .= '</tbody></table>';
                        $response['html'] = $html;
                        $response['success'] = true;
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = 'No leaderboard data for this campaign.';
                }
              
            } else {
                $leaderboard = $campaign->getOverallLeaderboard();
                if ($leaderboard) {
                    if (isset($leaderboard['message'])) {
                        $response['success'] = true;
                        $response['message'] = $leaderboard['message'];
                    } else {
                        $html ='<table><thead><tr><th>User</th><th>Rank</th><th>Completed Milestones</th></tr></thead><tbody>';
                        foreach ($leaderboard as $entry) {
                            $html .= "<tr><td>{$entry['user']}</td><td>{$entry['rankDisplayed']}</td><td>{$entry['completedMilestones']}</td></tr>";
                        }
                        $html .= '</tbody></table>';
                        $response['html'] = $html;
                        $response['success'] = true;
                    }   
                } else {
                    $response['success'] = false;
                    $response['message'] = 'No leaderboard data found.';
                }
               
            }

            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            error_log('Error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error retrieving campaign information'
            ]);
        }
    }

    public function getLeaderboardPage() {
        $tplPath = ROOT_DIR . '/interface/themes/responsive/Community/leaderboard.tpl';
        global $interface;

        if (file_exists($tplPath)) {
            $content = $interface->fetch($tplPath);

            //Extract body content
            preg_match('/<body.*?>(.*?)<\/body>/is', $content, $matches);
            $htmlContent = isset($matches[1]) ? $matches[1] : '';

            //Remove GrapesJS div
            $htmlContent = preg_replace('/<div id=["\']gjs["\'].*?>.*?<\/div>/is', '', $htmlContent);

            //Remove customization button
            $htmlContent = preg_replace('/<button[^>]*onclick="AspenDiscovery.CommunityEngagement.customizeLeaderboard\(\)"[^<]*>.*?<\/button>/is', '', $htmlContent);

            //Remove dropdown filter
            $htmlContent = preg_replace('/<label[^>]*for=["\']campaignFilter["\'].*?>.*?<\/select>/is', '', $htmlContent);

            //Remove the loading spinner
            $htmlContent = preg_replace('/<div id=["\']loading-placeholder["\'][^>]*>.*?<p>.*?<\/p>.*?<\/div>/is', '', $htmlContent);
            //Extract all CSS styles
            preg_match_all('/<style.*?>(.*?)<\/style>/s', $content, $cssMatches);
            $css = isset($cssMatches[1]) ? implode("\n", $cssMatches[1]) : '';

            //Handle external CSS
            preg_match_all('/<link.*?href=["\'](.*?)["\'].*?>/is', $content, $linkMatches);
            $externalCss = '';

            foreach($linkMatches[1] as $cssUrl) {
                $fullPath = ROOT_DIR . '/interface/themes/responsive/Community/' . basename($cssUrl);
                if (file_exists($fullPath)) {
                    $externalCss .= file_get_contents($fullPath) . "\n";
                }
            }

            preg_match_all('/style=["\'](.*?)["\']/is', $content, $inlineCssMatches);
            $inlineCss = isset($inlineCssMatches[1]) ? implode(";\n", $inlineCssMatches[1]) : '';

            $combinedCss = trim($css . "\n" . $externalCss . "\n" .  $inlineCss);


            return [
                'success' =>true,
                'html' => $htmlContent,
                'css' => $combinedCss
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Leaderboard template file not found.'
            ];
        }
    }

    public function getUpdatedLeaderboardPage() {
        require_once ROOT_DIR . '/sys/Community/LeaderboardTemplate.php';
		require_once ROOT_DIR . '/sys/UserAccount.php';
        try {
            $userId = UserAccount::getActiveUserId();
            $template = new LeaderboardTemplate();

            $template->whereAdd('userId = ' .  $template->escape($userId));
            $template->find();

            if ($template->fetch()) {
                return [
                    'success' => true,
                    'html' => $template->htmlContent,
                    'css' => $template->cssContent
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No custom leaderboard found for this user.'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    //TODO:: Next hide reset leaderboard button if the current display is not customized
    public function getDefaultLeaderboardDisplay() {
        require_once ROOT_DIR . '/sys/Community/LeaderboardTemplate.php';
        require_once ROOT_DIR . '/sys/UserAccount.php';

        $userId = UserAccount::getActiveUserId();
        $template = new LeaderboardTemplate();
        $template->whereAdd('userId = ' .  $template->escape($userId));
        $template->find();

        if ($template->fetch()) {
            $template->delete();
        }
        return [
            'success' => true,
        ];      
    }
}