<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceBoxType;
use App\Exceptions\MessageException;
use App\Http\Requests\AttendanceUnpackRequest;
use App\Jobs\XsollaRechargeJob;
use App\Models\AttendanceV2;
use App\Models\Receipt;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\XsollaAPIService;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Queue;
use Throwable;
use UnexpectedValueException;

class AttendanceController extends Controller
{
    private const KEY_MAX_COUNT = 10;

    /**
     * @var AttendanceService
     */
    private AttendanceService $attendanceSerivce;

    /**
     * @var XsollaAPIService
     */
    private XsollaAPIService $xsollaAPIService;

    /**
     * AttendanceController constructor.
     * @param AttendanceService $attendanceSerivce
     * @param XsollaAPIService $xsollaAPIService
     */
    public function __construct(AttendanceService $attendanceSerivce, XsollaAPIService $xsollaAPIService)
    {
        $this->attendanceSerivce = $attendanceSerivce;
        $this->xsollaAPIService = $xsollaAPIService;
    }

    /**
     * 팀 크레센도 디스코드 이용자의 key 개수를 확인합니다.
     *
     * @param string $id
     * @return JsonResponse
     *
     * @SWG\GET(
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
     *     @SWG\Response(
     *         response=200,
     *         description="Successful User Attendance v2"
     *     ),
     * )
     */
    public function show(string $id): JsonResponse
    {
        $attendance = AttendanceV2::whereDiscordId($id)->first();

        return new JsonResponse($attendance);
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

        if (! $attendance) {
            AttendanceV2::insert([
                AttendanceV2::DISCORD_ID => $id,
                AttendanceV2::KEY_ACQUIRED_AT => json_encode([$date]),
            ]);

            return new JsonResponse([
                'status' => 'success',
                AttendanceV2::KEY_COUNT => 1,
            ], Response::HTTP_CREATED);
        } else {
            $keyAcquiredAt = collect($attendance->key_acquired_at);

            if ($keyAcquiredAt->last() && Carbon::parse($keyAcquiredAt->last())->isToday()) {
                $timeDiff = Carbon::now()
                    ->diff(Carbon::tomorrow())
                    ->format('%hh %im %ss');

                return new JsonResponse([
                    'status' => 'exist_attendance',
                    'diff' => $timeDiff,
                ], Response::HTTP_CONFLICT);
            }

            if ($attendance->key_count < self::KEY_MAX_COUNT) {
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

    /**
     * 출석체크 박스를 개봉합니다.
     *
     * @param AttendanceUnpackRequest $request
     * @param string $id
     * @return JsonResponse
     * @throws Exception
     * @throws Throwable
     *
     * @SWG\POST(
     *     path="/discords/{discordId}/attendances/unpack",
     *     description="User Attendance Box unpack v2",
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
     *         name="box",
     *         in="query",
     *         description="Box Name",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="isPremium",
     *         in="query",
     *         description="User Premium Role Check",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful User Attendance Box unpack v2"
     *     ),
     * )
     */
    public function unpack(AttendanceUnpackRequest $request, string $id): JsonResponse
    {
        $attendance = AttendanceV2::query()
            ->whereDiscordId($id)
            ->firstOrFail();
        $user = User::whereDiscordId($id)->firstOrFail();
        $key = $attendance->key_count;
        $isPremium = $request->isPremium ?? false;
        $box = Str::lower($request->box);

        $demandKey = $this->checkValidateBoxFromKeyCount($box, $key, $isPremium);
        $package = $this->buildProbabilityBoxPackage($box);

        $unpackFromPoint = $this->buildPointByProbability($package);

        DB::beginTransaction();
        try {
            $oldPoints = $user->points;
            $user->points += $unpackFromPoint;
            $user->save();

            $boxUnpackedAt = collect($attendance->box_unpacked_at);
            $attendance->key_count = $attendance->key_count - $demandKey;
            $attendance->box_unpacked_at = $boxUnpackedAt->push(Carbon::now()->toDateTimeString());
            $attendance->save();

            Receipt::store($user->id, 5, null, 0, 0, $oldPoints, $user->points, 0);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            app(DiscordNotificationController::class)->exception($e, $request->all());
        }

        Queue::pushOn('xsolla-recharge', new XsollaRechargeJob($user, $unpackFromPoint, '포르테 출석체크 보상'));
        app(DiscordNotificationController::class)->point($user->email, $user->discord_id, $unpackFromPoint, $user->points);

        return new JsonResponse(
            [
                'point' => $unpackFromPoint,
                'key_count' => $attendance->key_count,
            ],
        );
    }

    /**
     * @param array $package
     * @return int
     */
    private function buildPointByProbability(array $package): int
    {
        $package = collect($package);
        $probabilities = collect($package->keys()->all());
        $points = collect($package->values()->all());

        $RAND = mt_rand(1, 1e6);

        if ($RAND % 100 < $probabilities->min()) {
            return $points->max();
        } elseif ($RAND % 100 < $probabilities->last()) {
            return $points->last();
        }

        return $points->min();
    }

    /**
     * @param string $box
     * @param int $key
     * @param bool $isPremium
     * @return int
     * @throws MessageException
     */
    private function checkValidateBoxFromKeyCount(string $box, int $key, bool $isPremium): int
    {
        switch ($box) {
            case AttendanceBoxType::BRONZE:
                $demandKey = 3;
                break;
            case AttendanceBoxType::SILVER:
                $demandKey = $isPremium ? 5 : 6;
                break;
            case AttendanceBoxType::GOLD:
                $demandKey = $isPremium ? 8 : 10;
                break;
            default:
                throw new UnexpectedValueException('올바르지 않은 상자깡 시도입니다. (box=bronze, silver, gold)');
        }

        if ($key < $demandKey) {
            throw new MessageException('상자를 여는데 필요한 열쇠가 부족합니다.');
        }

        return $demandKey;
    }

    /**
     * @param string $box
     * @return array
     */
    private function buildProbabilityBoxPackage(string $box): array
    {
        switch ($box) {
            case AttendanceBoxType::BRONZE:
                $package = [
                    69 => 3,
                    7 => 20,
                    24 => 10,
                ];
                break;
            case AttendanceBoxType::SILVER:
                $package = [
                    70 => 10,
                    5 => 50,
                    25 => 30,
                ];
                break;
            case AttendanceBoxType::GOLD:
                $package = [
                    71 => 20,
                    3 => 100,
                    26 => 60,
                ];
                break;
            default:
                throw new UnexpectedValueException();
        }

        return $package;
    }
}
