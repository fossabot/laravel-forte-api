<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AttendanceV2.
 *
 * @property int $id
 * @property string $discord_id
 * @property int $key_count 키 획득 count
 * @property mixed $key_acquired_at 키 획득 일
 * @property mixed $box_unpacked_at 상자 개봉 일
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AttendanceV2 newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AttendanceV2 newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AttendanceV2 query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AttendanceV2 whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AttendanceV2 whereDiscordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AttendanceV2 whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AttendanceV2 whereKeyAcquiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AttendanceV2 whereBoxUnpackedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AttendanceV2 whereKeyCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AttendanceV2 whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AttendanceV2 extends Model
{
    const ID = 'id';
    const DISCORD_ID = 'discord_id';
    const KEY_COUNT = 'key_count';
    const KEY_ACQUIRED_AT = 'key_acquired_at';
    const BOX_UNPACKED_AT = 'box_unpacked_at';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = 'v2_attendances';

    protected $fillable = [
        self::ID, self::DISCORD_ID, self::KEY_COUNT, self::KEY_ACQUIRED_AT, self::BOX_UNPACKED_AT,
    ];

    protected $casts = [
        [self::KEY_ACQUIRED_AT => 'array'],
        [self::BOX_UNPACKED_AT => 'array'],
    ];
}
