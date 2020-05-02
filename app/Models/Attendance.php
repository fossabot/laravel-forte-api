<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Attendance
 *
 * @property int $id
 * @property string $discord_id
 * @property int $stack
 * @property mixed $stacked_at
 * @property string|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Attendance attendanceRanks()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Attendance attendances()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Attendance existAttendance()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Attendance whereDiscordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Attendance whereStack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Attendance whereStackedAt($value)
 * @mixin \Eloquent
 */
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
