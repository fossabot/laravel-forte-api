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
    const DURATION = 'duration';
    const URL = 'url';
    const METHOD = 'method';
    const IP = 'ip';
    const REQUEST = 'request';
    const RESPONSE = 'response';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        self::DURATION, self::URL, self::METHOD, self::IP, self::REQUEST, self::RESPONSE
    ];

    /**
     * @param string $date
     * @return mixed
     */
    public static function scopeClearRequestLogs(string $date)
    {
        return self::where(self::CREATED_AT, '<', $date)->delete();
    }
}
