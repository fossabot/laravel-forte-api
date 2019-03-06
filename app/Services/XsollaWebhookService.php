<?php

namespace App\Services;

use App\Item;
use App\User;
use App\Client;
use App\Discord;
use App\Receipt;
use App\UserItem;
use Illuminate\Support\Facades\DB;

/**
 * RESPONSE CODE
 * @see https://gist.github.com/jeffochoa/a162fc4381d69a2d862dafa61cda0798
 */
const HTTP_OK = 200;
const HTTP_CREATED = 201;
const HTTP_ACCEPTED = 202;
const HTTP_BAD_REQUEST = 400;
const HTTP_UNAUTHORIZED = 401;
const HTTP_PAYMENT_REQUIRED = 402;
const HTTP_FORBIDDEN = 403;
const HTTP_NOT_FOUND = 404;
const HTTP_INTERNAL_SERVER_ERROR = 500;
const HTTP_NOT_IMPLEMENTED = 501;

/**
 * Class XsollaWebhookService
 * @package App\Services
 * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_webhooks_list
 */
class XsollaWebhookService
{
    /**
     * @var $merchantId
     */
    protected $merchantId;
    /**
     * @var $projectId
     */
    protected $projectId;
    /**
     * @var $projectKey
     */
    protected $projectKey;
    /**
     * @var $apiKey
     */
    protected $apiKey;

    public function __construct() {
        $this->merchantId = config('xsolla.merchantId');
        $this->projectId = config('xsolla.projectId');
        $this->projectKey = config('xsolla.projectKey');
        $this->apiKey = config('xsolla.apiKey');
    }

    /**
     * it identifies the presence of users in the game system.
     *
     * @param $data
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_validation
     */
    public function userValidation($data) {
        if (User::where('email', $data['user']['id'])->orWhere('name', $data['user']['id'])->first()) {
            return response(HTTP_OK);
        } else {
            return response([
                'error' => [
                    'code' => 'INVALID_USER',
                    'message' => 'The user is invalid',
                ],
            ], HTTP_NOT_FOUND);
        }
    }

    /**
     * this method is buying the outside xsolla game store.
     *
     * @return string
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_search
     */
    public function userSearch() {
        return 'no use';
    }

    /**
     * sends when the user has completed the payment process.
     *
     * @param $data
     * @return mixed
     * @throws \Exception
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_payment
     */
    public function payment($data) {
        $userData = $data['user'];
        $purchaseData = $data['purchase'];

        try {
            DB::beginTransaction();

            $user = User::scopeGetUser(NULL, $userData['email']);

            if (isset($purchaseData['virtual_items'])) {
                foreach ($purchaseData['virtual_items']['items'] as $item) {
                    $purchaseItem = Item::where('sku', $item['sku'])->first();
                    UserItem::scopePurchaseUserItem($user->id, $purchaseItem->id, 'xsolla');
                }
            } else {
                $user->points += (round($purchaseData['virtual_currency']['amount']) * $purchaseData['virtual_currency']['quantity']);
            }

            $user->save();

            DB::commit();
            return response([
                'success' => [
                    'code' => 'SUCCESS_PAYMENT',
                    'message' => 'The payment has been successfully completed successfully.',
                ],
            ], HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return response([
                'error' => [
                    'code' => 'INVALID_PAYMENT',
                    'message' => 'Payment failed.',
                ],
            ], HTTP_PAYMENT_REQUIRED);
        }
    }

    /**
     * sends when payment is cancelled for unknown reason.
     *
     * @param $data
     * @return mixed
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_refund
     */
    public function refund($data) {
        return $data;
    }
}
