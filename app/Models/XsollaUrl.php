<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\XsollaUrl
 *
 * @property int $id
 * @property string $token
 * @property string $redirect_url
 * @property int $hit
 * @property int $expired
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|XsollaUrl newModelQuery()
 * @method static Builder|XsollaUrl newQuery()
 * @method static Builder|XsollaUrl query()
 * @method static Builder|XsollaUrl whereCreatedAt($value)
 * @method static Builder|XsollaUrl whereExpired($value)
 * @method static Builder|XsollaUrl whereHit($value)
 * @method static Builder|XsollaUrl whereId($value)
 * @method static Builder|XsollaUrl whereRedirectUrl($value)
 * @method static Builder|XsollaUrl whereToken($value)
 * @method static Builder|XsollaUrl whereUpdatedAt($value)
 * @method static Builder|XsollaUrl whereUserId($value)
 * @mixin Eloquent
 */
class XsollaUrl extends Model
{
    const TOKEN = 'token';
    const REDIRECT_URL = 'redirect_url';
    const USER_ID = 'user_id';

    protected $fillable = [
        self::TOKEN, self::REDIRECT_URL, self::USER_ID,
    ];
}
