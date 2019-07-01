<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class UserItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'item_id', 'expired', 'consumed', 'sync',
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
        return self::with('item')->where('user_id', $id)->get();
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
        return self::where('user_id', $id)->where('item_id', $itemId)->whereNull('deleted_at')->count();
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
        if ($user->points < $item->price) {
            return response()->json([
                'message' => 'Insufficient points',
            ], 400);
        } elseif ($item->enabled == false) {
            return response()->json([
                'message' => 'Item is disable',
            ], 400);
        }

        if (self::scopeCountUserPurchaseDuplicateItem($id, $itemId) < Item::scopeItemDetail($itemId)->purchase_limit) {
            return response()->json([
                'message' => 'over user purchase limit !',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $userItemId = self::insertGetId([
                'user_id' => $id,
                'item_id' => $itemId,
                'expired' => 0,
                'consumed' => 0,
                'sync' => 0,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ]);

            $createUserReceipt = self::createUserReceipt($id, $itemId, $userItemId, $token);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();

            return ['error' => $exception->getMessage()];
        }

        return response()->json([
            'user_item_id' => $userItemId,
            'receipt_id' => $createUserReceipt,
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
        $client = $token == 'xsolla' ?: Client::bringNameByToken($token);
        $user = User::scopeGetUser($id);
        $item = Item::scopeItemDetail($itemId);

        if ($token != 'xsolla') {
            $currentPoints = $user->points - $item->price;
        } else {
            $currentPoints = $user->points;
        }

        $receiptId = Receipt::insertGetId([
            'user_id' => $id,
            'client_id' => $token == 'xsolla' ? 1 : $client->id,
            'user_item_id' => $userItemId,
            'about_cash' => 1,
            'refund' => 0,
            'points_old' => $user->points,
            'points_new' => $currentPoints,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s'),
        ]);

        $user->points = $currentPoints;
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
        if (Item::scopeItemDetail($itemId)->consumable == 0 && $data['consumed']) {
            return response()->json([
                'message' => 'Bad Request Consumed value is true',
            ], 400);
        }

        $userItem = self::where('user_id', $id)->find($itemId);
        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                if ($key === 'sync') {
                    $userItem->$key = in_array(Client::bringNameByToken($token)->name, Client::BOT_CLIENT) ? 1 : 0;
                    continue;
                }
                $userItem->$key = $item;
            }
            $userItem->save();

            (new \App\Http\Controllers\DiscordNotificationController)->xsollaUserAction('User Item Update', $userItem);

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
        self::where('user_id', $id)->where('item_id', $itemId)->update([
            'deleted_at' => date('Y-m-d H:m:s'),
        ]);

        return ['message' => 'Successful Destroy User Item'];
    }
}
