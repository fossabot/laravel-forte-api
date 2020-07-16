<?php

namespace App\Http\Controllers;

use App\Services\XsollaWebhookService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class XsollaWebhookController extends Controller
{
    const TYPE_USER_VALIDATION = 'user_validation';
    const TYPE_PAYMENT = 'payment';
    const TYPE_USER_BALANCE_OPERATION = 'user_balance_operation';
    const TYPE_REFUND = 'refund';
    const TYPE_COUPON = 'coupon';
    const TYPE_IN_GAME_PURCHASE = 'inGamePurchase';

    /**
     * @var XsollaWebhookService
     */
    private XsollaWebhookService $xws;

    /**
     * XsollaWebhookController constructor.
     * @param XsollaWebhookService $xws
     */
    public function __construct(XsollaWebhookService $xws)
    {
        $this->xws = $xws;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     * @throws \Throwable
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_webhooks_list
     */
    public function index(Request $request)
    {
        $data = null;

        switch ($request['notification_type']) {
            case self::TYPE_USER_VALIDATION:
                $data = $this->xws->userValidation($request->all());
                break;
            case self::TYPE_PAYMENT:
                $data = $this->xws->payment($request->all());
                break;
            case self::TYPE_REFUND:
            case self::TYPE_USER_BALANCE_OPERATION:
                $data = $this->xws->userBalanceOperation($request->all());
                break;
        }

        return new JsonResponse($data);
    }
}
