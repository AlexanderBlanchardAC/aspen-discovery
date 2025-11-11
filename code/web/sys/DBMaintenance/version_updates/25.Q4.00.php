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
		'add_default_event_calendar_display_dropdown' => [
			'title' => 'Add Default Event Calendar Display Dropdown',
			'description' => 'Add the option of selecting the default display for the native events calendar',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library ADD COLUMN eventsDefaultCalendarView TINYINT(1) NOT NULL DEFAULT 0",
			],
		], //add_default_event_calendar_display_dropdown
	];
}