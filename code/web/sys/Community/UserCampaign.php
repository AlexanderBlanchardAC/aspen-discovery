<?php
    require_once ROOT_DIR . '/sys/Community/CampaignMilestone.php';
    require_once ROOT_DIR . '/sys/Community/CampaignMilestoneUsersProgress.php';

class UserCampaign extends DataObject {
    public $__table = 'ce_user_campaign';
    public $id;
    public $userId;
    public $campaignId;
    public $enrollmentDate;
    public $unenerollmentDate;
    public $completed;
    public $rewardGiven;

    public static function getObjectStructure($context = ''): array {
        return [
            'id' => [
                'property' => 'id',
                'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
            ],
            'userId' => [
                'property' => 'userId',
                'type' => 'label',
				'label' => 'User Id',
				'description' => 'The unique id of the user',
            ],
            'campaignId' => [
                'property' => 'campaignId',
                'type' => 'label',
				'label' => 'Campaign Id',
				'description' => 'The unique id of the campaign',
            ],
            'enrollmentDate' => [
                'property' => 'enrollmentDate',
                'type' => 'date',
				'label' => 'Enrollment Date',
				'description' => 'The Date of Enrollment',
            ],
            'unenrollmentDate' => [
                'property' => 'unenrollmentDate',
                'type' => 'date',
				'label' => 'Unenrollment Date',
				'description' => 'The Date of Unenrollment',
            ],
            'completed' => [
                'property' => 'completed',
                'type' => 'checkbox',
				'label' => 'Campaign Complete',
				'description' => 'Whether or not the campaign is complete',
                'default' => false,
            ],
            'rewardGiven' => [
                'property' => 'rewardGiven',
                'type' => 'checkbox',
                'label' => 'Reward Given',
                'description' => 'Whether or not the reward for completing the campaign has been given',
                'default' => false,
            ],
        ];
    }

    //Check if the user has completed the campaign
    public function checkCompletionStatus() {
        //Get milestones for campaign
        $milestones = CampaignMilestone::getMilestoneByCampaign($this->campaignId);
        $isComplete = true;

        foreach ($milestones as $milestone) {
            $userProgress = CampaignMilestoneUsersProgress::getProgressByMilestoneId($milestone->id, $this->campaignId, $this->userId);
            $goal = CampaignMilestone::getMilestoneGoalCountByCampaign($this->campaignId, $milestone->id);

            if ($userProgress < $goal) {
                $isComplete = false;
                break;
            }
        }
        return $isComplete;
    }

    public function checkMilestoneCompletionStatus() {
        $milestones = CampaignMilestone::getMilestoneByCampaign($this->campaignId);
        $milestoneCompletionStatus = [];

        foreach ($milestones as $milestone) {
            //User's progress for this milestone
            $userProgress = CampaignMilestoneUsersProgress::getProgressByMilestoneId($milestone->id, $this->campaignId, $this->userId);

            //Goal for this milestone
            $goal = CampaignMilestone::getMilestoneGoalCountByCampaign($this->campaignId, $milestone->id);

            //Check if milestone is complete
            $isMilestoneComplete = ($userProgress >= $goal);

            //Add to array
            $milestoneCompletionStatus[$milestone->id] = $isMilestoneComplete;
        }

        return $milestoneCompletionStatus;
    }

    function getTotalEnrollmentsThisMonth() {
        $userCampaign = new UserCampaign();

        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');

        $userCampaign->whereAdd("enrollmentDate >= '$startOfMonth'");
        $userCampaign->whereAdd("enrollmentDate <= '$endOfMonth'");

        var_dump($userCampaign->count());
        return $userCampaign->count();
    }

    function getTotalEnrollmentsLastMonth() {
        $userCampaign = new UserCampaign();

        $lastMonthStart = date('Y-m-01', strtotime('first day of last month'));
        $lastMonthEnd = date('Y-m-t', strtotime('last day if last month'));

        $userCampaign->whereAdd("enrollmentDate >= '$lastMonthStart'");
        $userCampaign->whereAdd("enrollmentDate <= '$lastMonthEnd'");

        return $userCampaign->count();
    }

    function getTotalEnrollmentsThisYear() {
        $userCampaign = new UserCampaign();

        $startOfYear = date('Y-01-01');
        $today = date('Y-m-d');

        $userCampaign->whereAdd("enrollmentDate >= '$startOfYear'");
        $userCampaign->whereAdd("enrollmentDate <= '$today'");

        return $userCampaign->count();
    }

    function getTotalUnenrollmentsThisMonth() {
        $userCampaign = new UserCampaign();

        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');

        $userCampaign->whereAdd("unEnrollmentDate >= '$startOfMonth'");
        $userCampaign->whereAdd("unEnrollmentDate <= '$endOfMonth'");

        return $userCampaign->count();
    }

}