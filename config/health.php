<?php

return [
    'storage_disk' => env('HEALTH_STORAGE_DISK', env('FILESYSTEM_DISK', 'local')),
    'scheduler_stale_after_minutes' => (int) env('HEALTH_SCHEDULER_STALE_AFTER_MINUTES', 5),
    'backup_stale_after_hours' => (int) env('HEALTH_BACKUP_STALE_AFTER_HOURS', 26),
];
