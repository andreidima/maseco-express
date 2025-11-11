<?php

use Database\Seeders\RolesTableSeeder;

return [
    'seeders' => [
        RolesTableSeeder::class => [
            'name' => 'Roles & Permissions baseline',
            'description' => 'Ensures the platform has the expected roles, permissions, and pivot assignments synchronised for new and existing users.',
            'impact' => 'Security & access control',
            'estimated_runtime' => 'Under 1 second',
            'recommended' => 'Run after deploying permission changes or when onboarding new role definitions.',
            'tables' => [
                'roles' => 'Upserts the canonical set of application roles.',
                'permissions' => 'Seeds permissions required by each role (without removing custom additions).',
                'role_has_permissions' => 'Synchronises the mapping between roles and permissions.',
                'model_has_roles' => 'Re-attaches default roles to the Super Admin to guarantee access.',
            ],
            'operations' => [
                'Creates any missing roles defined by the platform specification.',
                'Updates the human-readable labels for existing roles to keep them in sync.',
                'Links each role to the curated permission set expected by the product team.',
                'Confirms that at least one Super Admin retains the Super Admin role.',
            ],
            'safety' => 'Idempotent: existing records are updated in-place without truncating the tables, so rerunning keeps data in sync.',
            'notes' => [
                'Custom roles or permissions added manually remain untouched.',
                'Users with bespoke role assignments are not altered unless they rely solely on the default roles.',
                'Database transactions ensure partial failures are rolled back before changes persist.',
            ],
        ],
    ],
];
