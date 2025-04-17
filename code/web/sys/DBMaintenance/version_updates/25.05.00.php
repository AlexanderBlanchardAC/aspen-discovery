<?php

function getUpdates25_05_00(): array {
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

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		//alexander - PTFS-Europe
		'add_table_for_extra_credit' => [
			'title' => 'Add Table For Extra Credit',
			'description' => 'Add a table to for extra credit activites',
			'sql' => [
				"CREATE TABLE ce_extra_credit (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					name VARCHAR(100) NOT NULL, 
					description VARCHAR(255),
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

		//chloe - PTFS-Europe

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}