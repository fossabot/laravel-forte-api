<?php

namespace App\Services;

use App\Models\User;
use App\Models\Item;
use App\Models\UserItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ItemService extends BaseService {
    /**
     * @var User
     */
    protected $user;
    /**
     * @var Item
     */
    protected $item;
    /**
     * @var UserItem
     */
    protected $userItem;
    /**
     * @var UserService
     */
    protected $userService;


    public function __construct(User $user,
                                Item $item,
                                UserItem $userItem,
                                UserService $userService)
    {
        $this->user = $user;
        $this->item = $item;
        $this->userItem = $userItem;
        $this->userService = $userService;
    }

    /**
     * @param int $id
     * @return Item|Item[]|Collection|Model|null
     */
    public function show(int $id): Item
    {
        return $this->item->findOrFail($id);
    }
}
