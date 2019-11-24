<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'discord_id',
    ];

    /**
     * @param int $id
     * @return
     */
    public static function scopeTodayAttendance(int $id)
    {
        return self::where('discord_id', $id)->whereDate('created_at', date('Y-m-d'))->first();
    }

    /**
     * @param int $id
     */
    public static function scope7daysAttendance(int $id) {

    }
}
