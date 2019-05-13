<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    //            $table->increments('id');
    //            $table->double('duration')->comment('microtime start - end');
    //            $table->string('url');
    //            $table->string('method');
    //            $table->ipAddress('ip');
    //            $table->text('request');
    //            $table->text('response');

    protected $fillable = [
        'duration', 'url', 'method', 'ip', 'request', 'response'
    ];
}
