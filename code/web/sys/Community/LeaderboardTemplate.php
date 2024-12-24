<?php

class LeaderboardTemplate extends DataObject {
    public $__table = 'ce_leaderboard_template';
    public $id;
    public $userId;
    public $htmlContent;
    public $cssContent;

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
				'description' => 'The id of the user creating the template',
            ],
            'htmlContent' => [
                'property' => 'htmlContent',
                'type' => 'label',
				'label' => 'html Content',
				'description' => 'The html content for the updated leaderboard display',
            ],
            'cssContent' => [
                'property' => 'cssContent',
                'type' => 'label',
				'label' => 'css Content',
				'description' => 'The css content for the updated leaderboard display',
            ],
        ];
    }
}