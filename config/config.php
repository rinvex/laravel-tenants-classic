<?php

declare(strict_types=1);

return [

    // Tenants Database Tables
    'tables' => [

        'tenants' => 'tenants',
        'tenantables' => 'tenantables',

    ],

    // Tenants Models
    'models' => [
        'tenant' => \Rinvex\Tenants\Models\Tenant::class,
    ],

];
