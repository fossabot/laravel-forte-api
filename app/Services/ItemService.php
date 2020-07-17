<?php

namespace App\Services;

use App\Models\Item;
use App\Models\User;
use App\Models\UserItem;

class ItemService extends BaseService
{
    /**
     * @var User
     */
    protected User $user;
    /**
     * @var Item
     */
    protected Item $item;
    /**
     * @var UserItem
     */
    protected UserItem $userItem;
    /**
     * @var UserService
     */
    protected UserService $userService;

    public function __construct(
        User $user,
        Item $item,
        UserItem $userItem,
        UserService $userService
    ) {
        $this->user = $user;
        $this->item = $item;
        $this->userItem = $userItem;
        $this->userService = $userService;
    }

    /**
     * @param int $id
     * @return Item
     */
    public function show(int $id): Item
    {
        return $this->item->findOrFail($id);
    }
}
