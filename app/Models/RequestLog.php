<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RequestLog.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog clearRequestLogs()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog query()
 * @mixin \Eloquent
 */
class RequestLog extends Model
{
    protected $fillable = [
        'duration', 'url', 'method', 'ip', 'request', 'response',
    ];

    /**
     * @param string $date
     * @return mixed
     */
    public static function scopeClearRequestLogs(string $date)
    {
        return self::where('created_at', '<', $date)->delete();
    }
}
