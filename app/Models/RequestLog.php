<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RequestLog
 *
 * @property int $id
 * @property float $duration microtime start - end
 * @property string $url
 * @property string $method
 * @property string $ip
 * @property string $request
 * @property string $response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog clearRequestLogs()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog whereRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RequestLog whereUrl($value)
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
