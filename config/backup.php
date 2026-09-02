<?php

return [
    'disk' => env('BACKUP_DISK', 'backups'),
    'storage_encrypted' => filter_var(env('BACKUP_STORAGE_ENCRYPTED', false), FILTER_VALIDATE_BOOL),
    'external_storage' => filter_var(env('BACKUP_EXTERNAL_STORAGE', false), FILTER_VALIDATE_BOOL),
    'production_restore_enabled' => filter_var(env('BACKUP_PRODUCTION_RESTORE_ENABLED', false), FILTER_VALIDATE_BOOL),
    'restore_max_uncompressed_bytes' => (int) env('BACKUP_RESTORE_MAX_UNCOMPRESSED_MB', 20480) * 1024 * 1024,
    'database_retention_days' => (int) env('BACKUP_DATABASE_RETENTION_DAYS', 14),
    'full_retention_days' => (int) env('BACKUP_FULL_RETENTION_DAYS', 56),
    'required_secret_references' => [
        'APP_KEY',
        'DB_PASSWORD',
        'LAMATECH_GODFATHER_PASSWORD',
        'AWS_ACCESS_KEY_ID',
        'AWS_SECRET_ACCESS_KEY',
    ],
    'excluded_paths' => ['.env', '.env.*', 'secrets/', '*.key', '*.pem', '*.p12', '*.pfx'],
];
