<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * App\Models\RequestLog.
 *
 * @property int $id
 * @property float $duration microtime start - end
 * @property string $url
 * @property string $method
 * @property string $ip
 * @property string $request
 * @property string $response
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|RequestLog clearRequestLogs()
 * @method static Builder|RequestLog newModelQuery()
 * @method static Builder|RequestLog newQuery()
 * @method static Builder|RequestLog query()
 * @method static Builder|RequestLog whereCreatedAt($value)
 * @method static Builder|RequestLog whereDuration($value)
 * @method static Builder|RequestLog whereId($value)
 * @method static Builder|RequestLog whereIp($value)
 * @method static Builder|RequestLog whereMethod($value)
 * @method static Builder|RequestLog whereRequest($value)
 * @method static Builder|RequestLog whereResponse($value)
 * @method static Builder|RequestLog whereUpdatedAt($value)
 * @method static Builder|RequestLog whereUrl($value)
 * @mixin Eloquent
 */
class RequestLog extends Model
{
    use SoftDeletes;

    const ID = 'id';
    const DURATION = 'duration';
    const URL = 'url';
    const METHOD = 'method';
    const IP = 'ip';
    const REQUEST = 'request';
    const RESPONSE = 'response';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        self::DURATION, self::URL, self::METHOD, self::IP, self::REQUEST, self::RESPONSE,
    ];

    /**
     * @param string $date
     * @return RequestLog[]|Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|Collection
     */
    public static function scopeClearRequestLogs(string $date)
    {
        return self::withTrashed()->where(self::CREATED_AT, '<', $date)->get();
    }
}
