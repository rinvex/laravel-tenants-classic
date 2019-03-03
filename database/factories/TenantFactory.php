<?php

declare(strict_types=1);

use Faker\Generator as Faker;

$factory->define(Rinvex\Tenants\Models\Tenant::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'slug' => $faker->slug,
        'email' => $faker->companyEmail,
        'language_code' => $faker->languageCode,
        'country_code' => $faker->countryCode,
    ];
});
