<?php
require_once ROOT_DIR . '/services/MyAccount/MyAccount.php';
require_once ROOT_DIR . '/sys/Community/Campaign.php';
require_once ROOT_DIR . '/sys/Community/CampaignMilestone.php';
require_once ROOT_DIR . '/sys/Community/Milestone.php';
require_once ROOT_DIR . '/sys/Community/UserCompletedMilestone.php';
require_once ROOT_DIR . '/sys/Account/User.php';

class MyCampaigns extends MyAccount {

    function launch() {
        global $interface;
        global $library;
        // require_once ROOT_DIR . '/sys/Community/Campaign.php';

        $campaign = new Campaign();
          //Get User
          $userId = $this->getUserId();
          $interface->assign('userId', $userId);
          $hasLinkedUsers = UserAccount::hasLinkedUsers();
          $interface->assign('hasLinkedUsers', $hasLinkedUsers);
          $linkedCampaigns = $this->getLinkedUserCampaigns($userId);

          $interface->assign('linkedCampaigns', $linkedCampaigns);

        //Get Campaigns
        $campaignList = $this->getCampaigns();
        $interface->assign('campaignList', $campaignList);

        //Get past campaigns
        $pastCampaigns = $campaign->getPastCampaigns($userId);
        $interface->assign('pastCampaigns', $pastCampaigns);


        $this->display('../MyAccount/myCampaigns.tpl', ',My Campaigns');
    }

    function getUserId() {
        $user = UserAccount::getLoggedInUser();
        $userId = $user->id;
        return $userId;
    }

    function getCampaigns() {
        $campaign = new Campaign();
        $campaignList = [];

        if (!UserAccount::isLoggedIn()) {
            return $campaignList;
        }
        $user = UserAccount::getLoggedInUser();
        $userId = $user->id;

        //Get active campaigns
        $activeCampaigns = Campaign::getActiveCampaignsList();

        //Get upcoming campaigns - those starting in the next month
        $upcomingCampaigns = Campaign::getUpcomingCampaigns();

        //Get campaigns
        $campaign->find();
        while ($campaign->fetch()) {
            $campaignId = $campaign->id;

            //Find out if user is enrolled in campaign
            $campaign->enrolled = $campaign->isUserEnrolled($userId);
            $campaign->leaderboardOptIn = $campaign->isUserOptedInToLeaderboard($userId);

            //Find out if campaign is active
            $campaign->isActive = isset($activeCampaigns[$campaignId]);

            //Find out if campaign in upcoming
            $campaign->isUpcoming = isset($upcomingCampaigns[$campaignId]);

            //Get campaign reward details
            $rewardDetails = $campaign->getRewardDetails();
            if ($rewardDetails !== null) {
                $campaign->rewardName = $rewardDetails['name'];
                $campaign->rewardType = $rewardDetails['rewardType'];
                $campaign->badgeImage = $rewardDetails['badgeImage'];
            } else {
                $campaign->rewardName = null;
                $campaign->rewardType = null;
                $campaign->badgeImage = null;
            }

            // if ($campaign->enrolled) {
                //Fetch milestones for this campaign
                $milestones = CampaignMilestone::getMilestoneByCampaign($campaignId);
                $completedMilestonesCount = 0;
                $numCampaignMilestones = 0;
                $milestoneProgressData = [];

                //Store progress for each milestone
                $campaign->milestoneProgress = [];


                foreach ($milestones as $milestone) {
                    $milestoneId = $milestone->id;
                    $numCampaignMilestones++;

                    //Calculate milestone progress
                    $milestoneProgress = CampaignMilestone::getMilestoneProgress($campaignId, $userId, $milestone->id);
                    $progressData = CampaignMilestoneProgressEntry::getUserProgressDataByMilestoneId($userId, $milestoneId, $campaignId);

                    $milestone->progress = $milestoneProgress['progress'];
                    $milestone->completedGoals = $milestoneProgress['completed'];
                    $milestone->totalGoals = CampaignMilestone::getMilestoneGoalCountByCampaign($campaignId, $milestoneId);
                    $milestone->progressData = $progressData;
                    // $milestone->rewardName = CampaignMilestone::getMilestoneRewardByCampaignId($campaignId, $milestoneId);
                

                    //Get completed milestones for user
                    // $completedMilestones = UserCompletedMilestone::getCompletedMilestones($userId, $campaignId);
                    // foreach ($completedMilestones as $completedMilestone) {
                    //     if ($completedMilestone->milestoneId == $milestoneId) {
                    //         $completedMilestonesCount++;
                    //     }
                    // }
                }
                //Add completed milestones count to campaign object
                // $campaign->numCompletedMilestones = $completedMilestonesCount;
                $campaign->numCampaignMilestones = $numCampaignMilestones;

                $userCampaign = new UserCampaign();
                $userCampaign->userId = $userId;
                $userCampaign->campaignId = $campaignId;
                $milestoneCompletionStatus = $userCampaign->checkMilestoneCompletionStatus();
                $campaign->numCompletedMilestones = count(array_filter($milestoneCompletionStatus));

                //Add milestones to campaign object
                $campaign->milestones = $milestones;

                //Add the campaign to the list
            // }
            $campaignList[] = clone $campaign;
        }
        return $campaignList;
    }


    function getLinkedUserCampaigns($userId) {
        if (empty($userId)) {
            throw new InvalidArgumentException("User ID is required.");
        }
        $user = new User();
        $user->id = $userId;

        if (!$user->find(true)) {
            throw new RuntimeException("User not found.");
        }

        $linkedUsers = $user->getLinkedUsers();
        if (empty($linkedUsers)){
            return [];
        }

    
        $groupedLinkedCampaigns = [];

        foreach ($linkedUsers as $linkedUser) {
            $eligibleCampaigns = [];
            $campaign = new Campaign();

            if ($campaign->find()) {
                while ($campaign->fetch()) {
                    $userCampaign = new UserCampaign();
                    $userCampaign->userId = $linkedUser->id;
                    $userCampaign->campaignId = $campaign->id;

                    $isEnrolled = $userCampaign->find(true);
                    $eligibleCampaigns[] = [
                        'campaignId' => $campaign->id, 
                        'campaignName' => $campaign->name,
                        'isEnrolled' => $isEnrolled
                    ];
                }
            }

            $groupedLinkedCampaigns[] = [
                'linkedUserName' => $linkedUser->displayName,
                'linkedUserId' => $linkedUser->id,
                'campaigns' => $eligibleCampaigns
            ];
        }
        return $groupedLinkedCampaigns;
    }


    //TODO:: Write a function that uses the milestone id for each progress bar to use the ce_milestone_progress_entries table and 
    //get information about which books were checked out and count towards the milestone. 
    function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'Campaigns');
		return $breadcrumbs;
    }
}