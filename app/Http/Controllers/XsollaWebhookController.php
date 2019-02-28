<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\XsollaWebhookService;

const TYPE_USER_VALIDATION = 'user_validation';
const TYPE_USER_SEARCH = 'user_search';
const TYPE_PAYMENT = 'payment';

class XsollaWebhookController extends Controller {
    /**
     * @var XsollaWebhookService $xws
     */
    protected $xws;

    /**
     * XsollaWebhookController constructor.
     * @param XsollaWebhookService $xws
     */
    public function __construct(XsollaWebhookService $xws) {
        $this->merchantId = env('XSOLLA_MERCHANT_ID', '');
        $this->projectId = env('XSOLLA_PROJECT_ID', '');
        $this->projectKey = env('XSOLLA_PROJECT_KEY', '');
        $this->apiKey = env('XSOLLA_API_KEY', '');

        $this->xws = $xws;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_validation
     */
    public function index(Request $request) {
        switch ($request['notification_type']) {
            case TYPE_USER_VALIDATION:
                return $this->xws->userValidation($request->all());
            case TYPE_USER_SEARCH:
                return $this->xws->userSearch($request->all());
            case TYPE_PAYMENT:
                return $this->xws->payment($request->all());
        }
    }

}
