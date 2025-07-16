<?php

return [
    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'local'),

    'layout' => [
        'sidebar' => [
            'is_collapsible_on_desktop' => true,
            'groups' => [
                'are_collapsible' => true,
            ],
        ],
    ],

    'widgets' => [
        'default_stats_widgets' => [
            'Filament\Widgets\StatsOverviewWidget',
        ],
    ],

    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 300, // 5 minutos
    ],
]; 