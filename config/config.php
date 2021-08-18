<?php

declare(strict_types=1);

return [

    // Manage autoload migrations
    'autoload_migrations' => true,

    // Tenants Database Tables
    'tables' => [

        'tenants' => 'tenants',
        'tenantables' => 'tenantables',

    ],

    // Tenants Models
    'models' => [
        'tenant' => \Rinvex\Tenants\Models\Tenant::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Central Domains
    |--------------------------------------------------------------------------
    |
    | If your application is accessible through multiple domain names,
    | you may specify these domains here, so the application router
    | take care of matching the incoming requests appropriately.
    |
    | No need to list the default domain, it is automatically
    | appended to the compiled list from `app.url` config.
    |
    */

    'central_domains' => [
        'app.rinvex.test',
    ],

];
