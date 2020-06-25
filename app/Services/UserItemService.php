<?php

namespace App\Services;

use App\Http\Controllers\DiscordNotificationController;
use App\Models\Client;
use App\Services\UserService;
use App\Services\ItemService;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\User;
use App\Models\UserItem;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserItemService extends BaseService {
    /**
     * @var User
     */
    protected $user;
    /**
     * @var UserItem
     */
    protected $itemService;
    /**
     * @var UserItem
     */
    protected $userItem;
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserItemService constructor.
     * @param User $user
     * @param UserItem $userItem
     * @param ItemService $itemService
     * @param UserService $userService
     */
    public function __construct(User $user,
                                UserItem $userItem,
                                ItemService $itemService,
                                UserService $userService)
    {
        $this->user = $user;
        $this->userItem = $userItem;
        $this->userService = $userService;
        $this->itemService = $itemService;
    }

    /**
     * @param int $id
     * @param int $itemId
     * @return UserItem|Model|\Illuminate\Database\Query\Builder|object
     */
    public function show(int $id, int $itemId): UserItem
    {
        return $this->userItem
            ->with('items')
            ->ofUser($id)
            ->ofId($itemId)
            ->first();
    }

    /**
     * @param User $user
     * @param int $itemId
     * @param string $token
     * @return array
     * @throws Exception
     */
    public function save(User $user, int $itemId, string $token): array
    {
        $item = $this->itemService->show($itemId);

        if ($user->{User::POINTS} < $item->{Item::PRICE}) {
            return ['message' => 'Insufficient points'];
        } elseif ($item->enabled === false) {
            return ['message' => 'Item is disable'];
        }

        if ($this->userItem
                ->ofUser($user->{User::ID})
                ->ofItem($itemId)
                ->whereNull(UserItem::DELETED_AT)->count() < $item->{ITEM::PURCHASE_LIMIT}) {
            return ['message' => 'over user purchase limit !'];
        }

        try {
            DB::beginTransaction();

            $userItem = $this->store($user->{User::ID}, $itemId);
            $receipt = $this->receiptService->save($user, $itemId, $userItem->{UserItem::ID}, $token);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();

            return ['error' => $exception->getMessage()];
        }

        return [Receipt::USER_ITEM_ID => $userItem->{UserItem::ID}, Receipt::RECEIPT_ID => $receipt->{Receipt::ID}];
    }

    /**
     * @param int $id
     * @param int $itemId
     * @param array $data
     * @param string $token
     * @return UserItem|array|Builder
     * @throws Exception
     */
    public function update(int $id, int $itemId, array $data, string $token)
    {
        $userItem = $this->userItem->find($itemId)->ofUser($id);

        if ($this->itemService->show($userItem->{UserItem::ITEM_ID})->{ITEM::CONSUMABLE} === 0) {
            return ['message' => 'Bad Request Consumed value is true'];
        }

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                if ($key === UserItem::SYNC) {
                    $userItem->$key =
                        in_array(Client::bringNameByToken($token)->{Client::NAME},
                            Client::BOT_CLIENT) ? 1 : 0;
                    continue;
                }
                $userItem->$key = $item;
            }
            $userItem->save();

            (new DiscordNotificationController)->xsollaUserAction('User Item Update', (array) $userItem);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();

            return ['error' => $exception->getMessage()];
        }

        return $userItem;
    }

    /**
     * @param int $id
     * @param int $itemId
     * @return UserItem
     */
    public function destroy(int $id, int $itemId): UserItem
    {
        return $this->userItem::withTrashed()->ofUser($id)->ofItem($itemId)->first();
    }

    /**
     * @param int $id
     * @param int $itemId
     * @return UserItem
     */
    protected function store(int $id, int $itemId): UserItem
    {
        return $this->userItem->create([
            UserItem::USER_ID => $id,
            UserItem::ITEM_ID => $itemId,
            UserItem::EXPIRED => 0,
            UserItem::CONSUMED => 0,
            UserItem::SYNC => 0,
        ]);
    }

    // TODO: withdraw
}
