<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\User;
use App\Models\UserItem;

class ReceiptService extends BaseService
{
    /**
     * @var User
     */
    protected $user;
    /**
     * @var Item
     */
    protected $item;
    /**
     * @var Receipt
     */
    protected $receipt;
    /**
     * @var UserItem
     */
    protected $userItem;
    /**
     * @var ItemService
     */
    protected $itemService;

    public function __construct(User $user,
                                Item $item,
                                Receipt $receipt,
                                UserItem $userItem,
                                ItemService $itemService)
    {
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
            $point = $user->{User::POINTS} - $item->{Item::PRICE};
        } else {
            $point = $user->{User::POINTS};
        }

        $receipt = $this->store($user, $client, $token, $userItemId, $point);

        $user->{User::POINTS} = $point;
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
            Receipt::USER_ID => $user->{User::ID},
            Receipt::CLIENT_ID => $token === Client::XSOLLA ? 1 : $client->{Client::ID},
            Receipt::USER_ITEM_ID => $userItemId,
            Receipt::ABOUT_CASH => 1,
            Receipt::REFUND => 0,
            Receipt::POINTS_OLD => $user->{User::POINTS},
            Receipt::POINTS_NEW => $point,
        ]);
    }
}
