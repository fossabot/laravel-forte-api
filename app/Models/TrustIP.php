<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\TrustIP
 *
 * @property int $id
 * @property string $ip
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|TrustIP newModelQuery()
 * @method static Builder|TrustIP newQuery()
 * @method static Builder|TrustIP query()
 * @method static Builder|TrustIP whereCreatedAt($value)
 * @method static Builder|TrustIP whereDescription($value)
 * @method static Builder|TrustIP whereId($value)
 * @method static Builder|TrustIP whereIp($value)
 * @method static Builder|TrustIP whereUpdatedAt($value)
 * @mixin Eloquent
 */
class TrustIP extends Model
{
    protected $table = 'trust_ips';
}
