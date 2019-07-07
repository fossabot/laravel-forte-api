<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
