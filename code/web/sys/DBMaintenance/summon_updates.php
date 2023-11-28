<?php
/** @noinspection SqlResolve */
function getSummonUpdates() {
    return[
        'createSummonModules' => [
            'title' => 'Create Summon modules',
            'description' => 'Setup modules for Summon Integration',
            'sql' => [
                "INSERT INTO modules (name, indexName, backgroundProcess) VALUES ('Summon', '', '')",
            ],
        ],
        'createSettingsForSummon' => [
            'title' => 'Create Summon Settings',
            'description' => 'Create settings to store informtion for Summon Integrations',
            'continueOnError' => true,
            'sql' => [
                "CREATE TABLE summon_settings(
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(50) NOT NULL,
                    summonApiProfile VARCHAR(50) DEFAULT '',
                    summonSearchProfile VARCHAR(50) DEFAULT '',
                    summonApiUsername VARCHAR(50) DEFAULT '',
                    summonApiPassword VARCHAR(50) DEFAULT ''
                ) ENGINE INNODB",
                'ALTER TABLE library ADD COLUMN summonSettingsId INT(11) DEFAULT -1',
                'ALTER TABLE location ADD COLUMN summonSettingsId INT(11) DEFAULT -2',
                ],
            ],
        'aspen_usage_summon' => [
            'title' => 'Aspen Usage for Summon Searches',
            'description' => 'Add a column to track usage of Summon searches within Aspen',
            'continueOnError' => false,
            'sql' => [
                'ALTER TABLE aspen_usage ADD COLUMN summonSearches INT(11) DEFAULT 0',
            ],
        ],
        'track_summon_user_usage' => [
            'title' => 'Summon Usage by User',
            'description' => 'Add a table to track how often a user uses Summon.',
            'sql' => [
                "CREATE TABLE user_summon_usage (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    userId INT(11),
                    month INT(2),
                    year INT(4),
                    instance VARCHAR(100),
                    usageCount INT(11)
                ) engine = InnoDB",
                "ALTER TABLE summon_usage ADD UNIQUE INDEX (instance, userId, year, month)",             
            ],
        ],
        'summon_record_usage' => [
            'title' => 'Summon Usage',
            'description' => 'Add a table to track how Summon is used.',
            'continueOnError' => true,
            'sql' => [
                'CREATE TABLE summon_usage (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    summonId VARCHAR(100),
                    instance VARCHAR(100),
                    month INT(2),
                    year int(4),
                    timesViewedInSearch INT(11),
                    timesUsed INT(11)
                ) ENGINE = InnoDB",
                "ALTER TABLE summon_usage ADD UNIQUE INDEX (instance, summonId, year,  month)',
             ],
        ],
        'summon_research_starters' => [
            'title' => 'Summon research starters',
            'description' => 'Setup ability to handle research starters from Summon',
            'sql' => [
                'CREATE TABLE summon_research_starter (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    summonId VARCHAR(100) NOT NULL UNIQUE,
                    title VARCHAR(255)
                )',
                'CREATE TABLE summon_research_starter_dismissals (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    researchStarterId INT NOT NULL,
                    userId INT NOT NULL
                )',
                'ALTER TABLE summon_research_starter_dismissals ADD UNIQUE INDEX (userId, researchStarterId)',
             ],
        ],
        'add_summon_to_permissions' => [
            'title' => 'Summon Permissions',
            'description' => 'Allows the user to configure Summon integration for all libraries',
            'sql' => [
                "INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES
                	('Cataloging & eContent', 'Administer Summon', 'Summon', 170, 'Allows the user configure Summon integration for all libraries.') ",
                "INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer Summon'))",
            ],
        ],
        'hide_research_starters_for_users_option' => [
            'title' => 'Hide Research Starters',
            'description' => 'Handle research starters from Summon',
            'sql' => [
                'ALTER TABLE user ADD COLUMN hideResearchStartersSummon TINYINT(1) DEFAULT 0',
            ],
        ],
        'summon_facets' => [
            'title' => 'Summon facets',
            'description' => 'Store Summon facet names',
            'continueOnError' => true,
            'sql' => [
                'CREATE TABLE summon_facet (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    shortName VARCHAR(50) NOT NULL UNIQUE,
                    displayName VARCHAR(100) NOT NULL
                ) ENGINE INNODB',
            ],
        ],
        'summon_search_settings' => [
            'title' => 'Summon Search Settings',
            'description' => 'Add database searching for Summon',
            'continueOnError' => true,
            'sql' => [
                'CREATE TABLE summon_database (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    searchSettingId INT NOT NULL,
                    shortName VARCHAR(50) NOT NULL,
                    displayName VARCHAR(50) NOT NULL,
                    allowSearching TINYINT DEFAULT 1,
                    searchByDefault TINYINT DEFAULT 1,
                    showInExploreMore TINYINT DEFAULT 0,
                    showInCombinedResults TINYINT DEFAULT 0
                ) ENGINE INNODB',
                'CREATE TABLE summon_search_options (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                    name VARCHAR(50) NOT NULL,
                    settingId INT(11) NOT NULL
                ) ENGINE INNODB',
                'ALTER TABLE library ADD COLUMN summonSearchSettingId INT(11) DEFAULT -1',
                'ALTER TABLE location ADD COLUMN summinSearchSettingId INT(11) DEFAULT -2',
             ],
         ],
         'add_summon_to_permissions' => [
            'title' => 'Summon Permissions',
            'description' => 'Allows the user to configure Summon integration for all libraries',
            'sql' => [
                "INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES
                	('Cataloging & eContent', 'Administer Summon', 'Summon', 170, 'Allows the user configure Summon integration for all libraries.') ",
                "INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer Summon'))",
            ],
        ],
        'hide_research_starters_for_users_option' => [
            'title' => 'Hide Research Starters',
            'description' => 'Handle research starters from Summon',
            'sql' => [
                'ALTER TABLE user ADD COLUMN hideResearchStartersSummon TINYINT(1) DEFAULT 0',
            ],
        ],
        'summon_facets' => [
            'title' => 'Summon facets',
            'description' => 'Store Summon facet names',
            'continueOnError' => true,
            'sql' => [
                'CREATE TABLE summon_facet (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    shortName VARCHAR(50) NOT NULL UNIQUE,
                    displayName VARCHAR(100) NOT NULL
                ) ENGINE INNODB',
            ],
        ],
        'summon_search_settings' => [
            'title' => 'Summon Search Settings',
            'description' => 'Add database searching for Summon',
            'continueOnError' => true,
            'sql' => [
                'CREATE TABLE summon_database (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    searchSettingId INT NOT NULL,
                    shortName VARCHAR(50) NOT NULL,
                    displayName VARCHAR(50) NOT NULL,
                    allowSearching TINYINT DEFAULT 1,
                    searchByDefault TINYINT DEFAULT 1,
                    showInExploreMore TINYINT DEFAULT 0,
                    showInCombinedResults TINYINT DEFAULT 0
                ) ENGINE INNODB',
                'CREATE TABLE summon_search_options (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                    name VARCHAR(50) NOT NULL,
                    settingId INT(11) NOT NULL
                ) ENGINE INNODB',
                'ALTER TABLE library ADD COLUMN summonSearchSettingId INT(11) DEFAULT -1',
                'ALTER TABLE location ADD COLUMN summinSearchSettingId INT(11) DEFAULT -2',
             ],
         ],
     ];
}