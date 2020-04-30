<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Client.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client query()
 * @mixin \Eloquent
 */
class Client extends Model
{
    const NAME = 'name';
    const XSOLLA_SELECTED_GROUP_NAME = 'xsolla_selected_group_name';
    const TOKEN = 'token';
    const PREV_TOKEN = 'token';

    const SKILEBOT = 'skilebot';
    const BAECHUBOT = 'baechubotv2';

    const BOT_CLIENT = [
        self::SKILEBOT,
        self::BAECHUBOT,
    ];

    const BOT_TOKEN_RENEWAL_EXCEPTION = ['lara'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        self::NAME, self::XSOLLA_SELECTED_GROUP_NAME, self::TOKEN, self::PREV_TOKEN
    ];

    /**
     * @param string $token
     * @return mixed
     */
    public static function bringNameByToken(string $token)
    {
        return self::where(self::TOKEN, $token)->first();
    }
}
