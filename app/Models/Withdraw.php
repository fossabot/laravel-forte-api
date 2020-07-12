<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Withdraw.
 *
 * @property int $id
 * @property int $item_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_item_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Withdraw newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Withdraw newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Withdraw query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Withdraw whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Withdraw whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Withdraw whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Withdraw whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Withdraw whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Withdraw whereUserItemId($value)
 * @mixin \Eloquent
 */
class Withdraw extends Model
{
    const ID = 'id';
    const USER_ID = 'user_id';
    const ITEM_ID = 'item_id';
    const USER_ITEM_ID = 'user_item_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        self::USER_ID, self::ITEM_ID, self::USER_ITEM_ID,
    ];
}
