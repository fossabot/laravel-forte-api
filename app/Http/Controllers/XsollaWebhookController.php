<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\XsollaWebhookService;

const TYPE_USER_VALIDATION = 'user_validation';
const TYPE_USER_SEARCH = 'user_search';
const TYPE_PAYMENT = 'payment';
const TYPE_REFUND = 'refund';
const TYPE_AFS_REJECT = 'afs_reject';
const TYPE_CREATE_SUBSCRIPTION = 'create_subscription';
const TYPE_UPDATE_SUBSCRIPTION = 'update_subscription';
const TYPE_CANCEL_SUBSCRIPTION = 'cancel_subscription';
const TYPE_GET_PINCODE = 'get_pincode';
const TYPE_USER_BALANCE_OPERATION = 'user_balance_operation';
const TYPE_REDEEM_KEY = 'redeem_key';
const TYPE_INVENTORY_GET = 'inventory_get';
const TYPE_INVENTORY_PULL = 'inventory_pull';
const TYPE_INVENTORY_PUSH = 'inventory_push';

class XsollaWebhookController extends Controller
{
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
            case TYPE_USER_VALIDATION:
                return $this->xws->userValidation($request->all());
            case TYPE_USER_SEARCH: // disable
                return $this->xws->userSearch();
            case TYPE_PAYMENT:
                return $this->xws->payment($request->all());
            case TYPE_REFUND:
                return $this->xws->refund($request->all());
            case TYPE_USER_BALANCE_OPERATION:
                return $this->xws->userBalanceOperation($request->all());
        }
    }
}
