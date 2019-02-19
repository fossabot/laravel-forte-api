<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * @param int $id
     * @return mixed
     */
    static public function scopeUserItemLists(int $id) {
        return self::selectRaw('items.*, user_items.*, user_items.item_id as user_item_id')->join('items', 'items.id', '=', 'user_items.item_id')->where('user_items.user_id', $id)->get();
    }

    /**
     * @param int $id
     * @param int $itemId
     * @return mixed
     */
    static public function scopeUserItemDetail(int $id, int $itemId) {
        return self::join('items', 'items.id', '=', 'user_items.item_id')->where('user_items.user_id', $id)
            ->where('user_items.id', $itemId)->first();
    }

    /**
     * @param int $id
     * @param int $itemId
     * @return mixed
     */
    static public function scopeCountUserPurchaseDuplicateItem(int $id, int $itemId) {
        return self::where('user_id', $id)->where('item_id', $itemId)->whereNull('deleted_at')->count();
    }

    /**
     * @param int $id
     * @param int $itemId
     * @param string $token
     * @return mixed
     */
    static public function scopePurchaseUserItem(int $id, int $itemId, string $token) {
        $user = User::scopeGetUser($id);
        $item = Item::scopeItemDetail($itemId);
        if ($user->points < $item->price) {
            return response()->json([
                'message' => 'Insufficient points'
            ], 400);
        } elseif ($item->enabled == false) {
            return response()->json([
                'message' => 'Item is disable'
            ], 400);
        }

        if (self::scopeCountUserPurchaseDuplicateItem($id, $itemId) < Item::scopeItemDetail($itemId)->purchase_limit) {
            return response()->json([
                'message' => 'over user purchase limit !'
            ], 400);
        }

        try {
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
        } catch (\Exception $e) {
            DB::rollback();
            return ['error' => $e->getMessage()];
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
    static private function createUserReceipt(int $id, int $itemId, int $userItemId, string $token) {
        $client = Client::bringNameByToken($token);
        $user = User::scopeGetUser($id);
        $item = Item::scopeItemDetail($itemId);
        $currentPoints = $user->points - $item->price;

        $receiptId = Receipt::insertGetId([
            'user_id' => $id,
            'client_id' => $client->id,
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
     * @param array $datas
     * @return array
     */
    static public function scopeUpdateUserItem(int $id, int $itemId, array $datas = [], string $token) {
        if (Item::scopeItemDetail($itemId)->consumable == 0 && $datas['consumed']) {
            return response()->json([
                'message' => 'Bad Request Consumed value is true'
            ], 400);
        }

        $userItem = self::where('user_id', $id)->find($itemId);
        try {
            DB::beginTransaction();

            foreach ($datas as $key => $data) {
                if ($key == 'sync') {
                    $userItem->$key = in_array(Client::bringNameByToken($token)->name, Client::BOT_CLIENT) ? 1 : 0;
                    continue;
                }
                $userItem->$key = $data;
            }
            $userItem->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return ['error' => $e->getMessage()];
        }

        return $userItem;
    }

    /**
     * @param int $id
     * @param int $itemId
     * @return array
     */
    static public function scopeDestroyUserItem(int $id, int $itemId) {
        self::where('user_id', $id)->where('item_id', $itemId)->update([
            'deleted_at' => date('Y-m-d H:m:s'),
        ]);

        return ['message' => 'Successful Destroy User Item'];
    }
}
