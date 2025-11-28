<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Master Data Sync Toggle
    |--------------------------------------------------------------------------
    |
    | Set UPLOAD_SYNC_MASTER=false in .env to skip syncing each upload result
    | into the master_data table. This lets long-running imports finish without
    | reprocessing the same dataset. When set to true (default), the sync runs.
    |
    */
    'sync_master_data' => env('UPLOAD_SYNC_MASTER', true),
];
