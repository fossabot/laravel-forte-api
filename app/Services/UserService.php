<?php

namespace App\Services;

use App\Http\Controllers\DiscordNotificationController;
use App\Models\User;
use App\Models\UserItem;
use DB;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserService extends BaseService
{
    /**
     * @var User
     */
    protected User $user;

    /**
     * @var XsollaAPIService
     */
    protected XsollaAPIService $xsollaAPI;

    /**
     * UserService constructor.
     * @param User $user
     * @param XsollaAPIService $xsollaAPIService
     */
    public function __construct(
        User $user,
        XsollaAPIService $xsollaAPIService
    ) {
        $this->user = $user;
        $this->xsollaAPI = $xsollaAPIService;
    }

    /**
     * @return Paginator
     */
    public function index(): Paginator
    {
        return $this->user->whereNull(User::DELETED_AT)->paginate();
    }

    /**
     * @param int $id
     * @return User
     */
    public function show(int $id): User
    {
        return $this->user->findOrFail($id);
    }

    /**
     * @param int $type
     * @return Collection
     */
    public function types(int $type): Collection
    {
        return $this->user->ofType($type)->whereNull(User::DELETED_AT)->get();
    }

    /**
     * @param int $id
     * @param array $userData
     * @return User|User[]|array|Collection|Model|null
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function update(int $id, array $userData = [])
    {
        $user = $this->user->find($id);
        DB::beginTransaction();
        try {
            foreach ($userData as $key => $data) {
                if ($this->user->where($key, $data)->first()) {
                    continue;
                }
                $user->$key = $data;
            }
            $user->save();

            $userData = [
                'enabled' => true,
                'user_name' => $user->name,
                'email' => $user->email,
            ];

            $this->xsollaAPI->request('PUT', 'projects/:projectId/users/'.$id, $userData);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            app(DiscordNotificationController::class)->exception($exception, $userData);
        }

        return $user;
    }

    /**
     * @param int $id
     * @return User
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function destroy(int $id): User
    {
        if ($this->user->withTrashed()->find($id)->trashed()) {
            return $this->user->find($id);
        }

        $this->xsollaAPI->request('PUT', 'projects/:projectId/users/'.$id, [
            'enabled' => false,
        ]);

        $user = $this->user->find($id);
        $user->delete();

        return $user;
    }

    /**
     * @param int $id
     * @return User|Builder
     */
    public function discord(int $id)
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
