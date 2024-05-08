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
			],
		],
		'add_template_content_column_to_the_grapes_page_table' => [
			'title' => 'Add Template Content Column to Grapes Table',
			'description' => 'Add a column to the Grapes table to store the content of the chosen template',
			'sql' => [
				'ALTER TABLE grapes_web_builder ADD COLUMN templateContent TEXT',
			],
		],
		'alter_contents_of_grapes_page_table' => [
			'title' => 'Alter the contents of the Grapes page table',
			'description' => 'Remove columns pageType and templateContent from the Grapes table',
			'sql' => [
				'ALTER TABLE grapes_web_builder DROP COLUMN pageType',
				'ALTER TABLE grapes_web_builder DROP COLUMN templateContent',
				'ALTER TABLE grapes_web_builder ADD COLUMN templateId INT(11) DEFAULT -1',
			],
		],
		'rename_templateId_to_tempalte_names_and_add_new_temaplate_id_column' => [
			'title' => 'Modify a column and add a new column',
			'description' => 'Add a new column for template names and modify the templateID column to alter its purpose.',
			'sql' => [
				'ALTER TABLE grapes_web_builder ADD COLUMN templateNames INT(11) DEFAULT -1',
				'ALTER TABLE grapes_web_builder MODIFY COLUMN templateId VARCHAR(250)',
			],
		],
		'add_templateId_column_to_templates_table' => [
			'title' => 'Add templateId column to templates table',
			'description' => 'Add a new column to store the templateId in the templates table',
			'sql' => [
				'ALTER TABLE templates ADD COLUMN templateId VARCHAR(250)',
			],
		],
	];
}

function addTemplatesToDatabase(){
    global $aspen_db;
    $templates = [];

    $templateFilePaths = [
		// ROOT_DIR . '/interface/themes/responsive/WebBuilder/Templates/noTemplate.html',
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
			$templateId = generateUniqueId();
            $templates[] = [
				'templateId' => $templateId,
                'templateName' => $templateName,
                'templateContent' => $templateContent,
            ];
        }
    }

    if (empty($templates)) {
        return false; // No new templates to insert
    }

    $query = "INSERT INTO templates (templateId, templateName, templateContent) VALUES ";
    $values = [];
    foreach ($templates as $template) {
		$id = $aspen_db->quote($template['templateId']);
        $name = $aspen_db->quote($template['templateName']);
        $content = $aspen_db->quote($template['templateContent']);
        $values[] = "($id, $name, $content)";
    }

    $query .= implode(', ', $values);

    try {
        $aspen_db->query($query);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function generateUniqueId() {
	return uniqid('template_', true);
}
