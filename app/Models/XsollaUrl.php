<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\XsollaUrl
 *
 * @property int $id
 * @property string $token
 * @property string $redirect_url
 * @property int $hit
 * @property int $expired
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl whereExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl whereHit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl whereRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl whereUserId($value)
 * @mixin \Eloquent
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
