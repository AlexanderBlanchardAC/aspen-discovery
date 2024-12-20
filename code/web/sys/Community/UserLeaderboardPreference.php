<?php

class UserLeaderboardPreference extends DataObject {
    public $__table = 'ce_user_leaderboard_preference';
    public $id;
    public $userId;
    public $globalOptIn;

    public static function getObjectStructure($context = ''):array {
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
            'globalOptIn' => [
                'property' => 'globalOptIn',
                'type' => 'checkbox',
                'label' => 'Opt in to all Leaderboard',
                'description' => 'Whether or not to opt in to all leaderboards',
                'default' => true,
            ],
        ];
    }

    public static function getGlobalLeaderboardPrefByUser($userId) {
        $preference = new UserLeaderboardPreference();
        $preference->userId = $userId;

        if ($preference->find(true)) {
            return $preference->globalOptIn;
        }
        return null;
    }

    public function ensureGlobalLeaderboardPref($userId) {
        $preference = new UserLeaderboardPreference();
        $preference->userId = $userId;

        if (!$preference->find(true)) {
            $preference->globalOptIn = 1;
            if ($preference->insert()) {
                return true;
            } else {
                throw new RuntimeException("Failed to create leaderboard preference for user ID: $userId");
            }
        }
        return false;
    }

}
