<?php

namespace App\Http\Controllers;

use App\Models\AttendanceV2;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AttendanceController extends Controller
{
    private const KEY_MAX_COUNT = 10;
    /**
     * @var AttendanceService $attendanceSerivce
     */
    private AttendanceService $attendanceSerivce;

    /**
     * AttendanceController constructor.
     * @param AttendanceService $attendanceSerivce
     */
    public function __construct(AttendanceService $attendanceSerivce)
    {
        $this->attendanceSerivce = $attendanceSerivce;
    }

    /**
     * 팀 크레센도 디스코드 이용자가 출석체크를 합니다.
     *
     * @param string $id
     * @return JsonResponse
     *
     * @SWG\POST(
     *     path="/discords/{discordId}/attendances",
     *     description="User Attendance v2",
     *     produces={"application/json"},
     *     tags={"Discord"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="discordId",
     *         in="path",
     *         description="Discord Id",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="isPremium",
     *         in="query",
     *         description="User Premium Role Check",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful User Attendance v2"
     *     ),
     * )
     */
    public function store(string $id): JsonResponse
    {
        $date = Carbon::now()->toDateTimeString();
        $attendance = AttendanceV2::query()
            ->whereDiscordId($id)
            ->first();

        if ($attendance->isEmpty()) {
            $attendance = AttendanceV2::insert([
                AttendanceV2::DISCORD_ID => $id,
                AttendanceV2::KEY_ACQUIRED_AT => json_encode([$date]),
            ]);

            return new JsonResponse([
                'status' => 'success',
                AttendanceV2::KEY_COUNT => $attendance->{AttendanceV2::KEY_COUNT},
            ], Response::HTTP_CREATED);
        } else {
            $keyAcquiredAt = $attendance->key_acquired_at;

            if ($keyAcquiredAt->last() && Carbon::parse($keyAcquiredAt->last())->isToday()) {
                $timeDiff = Carbon::now()
                    ->diff(Carbon::tomorrow())
                    ->format('%hh %im %ss');

                return new JsonResponse([
                    'status' => 'exist_attendance',
                    'diff' => $timeDiff,
                ], Response::HTTP_CONFLICT);
            }

            if ($attendance->key_count <= self::KEY_MAX_COUNT) {
                $attendance->update([
                    AttendanceV2::KEY_COUNT => $attendance->key_count + 1,
                    AttendanceV2::KEY_ACQUIRED_AT => $keyAcquiredAt->push($date),
                ]);

                return new JsonResponse([
                    'status' => 'success',
                    AttendanceV2::KEY_COUNT => $attendance->key_count,
                ], Response::HTTP_OK);
            } else {
                return new JsonResponse([
                    'status' => 'max_key_count',
                    AttendanceV2::KEY_COUNT => $attendance->key_count,
                ], Response::HTTP_CONFLICT);
            }
        }
    }

    public function unpack(Request $request, string $id)
    {

    }
}
