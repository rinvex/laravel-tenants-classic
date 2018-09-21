<?php

declare(strict_types=1);

use Faker\Generator as Faker;

$factory->define(Rinvex\Tenants\Models\Tenant::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'slug' => $faker->slug,
        'owner_id' => $faker->randomNumber(),
        'owner_type' => $faker->randomElement(['App\Models\Member', 'App\Models\Manager', 'App\Models\Admin']),
        'email' => $faker->companyEmail,
        'language_code' => $faker->languageCode,
        'country_code' => $faker->countryCode,
    ];
});
