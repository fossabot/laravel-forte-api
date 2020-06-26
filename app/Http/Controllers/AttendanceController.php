<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceUnpackRequest;
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
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use UnexpectedValueException;

class AttendanceController extends Controller
{
    private const KEY_MAX_COUNT = 10;
    private const BOX_UNPACKED_BRONZE = 'bronze';
    private const BOX_UNPACKED_SILVER = 'silver';
    private const BOX_UNPACKED_GOLD = 'gold';

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
            $keyAcquiredAt = collect(json_decode($attendance->key_acquired_at, true));

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
        $user = User::whereDiscordId($id);
        $key = $attendance->key_count;
        $isPremium = $request->isPremium ?? false;
        $box = $request->box;

        $demandKey = $this->checkValidateBoxFromKeyCount($box, $key, $isPremium);
        $package = $this->buildProbabilityBoxPackage($box);

        $unpackFromPoint = $this->buildProbability($package);

        DB::beginTransaction();
        try {
            $oldPoints = $user->points;
            $user->points += $unpackFromPoint;
            $user->save();

            $boxUnpackedAt = collect(json_decode($attendance->box_unpacked_at, true));
            $attendance->key_count = $attendance->key_count - $demandKey;
            $attendance->box_unpacked_at = $boxUnpackedAt->push(Carbon::now()->toDateTimeString());
            $attendance->save();

            $receipt = Receipt::store($user->id, 5, null, 0, 0, $oldPoints, $user->points, 0);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            app(DiscordNotificationController::class)->exception($e, $request->all());

            throw new AccessDeniedException($e);
        }

        app(PointController::class)->recharge($unpackFromPoint, '포르테 출석체크 보상', $receipt->user_id);
        app(DiscordNotificationController::class)->point($user->email, $user->discord_id, $unpackFromPoint, $user->points);

        return new JsonResponse($attendance);
    }

    /**
     * @param array $package
     * @return int
     */
    private function buildProbability(array $package): int
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
     */
    private function checkValidateBoxFromKeyCount(string $box, int $key, bool $isPremium): int
    {
        switch ($box) {
            case self::BOX_UNPACKED_BRONZE:
                $demandKey = 3;
                break;
            case self::BOX_UNPACKED_SILVER:
                $demandKey = (6 - ($isPremium && -1));
                break;
            case self::BOX_UNPACKED_GOLD:
                $demandKey = (10 - ($isPremium && -2));
                break;
            default:
                throw new UnexpectedValueException('올바르지 않은 상자깡 시도입니다. (box=bronze, silver, gold)');
        }

        if (! $key >= $demandKey ?? false) {
            throw new UnexpectedValueException('오픈하려는 상자의 Key가 부족합니다.');
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
            case self::BOX_UNPACKED_BRONZE:
                $package = [
                    69 => 1,
                    7 => 10,
                    24 => 3,
                ];
                break;
            case self::BOX_UNPACKED_SILVER:
                $package = [
                    70 => 10,
                    5 => 50,
                    25 => 30,
                ];
                break;
            case self::BOX_UNPACKED_GOLD:
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
