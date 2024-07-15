<?php

function getUpdates24_08_00(): array {
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

		//mark - ByWater

		//kirstien - ByWater

		//kodi - ByWater

		//katherine - ByWater

		//alexander - PTFS-Europe

		//pedro - PTFS-Europe

		//chloe - PTFS-Europe
		'show_in_search_facet_column' => [
			'title' => 'Show In Search Facet Column',
			'description' => 'Adds the showInSearchFacet column to the Location table',
			// 'continueOnError' => false,
			'sql' => [
				'ALTER TABLE location ADD COLUMN showInSearchFacet TINYINT(1) DEFAULT 1'
			]
			],
		//other

	];
}