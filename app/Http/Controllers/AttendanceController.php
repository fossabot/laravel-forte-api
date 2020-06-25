<?php

namespace App\Http\Controllers;

use App\Models\AttendanceV2;
use App\Models\Receipt;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\XsollaAPIService;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Boolean;
use UnexpectedValueException;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AttendanceController extends Controller
{
    private const KEY_MAX_COUNT = 10;
    private const BOX_UNPACKED_BRONZE = 'bronze';
    private const BOX_UNPACKED_SILVER = 'silver';
    private const BOX_UNPACKED_GOLD = 'gold';

    /**
     * @var AttendanceService $attendanceSerivce
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
     * @param Request $request
     * @param string $id
     * @return void
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
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful User Attendance Box unpack v2"
     *     ),
     * )
     */
    public function unpack(Request $request, string $id)
    {
        $attendance = AttendanceV2::query()
            ->whereDiscordId($id)
            ->firstOrFail();
        $user = User::scopeGetUserByDiscordId($id);
        $key = $attendance->key_count;
        $isPremium = $request->isPremium ?? false;

        $this->checkValidateBoxFromKeyCount($request->box, $key, $isPremium);
        $package = $this->buildProbabilityBoxPackage($request->box);

        $unpackFromPoint = $this->buildProbability($package);

        $oldPoints = $user->point;
        $user->point += $unpackFromPoint;
        $user->save();

        $boxUnpackedAt = $attendance->box_unpacked_at;
        $attendance->key_count -= $attendance->key_count;
        $attendance->box_unpacked_at = $boxUnpackedAt->push(Carbon::now()->toDateTimeString());
        $attendance->save();

        $repetition = false;
        $needPoint = 0;

        // TODO: api v2 개발 후 리팩토링
        $receipt = new Receipt;
        $receipt->user_id = $user->id;
        $receipt->client_id = 5; // Lara
        $receipt->user_item_id = null;
        $receipt->about_cash = 0;
        $receipt->refund = 0;
        $receipt->points_old = $oldPoints;
        $receipt->points_new = $user->points;
        $receipt->save();

        while (true) {
            $datas = [
                'amount' => $repetition ? $needPoint : $unpackFromPoint,
                'comment' => '포르테 출석체크 보상',
                'project_id' => env('XSOLLA_PROJECT_KEY'),
                'user_id' => $receipt->user_id,
            ];

            $response = json_decode($this->xsollaAPIService->requestAPI('POST', 'projects/:projectId/users/'.$receipt->user_id.'/recharge', $datas), true);

            if ($user->points !== $response['amount']) {
                $repetition = true;
                $needPoint = $user->points - $response['amount'];
                continue;
            } else {
                break;
            }
        }

        (new \App\Http\Controllers\DiscordNotificationController)->point($user->email, $user->discord_id, $unpackFromPoint, $user->points);
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
        } else if ($RAND % 100 < $probabilities->last()) {
            return $points->last();
        }

        return $points->min();
    }

    /**
     * @param string $box
     * @param int $key
     * @param bool $isPremium
     * @return void
     */
    private function checkValidateBoxFromKeyCount(string $box, int $key, bool $isPremium): void
    {
        switch ($box) {
            case self::BOX_UNPACKED_BRONZE:
                $passed = $key === 3 ?? false;
                break;
            case self::BOX_UNPACKED_SILVER:
                $passed = $key === (6 - ($isPremium && -1)) ?? false;
                break;
            case self::BOX_UNPACKED_GOLD:
                $passed = $key === (10 - ($isPremium && -2)) ?? false;
                break;
            default:
                throw new UnexpectedValueException();
        }

        if (! $passed) {
            throw new UnexpectedValueException('오픈하려는 상자의 Key가 부족합니다.');
        }
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
