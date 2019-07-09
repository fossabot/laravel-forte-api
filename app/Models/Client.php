<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public const BOT_CLIENT = ['skilebot', 'baechubotv2'];
    public const BOT_TOKEN_RENEWAL_EXCEPTION = ['lara'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'token', 'prev_token',
    ];

    /**
     * @param string $token
     * @return mixed
     */
    public static function bringNameByToken(string $token)
    {
        return self::where('token', $token)->first();
    }
}
