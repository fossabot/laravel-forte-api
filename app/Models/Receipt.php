<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Receipt
 *
 * @property int $id
 * @property int $user_id
 * @property int $transaction_id xsolla payment duplicate observe transaction id
 * @property int $client_id where the payment/refund is completed (xsolla)
 * @property int|null $user_item_id
 * @property int $about_cash whether the payment/refund is relate to real cash (not points)
 * @property int $refund whether the process is refund (not payment)
 * @property int $points_old
 * @property int $points_new
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Receipt createReceipt($clientId, $userItemId, $aboutCash, $refund, $oldPoint, $newPoint, $transactionId)
 * @method static Builder|Receipt newModelQuery()
 * @method static Builder|Receipt newQuery()
 * @method static Builder|Receipt observerTransaction()
 * @method static Builder|Receipt query()
 * @method static Builder|Receipt userReceiptDetail($receiptId)
 * @method static Builder|Receipt userReceiptLists()
 * @method static Builder|Receipt whereAboutCash($value)
 * @method static Builder|Receipt whereClientId($value)
 * @method static Builder|Receipt whereCreatedAt($value)
 * @method static Builder|Receipt whereId($value)
 * @method static Builder|Receipt wherePointsNew($value)
 * @method static Builder|Receipt wherePointsOld($value)
 * @method static Builder|Receipt whereRefund($value)
 * @method static Builder|Receipt whereTransactionId($value)
 * @method static Builder|Receipt whereUpdatedAt($value)
 * @method static Builder|Receipt whereUserId($value)
 * @method static Builder|Receipt whereUserItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Receipt ofTransactionCount($id)
 * @mixin Eloquent
 * @property-read Client $client
 * @property-read UserItem $item
 * @property-read User $user
 */
class Receipt extends Model
{
    const ID = 'id';
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
     * @return belongsTo
     */
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return belongsTo
     */
    public function item(): belongsTo
    {
        return $this->belongsTo(UserItem::class);
    }

    /**
     * @return belongsTo
     */
    public function client(): belongsTo
    {
        return $this->belongsTo(Client::class);
    }

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
        self::TRANSACTION_ID,
    ];

    /**
     * @param int $userId
     * @param int $clientId
     * @param int $userItemId
     * @param int $aboutCash
     * @param int $refund
     * @param int $oldPoint
     * @param int $newPoint
     * @param int $transactionId
     * @return Receipt|Model
     */
    public static function store
    (
        int $userId,
        int $clientId,
        int $userItemId,
        int $aboutCash,
        int $refund,
        int $oldPoint,
        int $newPoint,
        int $transactionId
    ): self {
        return self::create([
            self::USER_ID => $userId,
            self::CLIENT_ID => $clientId,
            self::USER_ITEM_ID => $userItemId,
            self::ABOUT_CASH => $aboutCash,
            self::REFUND => $refund,
            self::POINTS_OLD => $oldPoint,
            self::POINTS_NEW => $newPoint,
            self::TRANSACTION_ID => $transactionId,
        ]);
    }
}
