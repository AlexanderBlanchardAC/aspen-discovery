<?php

function getUpdates25_09_00(): array {
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
		'increase_location_display_name_allowed_length' => [
			'title' => 'Increase Location Display Name Allowed Length',
			'description' => 'Increase the allowed length for the location display name',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE location MODIFY displayName VARCHAR(100) NOT NULL'
			],
		], // increase_location_display_name_allowed_length
	];
}
