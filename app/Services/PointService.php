<?php

namespace App\Services;

use App\Models\User;

class PointService
{
    /**
     * @var XsollaAPIService
     */
    protected XsollaAPIService $xsollaAPIService;

    /**
     * UserController constructor.
     * @param XsollaAPIService $xsollaAPIService
     */
    public function __construct(XsollaAPIService $xsollaAPIService)
    {
        $this->xsollaAPIService = $xsollaAPIService;
    }

    /**
     * @param User $user
     * @param int $point
     * @param string $comment
     */
    public function recharge(User $user, int $point, string $comment): void
    {
        $needPoint = 0;
        $repetition = false;

        while (true) {
            $datas = [
                'amount' => $repetition ? $needPoint : $point,
                'comment' => $comment,
                'project_id' => config('xsolla.project_key'),
                'user_id' => $user->id,
            ];

            $response = json_decode(
                $this->xsollaAPIService->request('POST', 'projects/:projectId/users/'.$user->id.'/recharge', $datas),
            true);

            if ($user->points !== $response['amount']) {
                $repetition = true;
                $needPoint = $user->points - $response['amount'];
                continue;
            } else {
                break;
            }
        }
    }
}
