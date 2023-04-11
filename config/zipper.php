<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Save To Disk
    |--------------------------------------------------------------------------
    |
    | Set this to 'true' to save the created zips to disk.
    | The saved file will be used the next time a user requests a zip with the same payload.
    |
    */

    'save' => false,

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Choose the disk you want to use when saving a zip.
    |
    */

    'disk' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Link Expiry
    |--------------------------------------------------------------------------
    |
    | Set the time in minutes after which a link should expire.
    |
    */

    'expiry' => null,

    /*
    |--------------------------------------------------------------------------
    | Cleanup Scope
    |--------------------------------------------------------------------------
    |
    | The scope to use when cleaning up your zip references with the scheduled command.
    |
    | Options:
    | "expired": Only delete expired reference files
    | "all": Delete all reference files excluding unexpired files
    | "force": Delete all reference files including unexpired files
    |
    */

    'cleanup' => 'expired',

];
