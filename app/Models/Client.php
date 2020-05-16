<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Client
 *
 * @property int $id
 * @property string $name skilebot and xsolla
 * @property string|null $xsolla_selected_group_name xsolla selected_group name
 * @property string $token authentication token
 * @property string $prev_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Client newModelQuery()
 * @method static Builder|Client newQuery()
 * @method static Builder|Client query()
 * @method static Builder|Client whereCreatedAt($value)
 * @method static Builder|Client whereId($value)
 * @method static Builder|Client whereName($value)
 * @method static Builder|Client wherePrevToken($value)
 * @method static Builder|Client whereToken($value)
 * @method static Builder|Client whereUpdatedAt($value)
 * @method static Builder|Client whereXsollaSelectedGroupName($value)
 * @mixin Eloquent
 * @property-read Collection|UserItem[] $items
 * @property-read int|null $items_count
 */
class Client extends Model
{
    const ID = 'id';
    const NAME = 'name';
    const XSOLLA_SELECTED_GROUP_NAME = 'xsolla_selected_group_name';
    const TOKEN = 'token';
    const PREV_TOKEN = 'token';

    const SKILEBOT = 'skilebot';
    const BAECHUBOT = 'baechubotv2';

    const XSOLLA = 'xsolla';

    const BOT_CLIENT = [
        self::SKILEBOT,
        self::BAECHUBOT,
    ];

    const BOT_TOKEN_RENEWAL_EXCEPTION = ['lara'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        self::NAME, self::XSOLLA_SELECTED_GROUP_NAME, self::TOKEN, self::PREV_TOKEN,
    ];

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(UserItem::class);
    }

    /**
     * @param string $token
     * @return Client|Builder|Model|object
     */
    public static function bringNameByToken(string $token): self
    {
        return self::where(self::TOKEN, $token)->first();
    }
}
