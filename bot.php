<?php

include __DIR__.'/vendor/autoload.php';

use Discord\DiscordCommandClient;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

$discord = new DiscordCommandClient([
    'token' => getenv('DISCORD_BOT_TOKEN'),
    'description' => 'Command List',
    'discordOptions' => [
        'disabledEvents' => ['PRESENCE_UPDATE'],
    ],
]);

$discord->registerCommand('uptime', function () {
    return exec('uptime', $system);
}, [
    'description' => 'Server Uptime',
]);

$discord->registerCommand('xsolla:sync', function () {
    shell_exec('php artisan xsolla:sync');

    return 'success';
}, [
    'description' => 'Sync Xsolla from Forte API',
]);

$forte = $discord->registerCommand('forte', function ($discord) {
    $commands = [
        'forte users',
        'forte users <id>',
        'forte users ban <id>',
        'forte users unban <id>',
        'forte items',
        'forte items <id>',
    ];

    $string = '';

    foreach ($commands as $command) {
        $string .= $command.PHP_EOL;
    }

    return $discord->reply('```'.$string.'```');
}, [
    'description' => 'Forte Command List',
]);

$forte->registerSubCommand('users', function ($discord, $params) {
    $userId = isset($params[0])?'/'.$params[0]:'';
    if (getenv('APP_ENV') === 'local') {
        $users = exec('curl -X GET "http://localhost:8000/api/v1/users'.$userId.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);
    } else {
        $users = exec('curl -X GET "https://forte.team-crescendo.me/api/v1/users'.$userId.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);
    }

    $users = json_decode($users);
    $string = '';

    if($userId != ''){
        $string .= $users->name.' (ID: '.$users->id.' | EMAIL: '.$users->email.')'.PHP_EOL;
    }else{
        foreach ($users as $index => $user) {
            $index++;
            $string .= $index.'. '.$user->name.' (ID: '.$user->id.' | EMAIL: '.$user->email.')'.PHP_EOL;
        }
    }
    return $discord->reply('```'.$string.'```');
}, [
    'description' => 'Forte Users',
]);

$forte->registerSubCommand('items', function ($discord, $params) {
    $itemId = isset($params[0])?'/'.$params[0]:'';
    if (getenv('APP_ENV') === 'local') {
        $items = exec('curl -X GET "http://localhost:8000/api/v1/items'.$itemId.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);
    } else {
        $items = exec('curl -X GET "https://forte.team-crescendo.me/api/v1/items'.$itemId.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);
    }

    $items = json_decode($items);
    $string = '';

    if($itemId != ''){
        $string .= $items->name.' (ID: '.$items->id.' | '.number_format($items->price).' 원)'.PHP_EOL;
    }else{
        foreach ($items as $index => $item) {
            $index++;
            $string .= $index.'. '.$item->name.' (ID: '.$item->id.' | '.number_format($item->price).' 원)'.PHP_EOL;
        }
    }


    return $discord->reply('```'.$string.'```');
}, [
    'description' => 'Forte Items',
]);

$discord->run();
