<?php

return [
    'backup' => [
        /*
         * El nombre usado al crear el zip y notificar.
         * Lo tomamos de la variable global (o el .env) para que sea el nombre de tu gym.
         */
        'name' => config('app.name', 'Laravel'), // <-- CAMBIO AQUÍ

        'source' => [
            'files' => [
                'include' => [
                    storage_path('app/private/public'),
                ],
                'exclude' => [
                    storage_path('app/backups'),
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => null,
            ],
            'databases' => [
                'mysql',
            ],
        ],

        'database_dump' => [
            'dump_binary_path' => '',
            'use_single_transaction' => true,
            'timeout' => 60 * 5,
            'default_character_set' => env('DB_CHARSET', 'utf8mb4'),
            'add_extra_options' => ['--set-gtid-purged=OFF'],
        ],

        'destination' => [
            'filename_prefix' => config('app.name', 'laravel') . '-',
            'disks' => [
                'backups_local',
            ],
        ],

        // --- ¡ESTA ES LA SECCIÓN MÁS IMPORTANTE! ---
        // Le decimos al paquete que guarde los zips directamente
        // en la raíz del disco 'backups_local', no en subcarpetas.
        'destination_file_system' => [
            'disk_name' => 'backups_local',
            'path_generator' => \Spatie\Backup\BackupDestination\DefaultPathGenerator::class, // Generador por defecto
            'file_name_generator' => \Spatie\Backup\BackupDestination\DefaultFileNameGenerator::class, // Generador por defecto
        ],
        // --- FIN SECCIÓN IMPORTANTE ---


        'temporary_directory' => storage_path('app/backup-temp'),
    ],

    /*
     * Notificaciones (Usará el nombre del gym ahora)
     */
    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => ['mail'],
        ],
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
        'mail' => [
            'to' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', config('app.name')),
            ],
        ],
    ],

    /*
     * Limpieza
     */
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],
];