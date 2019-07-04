<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(App\RequestLog::class, function (Faker $faker) {
    return [
        'duration' => '0.003208160400390625',
        'url' => Str::random(10),
        'method' => 'post',
        'ip' => '68.183.18.15',
        'request' => '[]',
        'response' => '""',
    ];
});
