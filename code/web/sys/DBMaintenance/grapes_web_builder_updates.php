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
		'add_template_options' => [
			'title' => 'Add Template Options',
			'description' => 'Add Template Options for Grapes Pages',
			'sql' => [
				"ALTER TABLE grapes_web_builder ADD COLUMN pageType INT",
			],
		],
		'template_options_for_grapes_web_builder' => [
			'title' => 'Template Options for Grapes Web Builder',
			'description' => 'Store templates for Grapes Web Builder',
			'sql' => [
				"CREATE TABLE template_options (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					templateName VARCHAR(100) NOT NULL,
					contents TEXT NOT NULL
				)ENGINE=INNODB",
			],
		],
		'templates_for_grapes_web_builder' => [
			'title' => 'Templates for Grapes Web Builder',
			'description' => 'Store templates for Grapes Web Builder',
			'sql' => [
				"CREATE TABLE templates (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					templateName VARCHAR(255) NOT NULL,
					templateDescription TEXT,
					templateFilePath VARCHAR(255) NOT NULL
				)ENGINE=INNODB",
			],
		],
		'alter_template_option_type_remove_and_re_add' => [
			'title' => 'Alter Type of Template Options',
			'description' => 'Alter Template Options for Grapes Pages',
			'sql' => [
				"ALTER TABLE grapes_web_builder DROP COLUMN pageType",
			],
		],
		're_add_page_type_with_new_data_type' => [
			'title' => 'Alter Type of Template Options',
			'description' => 'Alter Template Options for Grapes Pages',
			'sql' => [
				"ALTER TABLE grapes_web_builder ADD COLUMN pageType VARCHAR(512)",
			],
		],
		'alter_templates_table_remove_description' => [
			'title' => 'Remove Description From Templates Table',
			'description' => 'Remove the description column from the template table',
			'sql' => [
				"ALTER TABLE templates DROP COLUMN templateDescription",
			],
		],
		'alter_temapltes_table_add_content' => [
			'title' => 'Add Content to Templates Table',
			'description' => 'Add content column to templates table',
			'sql' => [
				"ALTER TABLE templates ADD COLUMN templateContent TEXT",
			],
		],
		'add_default_for_template_file_path' => [
			'title' => 'Add Default to Template File Path',
			'description' => 'Add default value to template file path in templates table',
			'sql' => [
				"ALTER TABLE templates MODIFY COLUMN templateFilePath VARCHAR(255) DEFAULT NULL",
			]
		]
	];
}

// function addTemplatesToDatabase(){
// 	global $aspen_db;
// 	$templates = [];

// 	$templateFilePaths = [
// 		ROOT_DIR . '/interface/themes/responsive/WebBuilder/Templates/template1.html',
// 		ROOT_DIR . '/interface/themes/responsive/WebBuilder/Templates/template2.html',
// 		ROOT_DIR . '/interface/themes/responsive/WebBuilder/Templates/template3.html',
// 	];

// 	foreach ($templateFilePaths as $templateFilePath) {
// 		if (!file_exists($templateFilePath)) {
// 			continue;
// 		}

// 		$templateContent = file_get_contents($templateFilePath);
// 		$templateName = basename($templateFilePath, '.html');
		
// 		$existingTemplate = $aspen_db->query("SELECT id FROM templates WHERE templateName = ?", [$templateName])->fetch();


// 		if (!$existingTemplate) {
//             $templates[] = [
//                 'templateName' => $templateName,
//                 'templateContent' => $templateContent,
//             ];
//         }
    
// 		$templates[] = [
// 			'templateName' => $templateName,
// 			'templateContent' => $templateContent,
// 		];
	
// 	}

// 	if (empty($templates) || !is_array($templates)) {
// 		return false;
// 	}


// 	$query = "INSERT INTO templates (templateName, templateContent) VALUES ";
// 	$values = [];
// 	foreach ($templates as $template) {
// 		$name = $aspen_db->quote($template['templateName']);
// 		$content = $aspen_db->quote($template['templateContent']);
// 		$values[] = "($name, $content)";
// 	}

// 	$query .= implode(', ', $values);

// 	try {
// 		$aspen_db->query($query);
// 		return true;
// 	} catch (PDOException $e) {
// 		return false;
// 	}
// }
function addTemplatesToDatabase(){
    global $aspen_db;
    $templates = [];

    $templateFilePaths = [
        ROOT_DIR . '/interface/themes/responsive/WebBuilder/Templates/template1.html',
        ROOT_DIR . '/interface/themes/responsive/WebBuilder/Templates/template2.html',
        ROOT_DIR . '/interface/themes/responsive/WebBuilder/Templates/template3.html',
    ];

    foreach ($templateFilePaths as $templateFilePath) {
        if (!file_exists($templateFilePath)) {
            continue;
        }

        $templateContent = file_get_contents($templateFilePath);
        $templateName = basename($templateFilePath, '.html');

		$stmt = $aspen_db->prepare("SELECT id FROM templates WHERE templateName = ?");
		$stmt->execute([$templateName]);
		$existingTemplate = $stmt->fetch(PDO::FETCH_ASSOC);
		
        if (!$existingTemplate) {
            $templates[] = [
                'templateName' => $templateName,
                'templateContent' => $templateContent,
            ];
        }
    }

    if (empty($templates)) {
        return false; // No new templates to insert
    }

    $query = "INSERT INTO templates (templateName, templateContent) VALUES ";
    $values = [];
    foreach ($templates as $template) {
        $name = $aspen_db->quote($template['templateName']);
        $content = $aspen_db->quote($template['templateContent']);
        $values[] = "($name, $content)";
    }

    $query .= implode(', ', $values);

    try {
        $aspen_db->query($query);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
