<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    public $timestamps = false;

    const DISCORD_ID = 'discord_id';
    const STACK = 'stack';
    const STACKED_AT = 'stacked_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        self::DISCORD_ID, self::STACK, self::STACKED_AT
    ];

    /**
     * @param int $id
     * @return
     */
    public static function scopeExistAttendance(int $id)
    {
        return self::where(self::DISCORD_ID, $id)->first();
    }

    /**
     * @return mixed
     */
    public static function scopeAttendances()
    {
        return self::get();
    }

    /**
     * @return mixed
     */
    public static function scopeAttendanceRanks()
    {
        return self::leftJoin('users', 'users.discord_id', '=', 'attendances.discord_id')->orderBy('stacked_at', 'desc')->take(10)->get();
    }
}
