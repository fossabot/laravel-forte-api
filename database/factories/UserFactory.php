<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'discord_id' => $faker->randomNumber(),
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'points' => 0,
        'is_member' => 0
    ];
});
