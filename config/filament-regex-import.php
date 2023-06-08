<?php

use App\Import\Mappers\RoleMapper;
use App\Import\Mappers\UserMapper;

return [
    'accepted_mimes' => [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'text/plain',
        'csv',
    ],
    'mappers' => [
        UserMapper::class,
        RoleMapper::class,
    ],
    'navigation_group' => 'Import',
];
