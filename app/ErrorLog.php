<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $fillable = [
        'envrionment', 'title', 'message', 'parameters'
    ];
}
