<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deploy token
    |--------------------------------------------------------------------------
    |
    | The production host has no CLI, cron or SSH — only FTP. To still run the
    | database migrations and warm the caches after uploading a new build, the
    | web endpoint GET /__deploy/{token} runs those tasks. It is only reachable
    | when this token is set (and matches). Leave it empty to disable the
    | endpoint entirely. Use a long, random value.
    |
    */

    'token' => env('DEPLOY_TOKEN', ''),

];
