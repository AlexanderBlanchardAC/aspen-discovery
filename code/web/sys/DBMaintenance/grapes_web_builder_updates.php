<?php /** @noinspection SqlResolve */
function getGrapesWebBuilderUpdates() {
	return [
		'grapes_web_builder_module' => [
			'title' => 'Grapes Web Builder Module',
			'description' => 'Create Grapes Web Builder Module',
			'sql' => [
				"INSERT INTO modules (name, indexName, backgroundProcess) VALUES ('Grapes Web Builder', 'grapes_web_builder', '')",
			],
		],

		'grapes_web_builder_module_monitoring_and_indexing' => [
			'title' => 'Grapes Web Builder Module - Monitoring, indexing',
			'description' => 'Update Grapes Web Builder module to monitor logs and start indexer',
			'sql' => [
				"UPDATE modules set backgroundProcess='web_indexer', logClassPath='/sys/WebsiteIndexing/WebsiteIndexLogEntry.php', logClassName='WebsiteIndexLogEntry' WHERE name = 'Grapes Web Builder'",
			],
		],

	];
}