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
					templateName VARCHAR(255) NOT NULL UNIQUE,
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
				'ALTER TABLE grapes_web_builder MODIFY COLUMN templateId VARCHAR(250) UNIQUE',
			],
		],
		'add_templateId_column_to_templates_table' => [
			'title' => 'Add templateId column to templates table',
			'description' => 'Add a new column to store the templateId in the templates table',
			'sql' => [
				'ALTER TABLE templates ADD COLUMN templateId VARCHAR(250) UNIQUE',
			],
		],
		'change_template_name_data_type' => [
			'title' => 'Change Template Name Data Type',
			'description' => 'Change template name data type to varchar',
			'sql' => [
				'ALTER TABLE grapes_web_builder MODIFY COLUMN templateNames INT(11)',
			],
		],
		'modify_template_name_column' => [
			'title' => 'Change Template Column',
			'description' => 'Change template name column to not allow NULL',
			'sql' => [
				'ALTER TABLE grapes_web_builder MODIFY COLUMN templateNames INT(11) NOT NULL',
			],
		],
		'modify_template_name_column_add_default' => [
			'title' => 'Change Template Column',
			'description' => 'Change template name column to add default',
			'sql' => [
				'ALTER TABLE grapes_web_builder MODIFY COLUMN templateNames INT(11) DEFAULT -1',
			],
		],
		'add_new_template_column' => [
			'title' => 'Add New Template Column',
			'description' => 'Add  template column to grapes table',
			'sql' => [
				'ALTER TABLE grapes_web_builder ADD COLUMN templatesSelect INT(11) DEFAULT -1',
			],
		],
		'add_template_content_to_grapes_web_builder' => [
			'title' => 'add_column_for_template_content',
			'description' => 'add_column_in_grapes_web_builder_table_for_template_content',
			'sql' => [
				'ALTER TABLE grapes_web_builder ADD COLUMN templateContent TEXT',
			],
		],
		'create_table_for_grapes_page_saved_as_page' => [
			'title' => 'add_table_for_created_grapes_page',
			'description' => 'add_table_for_created_grapes_page_saved_as_page',
			'sql' => [
				"CREATE TABLE created_grapes_page (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					title VARCHAR(100) NOT NULL,
					urlAlias VARCHAR(100) NOT NULL,
					htmlData TEXT NOT NULL DEFAULT ' ',
					cssData TEXT NOT NULL DEFAULT ' ',
					assets TEXT NOT NULL DEFAULT '[]',
					components TEXT NOT NULL DEFAULT '[]',
					styles TEXT NOT NULL DEFAULT '[]'
				) ENGINE=INNODB",
			],
		],
		'remove_temaplteID_column' => [
			'title' => 'Remove templateId column from templates table',
			'description' => 'Remove tempalteId column from templates table',
			'sql' => [
				'ALTER TABLE templates DROP COLUMN templateId',
			],
		],
		'delete_column_from_grapes_web_builder' => [
			'title' => 'Delete column from grapes_web_builder table',
			'description' => 'Delte templateId from grapes_web_builder table',
			'sql' => [
				'ALTER TABLE grapes_web_builder DROP COLUMN templateId',
			],
		],
	];
}

function addTemplatesToDatabase(){
    global $aspen_db;
    $templates = [];

    $templateFilePaths = [
		ROOT_DIR . '/interface/themes/responsive/WebBuilder/Templates/noTemplate.html',
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
		// $id = $aspen_db->quote($template['templateId']);
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


