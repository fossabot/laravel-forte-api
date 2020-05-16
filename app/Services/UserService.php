<?php

namespace App\Services;

use App\Http\Controllers\DiscordNotificationController;
use App\Models\User;
use App\Models\UserItem;
use Http\Client\Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Services\XsollaAPIService;
use Illuminate\Contracts\Pagination\Paginator;

class UserService extends BaseService {
    /**
     * @var User
     */
    protected $user;

    /**
     * @var XsollaAPIService
     */
    protected $xsollaAPI;

    /**
     * UserService constructor.
     * @param User $user
     * @param XsollaAPIService $xsollaAPIService
     */
    public function __construct(User $user,
                                XsollaAPIService $xsollaAPIService)
    {
        $this->user = $user;
        $this->xsollaAPI = $xsollaAPIService;
    }

    public function index(): Paginator
    {
        return $this->user->whereNull($this->user::DELETED_AT)->paginate();
    }

    /**
     * @param int $id
     * @return User|User[]|Collection|Model|null
     */
    public function show(int $id): User
    {
        return $this->user->find($id);
    }

    /**
     * @return Collection
     */
    public function staffs(): Collection
    {
        return $this->user->where($this->user::IS_MEMBER, 2)->whereNull($this->user::DELETED_AT)->get();
    }

    /**
     * @param int $id
     * @param array $datas
     * @return User|User[]|array|Collection|Model|null
     */
    public function update(int $id, array $datas = [])
    {
        $user = $this->user->find($id);
        try {
            DB::beginTransaction();

            foreach ($datas as $key => $data) {
                if ($this->user->where($key, $data)->first()) {
                    continue;
                }
                $user->$key = $data;
            }
            $user->save();

            $datas = [
                'enabled' => true,
                'user_name' => $user->{User::NAME},
                'email' => $user->{User::EMAIL},
            ];

            $this->xsollaAPI->requestAPI('PUT', 'projects/:projectId/users/'.$id, $datas);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            (new DiscordNotificationController)->exception($exception, $datas);

            return ['error' => $exception->getMessage()];
        }

        return $user;
    }

    /**
     * @param int $id
     * @return User
     * @throws \Exception
     */
    public function destroy(int $id): User
    {
        if ($this->user->withTrashed()->find($id)->trashed()) return $this->user->find($id);

        $this->xsollaAPI->requestAPI('PUT', 'projects/:projectId/users/'.$id, [
            'enabled' => false,
        ]);

        $user = $this->user->find($id);
        $user->delete();

        return $user;
    }

    /**
     * @param int $id
     * @return User|Builder|Model|object
     */
    public function discord(int $id): User
    {
        return $this->user->where($this->user::DISCORD_ID, $id)->firstOrFail();
    }

    /**
     * @param array $user
     * @return User
     */
    public function save(array $user): User
    {
        return $this->user->create([
            User::EMAIL => $user->{User::EMAIL},
            User::NAME => $user->{User::NAME},
            User::DISCORD_ID => $user->{User::ID},
        ]);
    }

    /**
     * @param int $id
     * @return UserItem[]|Collection
     */
    public function items(int $id): Collection
    {
        return $this->user->find($id)->items;
    }
}
