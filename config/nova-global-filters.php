<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Session prefix
    |--------------------------------------------------------------------------
    |
    | Filter values are stored in the session under
    | "{prefix}.{scope}.{filter key}".
    |
    */
    'session_prefix' => 'nova-global-filters',

    /*
    |--------------------------------------------------------------------------
    | Default scope
    |--------------------------------------------------------------------------
    |
    | Cards and metrics that do not declare a scope share this one. Give a
    | card and its metrics an explicit scope to isolate them from the rest.
    |
    */
    'default_scope' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware for the package's API routes. When null, Nova's own
    | "api_middleware" is used (authenticated user allowed to view Nova).
    |
    */
    'middleware' => null,

];
