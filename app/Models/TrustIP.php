<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TrustIP
 *
 * @property int $id
 * @property string $ip
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TrustIP newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TrustIP newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TrustIP query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TrustIP whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TrustIP whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TrustIP whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TrustIP whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TrustIP whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TrustIP extends Model
{
    protected $table = 'trust_ips';
}
