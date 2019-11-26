<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'discord_id', 'stack', 'accrue_stack', 'stacked_at',
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
        return self::leftJoin('users', 'users.discord_id', '=', 'attendances.discord_id')->orderBy('accrue_stack', 'desc')->take(10)->get();
    }
}
