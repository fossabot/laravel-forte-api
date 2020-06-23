<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceV2 extends Model
{
    const ID = 'id';
    const DISCORD_ID = 'discord_id';
    const KEY_COUNT = 'key_count';
    const KEY_ACQUIRED_AT = 'KEY_ACQUIRED_AT';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = 'v2_attendances';

    protected $fillable = [
        self::ID, self::DISCORD_ID, self::KEY_COUNT, self::KEY_ACQUIRED_AT,
    ];
}
