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
		'grapes_web_builder' => [
			'title' => 'Web Builder Basic Pages',
			'description' => 'Setup Basic Pages within Web Builder',
			'sql' => [
				"CREATE TABLE grapes_web_builder (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					title VARCHAR(100) NOT NULL,
					urlAlias VARCHAR(100),
					teaser VARCHAR(512)
				) ENGINE=INNODB",
			],
		],
		'remove_grapes_js_module' => [
			'title' => 'Remove Seperate Grapes JS Module',
			'description' => 'Remove seperate Grapes JS Module form modules table',
			'sql' => [
				"ALTER TABLE modules DROP COLUMN `Grapes Web Builder`;"
			],
		],
	];
}