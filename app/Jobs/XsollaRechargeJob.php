<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PointService;

class XsollaRechargeJob extends Job
{
    private User $user;
    private int $point;
    private string $comment;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param int $point
     * @param string $comment
     */
    public function __construct(User $user, int $point, string $comment)
    {
        $this->user = $user;
        $this->point = $point;
        $this->comment = $comment;
    }

    /**
     * Execute the job.
     *
     * @param PointService $pointService
     * @return void
     */
    public function handle(PointService $pointService)
    {
        $pointService->recharge($this->user, $this->point, $this->comment);
    }
}
