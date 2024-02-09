<?php
/** @noinspection PhpUnused */
function getUpdates24_03_00(): array {
    return [

        'add_summon_to_library_field_level_permissions' => [
            'title' => 'Add Library Field Level Permissions for Summon',
            'description' => 'Add permissions to control access to Summon',
            'continueOnError' => true,
            'sql' => [
                "INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Primary Configuration - Library Fields', 'Library Summon Options', '', 55, 'Configure Library fields related to Summon content.')",
                "INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name  = 'opacAdmin'), (SELECT id from permissions where name='Library Summon Options'))",
                "INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name  = 'libraryAdmin'), (SELECT id from permissions where name='Library Summon Options'))",
            ],
        ],
        //add_library_field_level_permissions_for_summon
    ];
}