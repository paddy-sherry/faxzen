<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Set the IP addresses of the proxies that are always trusted.
    |
    */

    'proxies' => '*', // Trust all proxies for now - adjust as needed

    /*
    |--------------------------------------------------------------------------
    | Trusted Headers
    |--------------------------------------------------------------------------
    |
    | These are the headers that your proxy uses to provide information
    | about the original request to the application.
    |
    */

    'headers' => Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                 Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                 Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                 Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,

];
