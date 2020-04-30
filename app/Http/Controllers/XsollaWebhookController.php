<?php

namespace App\Http\Controllers;

use App\Services\XsollaWebhookService;
use Illuminate\Http\Request;

class XsollaWebhookController extends Controller
{
    const TYPE_USER_VALIDATION = 'user_validation';
    const TYPE_USER_SEARCH = 'user_search';
    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND = 'refund';
    const TYPE_USER_BALANCE_OPERATION = 'user_balance_operation';

    /**
     * @var XsollaWebhookService
     */
    protected $xws;

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
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     * @throws \Exception
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_webhooks_list
     */
    public function index(Request $request)
    {
        switch ($request['notification_type']) {
            case self::TYPE_USER_VALIDATION:
                return $this->xws->userValidation($request->all());
            case self::TYPE_USER_SEARCH: // disable
                return $this->xws->userSearch();
            case self::TYPE_PAYMENT:
                return $this->xws->payment($request->all());
            case self::TYPE_REFUND:
                return $this->xws->refund($request->all());
            case self::TYPE_USER_BALANCE_OPERATION:
                return $this->xws->userBalanceOperation($request->all());
        }
    }
}
