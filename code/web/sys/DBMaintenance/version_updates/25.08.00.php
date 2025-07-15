<?php

function getUpdates25_08_00(): array {
	$curTime = time();
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark - Grove

		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		// Laura Escamilla - ByWater Solutions

		//alexander - Open Fifth
		'control_display_of_user_dropdown_in_community_engagement_admin_view' => [
			'title' => 'Control User Select Type in Admin View',
			'description' => 'Add options for how to select users in the admin view section',
			'sql' => [
				"ALTER TABLE library ADD COLUMN communityEngagementAdminUserSelect VARCHAR(20) DEFAULT 'dropdown'",
			],
		], //control_display_of_user_dropdown_in_community_engagement_admin_view
		'display_only_users_from_current_library_in_user_search_admin_view' => [
			'title' => 'Display Only Users From Current Library in User Search Admin View',
			'description' => 'Add option to display users from all libraries or only the current library location when searching by user in Admin View',
			'sql' => [
				"ALTER TABLE library ADD COLUMN displayOnlyUsersForLocationInUserAdmin TINYINT(1) DEFAULT 0",
			],
		], //display_only_users_from_current_library_in_user_search_admin_view
		'allow_admin_to_enroll_users_via_admin_view' => [
			'title' => 'Allow Admin To Enroll Users Via Admin View',
			'description' => 'Add control over whether admin can enroll users via the admin view page',
			'sql' => [
				"ALTER TABLE library ADD COLUMN allowAdminToEnrollUsersInAdminView TINYINT(1) DEFAULT 0",
			],
		], //allow_admin_to_enroll_users_via_admin_view
		'add_table_for_extra_credit' => [
			'title' => 'Add Table For Extra Credit',
			'description' => 'Add a table to for extra credit activites',
			'sql' => [
				"CREATE TABLE ce_extra_credit (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					name VARCHAR(100) NOT NULL, 
					description VARCHAR(225),
					allowPatronProgressInput TINYINT DEFAULT 0
				)ENGINE = InnoDB"
			],
		],
		'add_extra_credit_to_campaigns' => [
			'title' => 'Add Extra Credit to Campaigns',
			'description' => 'Add the ability to add extra credit activities to campaigns',
			'sql' => [
				"ALTER TABLE ce_campaign ADD COLUMN addExtraCreditActivities TINYINT DEFAULT 0 "
			],
		],
		'add_campaign_extra_credit_activities' => [
			'title' => 'Add Campaign Extra Credit Activities',
			'description' => 'Add a new table to link campaigns and extra credit activities',
			'sql' => [
				"CREATE TABLE ce_campaign_extra_credit (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					weight INT(11) NOT NULL DEFAULT 0,
					campaignId INT NOT NULL,
					extraCreditId INT NOT NULL,
					goal INT DEFAULT 0, 
					reward INT(11) DEFAULT -1
				)ENGINE = InnoDB",
			],
		],
		'add_extra_credit_progress_table' => [
			'title' => 'Add Extra Credit Progress Table',
			'description' => 'Store progress for of extra credit activites for each user',
			'sql' => [
				"CREATE Table ce_campaign_extra_credit_activity_users_progress (
					 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					 userId INT NOT NULL,
					 campaignId INT NOT NULL,
					 extraCreditId INT NOT NULL,
					 progress INT NOT NULL,
					 rewardGiven TINYINT DEFAULT 0
				)ENGINE = InnoDB",
			],
		],
		'add_ability_to_highlight_campaigns_open_for_enrollment' => [
			'title' => 'Add Ability to Highlight Campaigns Open For Enrollment',
			'description' => 'Allow libraries to choose whether to display campaigns open for enrollment in the campaign highlights banner',
			'sql' => [
				"ALTER TABLE library ADD COLUMN highlightCommunityEngagementOpenToEnroll TINYINT(1) DEFAULT 0",
			],
		], //add_ability_to_highlight_campaigns_open_for_enrollment
		'add_admin_control_over_digital_reward_display' => [
			'title' => 'Add Admin Control Over Digital Reward Display',
			'description' => 'Add the option for libraries to choose whether the digital reward displays all the time or only once awarded',
			'sql' => [
				"ALTER TABLE library ADD COLUMN displayDigitalRewardOnlyWhenAwarded TINYINT(1) DEFAULT 0",
			]
		], //add_admin_control_over_digital_reward_display
		'add_ability_to_upload_placeholder_image' => [
			'title' => 'Add Ability to Upload Placeholder Image',
			'description' => 'Add the ability to upload a placeholder image',
			'sql' => [
				"ALTER TABLE library ADD COLUMN digitalRewardPlaceholderImage VARCHAR(100) DEFAULT ''",
			]
		], //add_ability_to_upload_placeholder_image
			'create_campaign_location_access' => [
			'title' => 'Create Campaign Location Access',
			'description' => 'Add table for location campaign access',
			'sql' => [
				"CREATE TABLE ce_campaign_location_access (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					campaignId INT NOT NULL,
					locationId INT NOT NULL
				) ENGINE = InnoDB",
			],
		], //create_campaign_location_access

		//chloe - Open Fifth


		//Jacob - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

		//Talpa Search
		
	];
}
