<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'token',
    ];

    static public function bringNameByToken(string $token) {
        return self::where('token', $token)->first();
    }
}
