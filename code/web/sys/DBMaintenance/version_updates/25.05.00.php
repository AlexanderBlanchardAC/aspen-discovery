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
					description VARCHAR(100) NOT NULL,
					allowPatronProgressInput TINYINT DEFAULT 0
				)ENGINE = InnoDB"
			],
		],

		//chloe - PTFS-Europe

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}