<?php

namespace App\Services;

use App\Item;
use App\User;
use App\Receipt;
use App\UserItem;
use Illuminate\Support\Facades\DB;

/**
 * RESPONSE CODE.
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
 * Class XsollaWebhookService.
 * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_webhooks_list
 */
class XsollaWebhookService
{
    /**
     * @var
     */
    protected $merchantId;
    /**
     * @var
     */
    protected $projectId;
    /**
     * @var
     */
    protected $projectKey;
    /**
     * @var
     */
    protected $apiKey;
    /**
     * @var XsollaAPIService
     */
    protected $xsollaAPI;

    public function __construct(XsollaAPIService $xsollaAPI)
    {
        $this->xsollaAPI = $xsollaAPI;
        $this->merchantId = config('xsolla.merchantId');
        $this->projectId = config('xsolla.projectId');
        $this->projectKey = config('xsolla.projectKey');
        $this->apiKey = config('xsolla.apiKey');
    }

    /**
     * it identifies the presence of users in the game system.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_validation
     */
    public function userValidation(array $data)
    {
        if (User::where('email', $data['user']['id'])->orWhere('name', $data['user']['id'])->first()) {
            return response([
                'success' => [
                    'code' => 'VALID_USER',
                    'message' => 'The user is valid',
                ],
            ], HTTP_OK);
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
     * @deprecated xsolla webhooks userSearch method is no use
     * @return string
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_search
     */
    public function userSearch()
    {
        return 'no use';
    }

    /**
     * sends when the user has completed the payment process.
     *
     * @param array $data
     * @return mixed
     * @throws \Exception
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_payment
     */
    public function payment(array $data)
    {
        $userData = $data['user'];
        $purchaseData = $data['purchase'];

        try {
            DB::beginTransaction();
            $user = User::scopeGetUserById($userData['id']);

            if (isset($purchaseData['virtual_items'])) {
                foreach ($purchaseData['virtual_items']['items'] as $item) {
                    $purchaseItem = Item::where('sku', $item['sku'])->first();
                    UserItem::scopePurchaseUserItem($user->id, $purchaseItem->id, 'xsolla');
                }
            } else {
                $receipt = new Receipt;
                $receipt->user_id = $user->id;
                $receipt->client_id = 1;
                $receipt->user_item_id = null;
                $receipt->about_cash = 1;
                $receipt->refund = 0;
                $receipt->points_old = $user->points;

                $user->points += $purchaseData['virtual_currency']['quantity'];

                $receipt->points_new = $user->points;
                $receipt->save();
            }

            $user->save();

            DB::commit();

            return response([
                'success' => [
                    'code' => 'SUCCESS_PAYMENT',
                    'message' => 'The payment has been completed successfully.',
                ],
            ], HTTP_OK);
        } catch (\Exception $exception) {
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
     * @deprecated xsolla webhooks refund method is no use
     * @param array $data
     * @return mixed
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_refund
     */
    public function refund(array $data) {
        return $data;
    }

    /**
     * @param array $data
     * @return mixed
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_payment
     * @throws \Exception
     */
    public function userBalanceOperation(array $data)
    {
        $operationType = $data['operation_type'];

        switch ($operationType) {
            case 'payment':
                $this->operationPayment($data);
                break;
            case 'inGamePurchase':
                $this->operationPurchase($data);
                break;
            case 'coupon':
                $this->operationCoupon($data);
                break;
            case 'internal':
//                $this->operationInternal($data);
                break;
            case 'refund':
                $this->operationRefund($data);
                break;
        }
    }

    /**
     * @param array $data
     * @return array
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_payment
     */
    private function operationPayment(array $data)
    {
        return $this->operationPointRelevant($data);
    }

    /**
     * @param array $data
     * @return string
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_purchase
     * @throws \Exception
     */
    private function operationPurchase(array $data)
    {
        $userData = $data['user'];
        $items = $data['items_operation_type']['items'];

        try {
            foreach ($items as $item) {
                UserItem::scopePurchaseUserItem($userData['id'], Item::scopeSkuParseId($item->sku), 'xsolla');
            }
        } catch (\Exception $exception) {
            (new \App\Http\Controllers\DiscordNotificationController)->exception($exception, $data);

            return $exception->getMessage();
        }
    }

    /**
     * @param array $data
     * @return array
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_redeem_coupon
     */
    private function operationCoupon(array $data)
    {
        return $this->operationPointRelevant($data);
    }

    /**
     * @deprecated
     * @param array $data
     * @return array
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_manual_update
     */
    private function operationInternal(array $data)
    {
        return $this->operationPointRelevant($data);
    }

    /**
     * @param array $data
     * @return array
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_refund
     */
    private function operationRefund(array $data)
    {
        return $this->operationPointRelevant($data);
    }

    /**
     * User Balance Operation Central Point Processing Function.
     * @param array $data
     * @return array
     */
    private function operationPointRelevant(array $data)
    {
        // TODO: sync xsolla with crescendo API if points are different

        $repetition = false;
        $needPoint = 0;

        $userData = $data['user'];
        $virtualCurrencyBalance = $data['virtual_currency_balance'];
        $user = User::scopeGetUser($userData['id']);

        $oldPoints = $user->points;
        $user->points += $virtualCurrencyBalance['new_value'] - $virtualCurrencyBalance['old_value'];
        $user->save();

        $receipt = new Receipt;
        $receipt->user_id = $user->id;
        $receipt->client_id = 1;
        $receipt->user_item_id = null;
        $receipt->about_cash = 0;
        $receipt->refund = 0;
        $receipt->points_old = $oldPoints;
        $receipt->points_new = $user->points;
        $receipt->save();

        while (true) {
            $datas = [
                'amount' => $repetition ? $needPoint : $virtualCurrencyBalance['new_value'],
                'comment' => 'Updated User Point => xsolla',
            ];

            $response = json_decode($this->xsollaAPI->requestAPI('POST', 'projects/:projectId/users/'.$receipt->user_id.'/recharge', $datas), true);

            if ($user->points !== $response['amount']) {
                $repetition = true;
                $needPoint = $user->points - $response['amount'];
                continue;
            } else {
                break;
            }
        }

        return ['receipt_id' => $receipt->id];
    }
}
