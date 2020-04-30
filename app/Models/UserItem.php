<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\UserItem.
 *
 * @property-read \App\Models\Item $item
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserItem countUserPurchaseDuplicateItem($itemId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserItem destroyUserItem($itemId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserItem purchaseUserItem($itemId, $token)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserItem updateUserItem($itemId, $data, $token)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserItem userItemDetail($itemId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserItem userItemLists()
 * @mixin \Eloquent
 */
class UserItem extends Model
{
    const SBK_5 = 'skb_5';
    const SKB_9 = 'skb_9';
    const SKB_12 = 'skb_12';

    const DISABLE_WITHDRAW_ITEMS = [
        self::SBK_5,
        self::SKB_9,
        self::SKB_12,
    ];

    const USER_ID = 'user_id';
    const ITEM_ID = 'item_id';
    const EXPIRED = 'expired';
    const CONSUMED = 'consumed';
    const SYNC = 'sync';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        self::USER_ID, self::ITEM_ID, self::EXPIRED, self::CONSUMED, self::SYNC,
    ];

    protected $dates = [
        self::DELETED_AT,
    ];

    /**
     * @brief 1:n relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function scopeUserItemLists(int $id)
    {
        return self::with('item')->where(self::USER_ID, $id)->orderBy('desc')->get();
    }

    /**
     * @param int $id
     * @param int $itemId
     * @return mixed
     */
    public static function scopeUserItemDetail(int $id, int $itemId)
    {
        return self::join('items', 'items.id', '=', 'user_items.item_id')->where('user_items.user_id', $id)
            ->where('user_items.id', $itemId)->first();
    }

    /**
     * @param int $id
     * @param int $itemId
     * @return mixed
     */
    public static function scopeCountUserPurchaseDuplicateItem(int $id, int $itemId)
    {
        return self::where(self::USER_ID, $id)->where(self::ITEM_ID, $itemId)->whereNull(self::DELETED_AT)->count();
    }

    /**
     * @param int $id
     * @param int $itemId
     * @param string $token
     * @return mixed
     * @throws \Exception
     */
    public static function scopePurchaseUserItem(int $id, int $itemId, string $token)
    {
        $user = User::scopeGetUser($id);
        $item = Item::scopeItemDetail($itemId);
        if ($user->{User::POINTS} < $item->{Item::PRICE}) {
            return response()->json([
                'message' => 'Insufficient points',
            ], 400);
        } elseif ($item->enabled == false) {
            return response()->json([
                'message' => 'Item is disable',
            ], 400);
        }

        if (self::scopeCountUserPurchaseDuplicateItem($id, $itemId) < Item::scopeItemDetail($itemId)->{ITEM::PURCHASE_LIMIT}) {
            return response()->json([
                'message' => 'over user purchase limit !',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $userItemId = self::insertGetId([
                self::USER_ID => $id,
                self::ITEM_ID => $itemId,
                self::EXPIRED => 0,
                self::CONSUMED => 0,
                self::SYNC => 0,
            ]);

            $createUserReceipt = self::createUserReceipt($id, $itemId, $userItemId, $token);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();

            return ['error' => $exception->getMessage()];
        }

        return response()->json([
            Receipt::USER_ITEM_ID => $userItemId,
            Receipt::RECEIPT_ID => $createUserReceipt,
        ], 201);
    }

    /**
     * @param int $id
     * @param int $itemId
     * @param int $userItemId
     * @param string $token
     * @return int
     */
    private static function createUserReceipt(int $id, int $itemId, int $userItemId, string $token)
    {
        $client = $token === 'xsolla' ?: Client::bringNameByToken($token);
        $user = User::scopeGetUser($id);
        $item = Item::scopeItemDetail($itemId);

        if ($token != 'xsolla') {
            $currentPoints = $user->{User::POINTS} - $item->{Item::PRICE};
        } else {
            $currentPoints = $user->{User::POINTS};
        }

        $receiptId = Receipt::insertGetId([
            Receipt::USER_ID => $id,
            Receipt::CLIENT_ID => $token == 'xsolla' ? 1 : $client->id,
            Receipt::USER_ITEM_ID => $userItemId,
            Receipt::ABOUT_CASH => 1,
            Receipt::REFUND => 0,
            Receipt::POINTS_OLD => $user->{User::POINTS},
            Receipt::POINTS_NEW => $currentPoints,
        ]);

        $user->{User::POINTS} = $currentPoints;
        $user->save();

        return $receiptId;
    }

    /**
     * @param int $id
     * @param int $itemId
     * @param array $data
     * @param string $token
     * @return array
     * @throws \Exception
     */
    public static function scopeUpdateUserItem(int $id, int $itemId, array $data, string $token)
    {
        $items = [
            User::NAME => User::scopeGetUser($id)->{User::NAME},
            User::EMAIL => User::scopeGetUser($id)->{User::EMAIL},
        ];

        $userItem = self::find($itemId)->where(self::USER_ID, $id);

        if (Item::scopeItemDetail($userItem->{self::ITEM_ID})->{ITEM::CONSUMABLE} === 0) {
            return response()->json([
                'message' => 'Bad Request Consumed value is true',
            ], 400);
        }

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                if ($key === self::SYNC) {
                    $userItem->$key = in_array(Client::bringNameByToken($token)->name, Client::BOT_CLIENT) ? 1 : 0;
                    continue;
                }
                $userItem->$key = $item;

                array_push($items, [
                    self::EXPIRED => $userItem->{self::EXPIRED} ? 'true' : 'false',
                    self::CONSUMED => $userItem->{self::CONSUMED} ? 'true' : 'false',
                    self::SYNC => $userItem->{self::SYNC} ? 'true' : 'false',
                ]);
            }
            $userItem->save();

            (new \App\Http\Controllers\DiscordNotificationController)->xsollaUserAction('User Item Update', $items);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();

            return ['error' => $exception->getMessage()];
        }

        return $userItem;
    }

    /**
     * @param int $id
     * @param int $itemId
     * @return array
     */
    public static function scopeDestroyUserItem(int $id, int $itemId)
    {
        self::where(self::USER_ID, $id)->where(self::ITEM_ID, $itemId)->update([
            self::DELETED_AT => date('Y-m-d H:m:s'),
        ]);

        return ['message' => 'Successful Destroy User Item'];
    }

    /**
     * @param int $itemId
     * @return array
     */
    public static function scopeUserItemWithdraw(int $itemId)
    {
        $xsollaAPI = \App::make('App\Services\XsollaAPIService');
        $repetition = false;
        $needPoint = 0;

        $user = User::scopeGetUser(\Auth::User()->id);

        self::find($itemId)->where(self::USER_ID, $user->id)->update([
            self::DELETED_AT => date('Y-m-d H:m:s'),
        ]);

        $item = self::scopeUserItemDetail($user->id, $itemId);

        $user->{User::POINTS} = $user->{User::POINTS} + $item->{Item::PRICE};
        $user->save();

        $datas = [];
        while (true) {
            $datas = [
                'amount' => $item->{ITEM::PRICE},
                'comment' => '포르테 아이템 청약철회',
                'project_id' => config('xsolla.projectKey'),
                'user_id' => $user->id,
            ];

            $response = json_decode($xsollaAPI->requestAPI('POST', 'projects/:projectId/users/'.$user->id.'/recharge', $datas), true);

            if ($user->points !== $response['amount']) {
                $repetition = true;
                $needPoint = $user->{User::POINTS} - $response['amount'];
                continue;
            } else {
                break;
            }
        }

        unset($datas['project_id']);
        $datas['email'] = $user->{User::EMAIL};
        array_push($datas, $item);
        (new \App\Http\Controllers\DiscordNotificationController)->xsollaUserAction('User Item Withdraw', $datas);

        return ['message' => 'Successful Withdraw User Item'];
    }
}
