<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    const BOT_CLIENT = ['skilebot', 'baechubotv2'];
    const BOT_TOKEN_RENEWAL_EXCEPTION = ['lara'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'token', 'prev_token'
    ];

    /**
     * @param string $token
     * @return mixed
     */
    static public function bringNameByToken(string $token) {
        return self::where('token', $token)->first();
    }
}
