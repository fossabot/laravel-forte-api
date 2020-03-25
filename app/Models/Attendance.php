<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'discord_id', 'stack', 'stacked_at',
    ];

    /**
     * @param int $id
     * @return
     */
    public static function scopeExistAttendance(int $id)
    {
        return self::where('discord_id', $id)->first();
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
