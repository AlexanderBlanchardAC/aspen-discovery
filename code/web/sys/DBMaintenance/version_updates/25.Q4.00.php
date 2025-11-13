<?php

/** @noinspection PhpUnused */
function getUpdates25_Q4_00(): array {
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		 //alexander - Open Fifth
		 'change_data_types_for_grapes_js_columns' => [
			'title' => 'Change Data Types For Grapes JS Columns',
			'description' => 'Update column types to allow for longer pages',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE grapes_web_builder MODIFY templateContent LONGTEXT",
				"ALTER TABLE grapes_web_builder MODIFY htmlData LONGTEXT",
				"ALTER TABLE grapes_web_builder MODIFY cssData LONGTEXT",
			]
		], //change_data_types_for_grapes_js_columns
		'add_event_date_format_to_event_types_and_events' => [
			'title' => 'Add Event Date Format To Event Types And Events',
			'description' => 'Add the ability to select a format in which to display othe revents in the series',
			'sql' =>[
				"ALTER TABLE event_type ADD COLUMN eventDateFormat TINYINT(1) DEFAULT 0",
				"ALTER TABLE event ADD COLUMN eventDateFormat TINYINT(1) DEFAULT 0",
			]
		], //add_event_date_format_to_event_types_and_events
	];
}