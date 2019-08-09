<?php

namespace App\Services;

use App\Models\Item;
use App\Models\User;
use App\Models\Receipt;
use App\Models\UserItem;
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
        if (User::scopeGetUser((int) $data['user']['id'])) {
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
        $transactionData = $data['transaction'];

        if (Receipt::scopeObserverTransaction($transactionData['id']) > 0) {
            // This payment is a duplicate payment.
            return response([
                'success' => [
                    'code' => 'SUCCESS_PAYMENT',
                    'message' => 'The payment has already handled.',
                ],
            ], HTTP_OK);
        }

        try {
            DB::beginTransaction();
            $user = User::scopeGetUser((int) $userData['id']);

            if (isset($purchaseData['virtual_items'])) {
                foreach ($purchaseData['virtual_items']['items'] as $item) {
                    $purchaseItem = Item::where('sku', $item['sku'])->first();
                    UserItem::scopePurchaseUserItem($user->id, $purchaseItem->id, 'xsolla');
                }
            } else {
                $receipt = new Receipt;
                $receipt->transaction_id = $transactionData['id'];
                $receipt->user_id = $user->id;
                $receipt->client_id = 1;
                $receipt->user_item_id = null;
                $receipt->about_cash = 1;
                $receipt->refund = 0;
                $receipt->points_old = $user->points;

                $quantity = $purchaseData['virtual_currency']['quantity'];
                $user->points += $quantity;

                $receipt->points_new = $user->points;
                $receipt->save();

                $userAction = [
                    'name' => $userData,
                    'purchase' => $purchaseData,
                ];

                (new \App\Http\Controllers\DiscordNotificationController)->xsollaUserAction('Payment', $userAction);

                $datas = [
                    'amount' => $quantity,
                    'comment' => 'Purchase '.$quantity.' points',
                ];
                $this->xsollaAPI->requestAPI('POST', 'projects/:projectId/users/'.$receipt->user_id.'/recharge', $datas);
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
    public function refund(array $data)
    {
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
                // $this->operationPayment($data);
                break;
            case 'inGamePurchase':
                // $this->operationPurchase($data);
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
        $userId = (int) $userData['id'];
        $user = User::scopeGetUser($userId);

        $items = $data['items'];

        try {
            DB::beginTransaction();
            if ($data['items_operation_type'] == 'add') {
                foreach ($items as $item) {
                    UserItem::scopePurchaseUserItem($userId, Item::scopeSkuParseId($item['sku']), 'xsolla');
                }
            }

            $user->points = $data['virtual_currency_balance']['new_value'];
            $user->save();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            (new \App\Http\Controllers\DiscordNotificationController)->exception($exception, $data);

            return $exception->getMessage();
        }

        $userAction = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'items' => $items,
            'balance' => $data['virtual_currency_balance'],
        ];

        (new \App\Http\Controllers\DiscordNotificationController)->xsollaUserAction('Item Purchase', $userAction);

        return response()->json([
            'message' => 'Success',
        ], 200);
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
