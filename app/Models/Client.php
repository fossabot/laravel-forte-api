<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Client
 *
 * @property int $id
 * @property string $name skilebot and xsolla
 * @property string|null $xsolla_selected_group_name xsolla selected_group name
 * @property string $token authentication token
 * @property string $prev_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client wherePrevToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Client whereXsollaSelectedGroupName($value)
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
