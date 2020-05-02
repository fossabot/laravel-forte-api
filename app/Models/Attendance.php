<?php

namespace App\Models;

use Eloquent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Attendance.
 *
 * @property int $id
 * @property string $discord_id
 * @property int $stack
 * @property mixed $stacked_at
 * @property string|null $created_at
 * @method static Builder|Attendance attendanceRanks()
 * @method static Builder|Attendance attendances()
 * @method static Builder|Attendance existAttendance()
 * @method static Builder|Attendance newModelQuery()
 * @method static Builder|Attendance newQuery()
 * @method static Builder|Attendance query()
 * @method static Builder|Attendance whereCreatedAt($value)
 * @method static Builder|Attendance whereDiscordId($value)
 * @method static Builder|Attendance whereId($value)
 * @method static Builder|Attendance whereStack($value)
 * @method static Builder|Attendance whereStackedAt($value)
 * @mixin Eloquent
 */
class Attendance extends Model
{
    public $timestamps = false;

    const DISCORD_ID = 'discord_id';
    const STACK = 'stack';
    const STACKED_AT = 'stacked_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        self::DISCORD_ID, self::STACK, self::STACKED_AT,
    ];

    /**
     * @param int $id
     * @return Attendance|Builder|Model|object
     */
    public static function scopeExistAttendance(int $id): self
    {
        return self::where(self::DISCORD_ID, $id)->first();
    }

    /**
     * @return LengthAwarePaginator|Collection
     */
    public static function scopeAttendances()
    {
        return self::paginate();
    }
}
