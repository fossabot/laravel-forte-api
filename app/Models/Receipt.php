<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Receipt.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Receipt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Receipt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Receipt observerTransaction()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Receipt query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Receipt userReceiptDetail($receiptId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Receipt userReceiptLists()
 * @mixin \Eloquent
 */
class Receipt extends Model
{
    const RECEIPT_ID = 'receipt_id';
    const USER_ID = 'user_id';
    const CLIENT_ID = 'client_id';
    const USER_ITEM_ID = 'user_item_id';
    const ABOUT_CASH = 'about_cash';
    const REFUND = 'refund';
    const TRANSACTION_ID = 'transaction_id';
    const POINTS_OLD = 'points_old';
    const POINTS_NEW = 'points_new';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        self::USER_ID,
        self::CLIENT_ID,
        self::USER_ITEM_ID,
        self::ABOUT_CASH,
        self::REFUND,
        self::POINTS_OLD,
        self::POINTS_NEW,
    ];

    /**
     * @param int $id
     * @return mixed
     */
    public static function scopeUserReceiptLists(int $id)
    {
        return self::where(self::USER_ID, $id)->get();
    }

    /**
     * @param int $id
     * @param int $receiptId
     * @return mixed
     */
    public static function scopeUserReceiptDetail(int $id, int $receiptId)
    {
        return self::find($receiptId)->where(self::USER_ID, $id)->get();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function scopeObserverTransaction(int $id)
    {
        return self::where(self::TRANSACTION_ID, $id)->count();
    }
}
