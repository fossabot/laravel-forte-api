<?php

include __DIR__.'/vendor/autoload.php';

use Discord\DiscordCommandClient;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

const API_VERSION = 'v1';
define('PATH', (getenv('APP_ENV') === 'local' ? 'http://localhost:8000/api/'. API_VERSION : 'https://forte.team-crescendo.me/api/'. API_VERSION));

$discord = new DiscordCommandClient([
    'token' => getenv('DISCORD_BOT_TOKEN'),
    'description' => 'Command List',
    'discordOptions' => [
        'disabledEvents' => ['PRESENCE_UPDATE'],
    ],
]);

if (getenv('APP_ENV') === 'local') {
    $discord->on('ready', function ($discord) {
        echo 'Bot is ready!', PHP_EOL;

        // Listen for messages.
        $discord->on('message', function ($message, $discord) {
            echo $message->author->id . " " . $message->content;
            if (strpos($message->content, '라라') !== false) {
                if (strpos($message->content, '출석') || strpos($message->content, '출석체크') !== false) {
                    $id = $message->author->id; // discord id
                    $attendance = exec('curl -X POST "'. PATH .'/users/'.$id.'/attendances" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);
                    $attendance = json_decode($attendance);

                    if ($attendance->status === 'false') {
                        return $message->reply('이미 출석체크를 했습니다.');
                    } else if ($attendance->status === 'success') {
                        return $message->reply('```' . $attendance . '```');
                    }
                }
            }
        });
    });
}

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
    $userId = isset($params[0]) ? '/'.$params[0] : '';
    $users = exec('curl -X GET "'. PATH .'/users'.$userId.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

    $users = json_decode($users);
    $string = '';

    if ($userId != '') {
        $string .= $users->name.' (ID: '.$users->id.' | EMAIL: '.$users->email.')'.PHP_EOL;
    } else {
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
    $itemId = isset($params[0]) ? '/'.$params[0] : '';
    $items = exec('curl -X GET "'. PATH .'/items'.$itemId.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

    $items = json_decode($items);
    $string = '';

    if ($itemId != '') {
        $string .= $items->name.' (ID: '.$items->id.' | '.number_format($items->price).' 원)'.PHP_EOL;
    } else {
        foreach ($items as $index => $item) {
            $index++;
            $string .= $index.'. '.$item->name.' (ID: '.$item->id.' | '.number_format($item->price).' 원)'.PHP_EOL;
        }
    }

    return $discord->reply('```'.$string.'```');
}, [
    'description' => 'Forte Items',
]);

// discord id input convert user id
$forte->registerSubCommand('deposit', function ($discord, $params) {
    if (! $discord->author->roles[getenv('DISCORD_LARA_FORTE_DEPOSIT_AUTH_ROLE')]) {
        return $discord->reply('You have no authority.');
    }

    if (! $params[0] || ! $params[1]) {
        return $discord->reply('```Lara forte deposit <id> <point>```');
    }

    $id = $params[0];
    $point = $params[1];

    if (strlen($id) >= 18) {
        $user = exec('curl -X GET "'. PATH .'/discords/'.$id.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

        $user = json_decode($user);

        $id = $user->id;
    }

    $res = exec('curl -X POST "'. PATH .'/users/'.$id.'/points?points='.$point.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

    $res = json_decode($res);

    return $discord->reply('```'.$res->receipt_id.'```');
}, [
    'description' => 'Forte User Point Deposit',
]);

$discord->run();
