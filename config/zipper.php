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

];
