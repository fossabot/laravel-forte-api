<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\User;
use App\Models\UserItem;

class ReceiptService extends BaseService {
    /**
     * @var User
     */
    protected User $user;
    /**
     * @var Item
     */
    protected Item $item;
    /**
     * @var Receipt
     */
    protected Receipt $receipt;
    /**
     * @var UserItem
     */
    protected UserItem $userItem;
    /**
     * @var ItemService
     */
    protected ItemService $itemService;


    public function __construct
    (
        User $user,
        Item $item,
        Receipt $receipt,
        UserItem $userItem,
        ItemService $itemService
    ) {
        $this->user = $user;
        $this->item = $item;
        $this->receipt = $receipt;
        $this->userItem = $userItem;
        $this->itemService = $itemService;
    }

    public function save(User $user, int $itemId, int $userItemId, string $token): Receipt
    {
        $client = $token === Client::XSOLLA ?: Client::bringNameByToken($token);
        $item = $this->itemService->show($itemId);

        if ($token !== Client::XSOLLA) {
            $point = $user->points - $item->price;
        } else {
            $point = $user->points;
        }

        $receipt = $this->store($user, $client, $token, $userItemId, $point);

        $user->points = $point;
        $user->save();

        return $receipt;
    }

    /**
     * @param User $user
     * @param Client $client
     * @param string $token
     * @param int $userItemId
     * @param int $point
     * @return Receipt
     */
    public function store(User $user, Client $client, string $token, int $userItemId, int $point): Receipt
    {
        return $this->receipt->create([
            Receipt::USER_ID => $user->id,
            Receipt::CLIENT_ID => $token === Client::XSOLLA ? 1 : $client->id,
            Receipt::USER_ITEM_ID => $userItemId,
            Receipt::ABOUT_CASH => 1,
            Receipt::REFUND => 0,
            Receipt::POINTS_OLD => $user->points,
            Receipt::POINTS_NEW => $point,
        ]);
    }
}
