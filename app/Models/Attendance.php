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

    public static function scopeAttendanceRanks()
    {
        return self::leftJoin('users', 'users.discord_id', '=', 'attendances.discord_id')->orderBy('stack', 'desc')->take(10)->get();
    }
}
