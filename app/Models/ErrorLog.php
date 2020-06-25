<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\ErrorLog
 *
 * @property int $id
 * @property string $environment 환경
 * @property string $title
 * @property string $message
 * @property string $parameters
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ErrorLog newModelQuery()
 * @method static Builder|ErrorLog newQuery()
 * @method static Builder|ErrorLog query()
 * @method static Builder|ErrorLog whereCreatedAt($value)
 * @method static Builder|ErrorLog whereEnvironment($value)
 * @method static Builder|ErrorLog whereId($value)
 * @method static Builder|ErrorLog whereMessage($value)
 * @method static Builder|ErrorLog whereParameters($value)
 * @method static Builder|ErrorLog whereTitle($value)
 * @method static Builder|ErrorLog whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ErrorLog extends Model
{
    const ID = 'id';
    const ENVIRONMENT = 'environment';
    const TITLE = 'title';
    const MESSAGE = 'message';
    const PARAMETERS = 'parameters';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        self::ENVIRONMENT, self::TITLE, self::MESSAGE, self::PARAMETERS,
    ];
}
