<?php

include __DIR__.'/vendor/autoload.php';

use Discord\DiscordCommandClient;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

const API_VERSION = 'v1';
define('PATH', (getenv('APP_ENV') === 'local' ? 'http://localhost:8000/api/'.API_VERSION : 'https://localhost/api/'.API_VERSION));

$discord = new DiscordCommandClient([
    'token' => getenv('DISCORD_BOT_TOKEN'),
    'description' => 'Command List',
    'discordOptions' => [
        'disabledEvents' => ['PRESENCE_UPDATE'],
    ],
]);

if (getenv('APP_ENV') === 'local' || getenv('APP_ENV') === 'production') {
    $discord->on('ready', function ($discord) {
        echo 'Bot is ready!', PHP_EOL;
        // Listen for messages.
        $discord->on('message', function ($message, $discord) {
            echo $message->author->id.' '.$message->content;
            $command = explode(' ', $message->content);
            if ($command[0] == '라라' || $command[0] == '라라야' || $command[0] == 'ㄹ') {
                if ($message->channel->guild_id != '348393122503458826' && $message->channel->guild_id != '399121287504723970') {
                    return $message->reply(':warning: 팀 크레센도 디스코드에 서만 사용가능합니다.');
                }
                if ($command[1] == '출석체크' || $command[1] == '출첵' || $command[1] == 'ㅊ') {
                    $id = $message->author->id; // discord id
                    $isPremium = isset($message->author->roles[getenv('DISCORD_PREMIUM_ROLE')]) ? 1 : 0;
                    $exist = json_decode(exec('curl -X GET "'.PATH.'/discords/'.$id.'" -H "accept: application/json" -k -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system));
                    if (count(get_object_vars($exist)) <= 0) {
                        return $message->reply(":warning: 팀 크레센도 FOTRE에 가입되어있지 않습니다.\n
출석체크 및 개근 보상으로 POINT를 지급받기 위해선 FORTE 가입이 필요합니다.\n
하단의 링크에서 Discord 계정 연동을 통해 가입해주세요.\n
> https://forte.team-crescendo.me/login/discord");
                    }

                    $attendance = exec('curl -X POST "'.PATH.'/discords/'.$id.'/attendances?isPremium='.$isPremium.'" -k -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);
                    $attendance = json_decode($attendance);
                    if ($attendance->error) {
                        return $message->reply(':fire: 에러 발생. 잠시 후 다시 시도해주세요.');
                    }
                    if ($attendance->status === 'exist_attendance') {
                        return $message->reply("최근에 이미 출석체크를 완료했습니다. \n `{$attendance->diff}` 후 다시 시도해주세요.");
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
__7일 누적으로__ 출석하면 출석 보상으로 FORTE STORE(포르테 스토어)에서 사용할 수 있는 POINT를 지급해드립니다. \n
※ 개근 보상을 받을 때 `💎Premium` 역할을 보유하고 있다면 POINT가 추가로 지급됩니다! (자세한 사항은 <#585653003122507796> 를 확인해주세요.)");
                    } elseif ($attendance->status === 'regular') {
                        return $message->reply(":gift_heart: **출석 성공!** \n
축하드립니다! 7일 누적으로 출석체크에 성공하여 개근 보상을 획득하였습니다. \n
> `{$attendance->point}` POINT ".($isPremium > 0 ? ' (`💎Premium` 보유 보너스 포함) ' : ''));
                    }
                } elseif ($command[1] == '출석랭킹') {
                    $ranks = exec('curl -X GET "'.PATH.'/discords/attendances/ranks" -H "accept: application/json" -k -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

                    $ranks = json_decode($ranks);
                    $string = '';

                    foreach ($ranks as $index => $rank) {
                        $index++;
                        $string .= $index.'. '.substr($rank->name, 0, 5)." \t(".preg_replace('/(?<=.{3})./u', '*', substr($rank->email, 0, 7)).") \t누적 출석: ".$rank->accrue_stack.PHP_EOL;
                    }

                    return $message->reply('```'.$string.'```');
                } elseif ($command[1] == '구독') {
                    if ($message->channel->id == '648509969687117825') {
                        $guild = $discord->guilds->get('name', '팀 크레센도 디스코드');
                        if (! $message->author->roles->get('name', '구독자')) {
                            $role = $guild->roles->get('name', '구독자');
                            $message->author->addRole($role);
                            $guild->members->save($message->author);

                            return $message->reply('구독되었습니다.');
                        } else {
                            $role = $guild->roles->get('name', '구독자');
                            $message->author->removeRole($role);
                            $guild->members->save($message->author);

                            return $message->reply('구독취소되었습니다.');
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
    $users = exec('curl -X GET "'.PATH.'/users'.$userId.'" -H "accept: application/json" -k -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

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
    $items = exec('curl -X GET "'.PATH.'/items'.$itemId.'" -H "accept: application/json" -k -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

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
        $user = exec('curl -X GET "'.PATH.'/discords/'.$id.'" -H "accept: application/json" -k -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

        $user = json_decode($user);

        $id = $user->id;
    }

    $res = exec('curl -X POST "'.PATH.'/users/'.$id.'/points?points='.$point.'" -k -H "accept: application/json" -H "Authorization: '.getenv('DISCORD_LARA_TOKEN').'" -H "X-CSRF-TOKEN: "', $system);

    $res = json_decode($res);

    return $discord->reply('```'.$res->receipt_id.'```');
}, [
    'description' => 'Forte User Point Deposit',
]);

$discord->run();
