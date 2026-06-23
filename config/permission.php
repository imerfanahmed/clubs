<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return [
    'models' => [
        'permission' => Permission::class,
        'role' => Role::class,
    ],
    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],
    'cache_expiration_time' => 3600,
    'display_permission_in_exception' => false,
    'display_role_in_exception' => false,
    'enable_wildcard_permission' => false,
    'register' => [],
    'teams' => false,
    'team_foreign_key' => 'team_id',
];
