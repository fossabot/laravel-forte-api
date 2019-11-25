<?php

include __DIR__.'/vendor/autoload.php';

use Discord\DiscordCommandClient;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

const API_VERSION = 'v1';
define('PATH', (getenv('APP_ENV') === 'local' ? 'http://localhost:8000/api/'.API_VERSION : 'https://forte.team-crescendo.me/api/'.API_VERSION));

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
            echo $message->author->id.' '.$message->content;
            if (strpos($message->content, '라라') !== false || strpos($message->content, '라라야') || explode(' ', $message->content)[0] == 'ㄹ') {
                if (strpos($message->content, '출석') || strpos($message->content, '출석체크') !== false) {
                    $id = $message->author->id; // discord id
                    $isPremium = isset($message->author->roles[getenv('DISCORD_PREMIUM_ROLE')]) ? 1 : 0;
                    $exist = json_decode(exec('curl -X GET "'.PATH.'/discords/'.$id.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system));

                    if (count(get_object_vars($exist)) <= 0) {
                        return $message->reply(":warning: 팀 크레센도 FOTRE에 가입되어있지 않습니다.\n
출석체크 및 개근 보상으로 POINT를 지급받기 위해선 FORTE 가입이 필요합니다.\n
하단의 링크에서 Discord 계정 연동을 통해 가입해주세요.\n
> https://forte.team-crescendo.me/login/discord");
                    }

                    $attendance = exec('curl -X POST "'.PATH.'/discords/'.$id.'/attendances?isPremium='.$isPremium.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);
                    $attendance = json_decode($attendance);

                    if ($attendance->status === 'exist_attendance') {
                        return $message->reply("오늘은 이미 출석체크를 완료했습니다. \n `{$attendance->diff}` 후 다시 시도해주세요.");
                    } elseif ($attendance->status === 'success') {
                        $heart = '';
                        $day = 7 - $attendance->stack;

                        for ($i = 0; $i < $attendance->stack; $i++) {
                            $heart .= ':hearts: ';
                        }

                        for ($i = 0; $i < $day; $i++) {
                            $heart .= ':black_heart: ';
                        }

                        return $message->reply(":zap:  **출석 체크 완료!** \n
개근까지 앞으로 `{$day}일` 남았습니다. 내일 또 만나요! \n
{$heart} \n 

__7일 연속으로__ 출석하면 FORTE STORE(포르테 스토어)에서 사용할 수 있는 개근 보상으로 :point~1: POINT를 지급해드립니다. \n 
※ 개근 보상을 받을 때 `💎Premium` 역할을 보유하고 있다면 POINT가 추가로 지급됩니다! (자세한 사항은 #:book:premium_역할안내 를 확인해주세요.)");
                    } elseif ($attendance->status === 'regular') {
                        if ($isPremium > 0) {
                            return $message->reply(":gift_heart: **개근 성공!** \n
축하드립니다! 7일 연속 출석체크에 성공하여 개근 보상을 지급해드렸습니다. \n
> `10`:point~1: \n
> 프리미엄 추가 보상 `10`:point~1:");
                        } else {
                            return $message->reply(":gift_heart: **개근 성공!** \n
축하드립니다! 7일 연속 출석체크에 성공하여 개근 보상을 지급해드렸습니다.\n
> `10`:point~1:");
                        }
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
    $users = exec('curl -X GET "'.PATH.'/users'.$userId.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

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
    $items = exec('curl -X GET "'.PATH.'/items'.$itemId.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

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
        $user = exec('curl -X GET "'.PATH.'/discords/'.$id.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

        $user = json_decode($user);

        $id = $user->id;
    }

    $res = exec('curl -X POST "'.PATH.'/users/'.$id.'/points?points='.$point.'" -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

    $res = json_decode($res);

    return $discord->reply('```'.$res->receipt_id.'```');
}, [
    'description' => 'Forte User Point Deposit',
]);

$discord->run();
