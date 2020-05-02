<?php

namespace App\Services;

use App\Http\Controllers\DiscordNotificationController;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\User;
use App\Models\UserItem;
use Exception as ExceptionAlias;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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
     * @return Array|JsonResponse
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_validation
     */
    public function userValidation(array $data): JsonResponse
    {
        if (! User::scopeGetUser((int) $data['user']['id'])) {
            return new JsonResponse([
                'error' => [
                    'code' => 'INVALID_USER',
                    'message' => 'The user is invalid',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'success' => [
                'code' => 'VALID_USER',
                'message' => 'The user is valid',
            ],
        ], Response::HTTP_OK);
    }

    /**
     * sends when the user has completed the payment process.
     *
     * @param array $data
     * @return JsonResponse
     * @throws ExceptionAlias
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_payment
     */
    public function payment(array $data): JsonResponse
    {
        $userData = $data['user'];
        $purchaseData = $data['purchase'];
        $transactionData = $data['transaction'];

        if (Receipt::scopeObserverTransaction($transactionData['id']) > 0) {
            // This payment is a duplicate payment.
            return new JsonResponse([
                'success' => [
                    'code' => 'SUCCESS_PAYMENT',
                    'message' => 'The payment has already handled.',
                ],
            ], Response::HTTP_OK);
        }

        try {
            DB::beginTransaction();
            $user = User::scopeGetUser((int) $userData['id']);

            if (isset($purchaseData['virtual_items'])) {
                foreach ($purchaseData['virtual_items']['items'] as $item) {
                    $purchaseItem = Item::where(Item::SKU, $item['sku'])->first();
                    UserItem::scopePurchaseUserItem($user->id, $purchaseItem->id, 'xsolla');
                }
            } else {
                $oldPoint = $user->{User::POINTS};
                $quantity = $purchaseData['virtual_currency']['quantity'];
                $user->{User::POINTS} += $quantity;

                Receipt::scopeCreateReceipt($user->id, 1, null, 1, 0, $oldPoint, $user->{User::POINTS}, $transactionData['id']);

                $userAction = [
                    'name' => $userData,
                    'purchase' => $purchaseData,
                ];

                (new DiscordNotificationController)->xsollaUserAction('Payment', $userAction);
            }

            $user->save();

            DB::commit();

            return new JsonResponse([
                'success' => [
                    'code' => 'SUCCESS_PAYMENT',
                    'message' => 'The payment has been completed successfully.',
                ],
            ], Response::HTTP_OK);
        } catch (ExceptionAlias $exception) {
            DB::rollback();

            return new JsonResponse([
                'error' => [
                    'code' => 'INVALID_PAYMENT',
                    'message' => 'Payment failed.',
                ],
            ], Response::HTTP_PAYMENT_REQUIRED);
        }
    }

    /**
     * sends when payment is cancelled for unknown reason.
     * @param array $data
     * @return Array|array
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_refund
     * @deprecated xsolla webhooks refund method is no use
     */
    public function refund(array $data)
    {
        return $data;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ExceptionAlias
     *@see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_payment
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
     * handles in-game purchase.
     *
     * @param array $data
     * @return string
     * @throws ExceptionAlias
     *@see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_purchase
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
                    UserItem::scopePurchaseUserItem($userId, Item::scopeSkuParseId($item{Item::SKU}), 'xsolla');
                }
            }

            $user->{USER::POINTS} = $data['virtual_currency_balance']['new_value'];
            $user->save();
            DB::commit();
        } catch (ExceptionAlias $exception) {
            DB::rollback();
            (new DiscordNotificationController)->exception($exception, $data);

            return $exception->getMessage();
        }

        $userAction = [
            'name' => $userData['name'],
            'items' => $items,
            'balance' => $data['virtual_currency_balance'],
        ];

        (new DiscordNotificationController)->xsollaUserAction('Item Purchase', $userAction);

        return new JsonResponse([
            'message' => 'Success',
        ], 200);
    }

    /**
     * @param array $data
     * @return Array
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_redeem_coupon
     */
    private function operationCoupon(array $data): Array
    {
        return $this->operationPointRelevant($data);
    }

    /**
     * @param array $data
     * @return Array
     * @deprecated
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_manual_update
     */
    private function operationInternal(array $data): Array
    {
        return $this->operationPointRelevant($data);
    }

    /**
     * @param array $data
     * @return Array
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_refund
     */
    private function operationRefund(array $data): Array
    {
        return $this->operationPointRelevant($data);
    }

    /**
     * User Balance Operation Central Point Processing Function.
     * @param array $data
     * @return Array|array
     */
    private function operationPointRelevant(array $data): Array
    {
        // TODO: sync xsolla with crescendo API if points are different

        $repetition = false;
        $needPoint = 0;

        $userData = $data['user'];
        $transactionData = $data['transaction'];
        if (Receipt::scopeObserverTransaction($transactionData['id']) > 0) {
            return ['error' => 'Duplicated point relevant'];
        }

        $virtualCurrencyBalance = $data['virtual_currency_balance'];
        $user = User::scopeGetUser($userData['id']);

        $oldPoints = $user->{User::POINTS};
        $user->{User::POINTS} += $virtualCurrencyBalance['new_value'] - $virtualCurrencyBalance['old_value'];
        $user->save();

        $receipt = Receipt::scopeCreateReceipt($user->id, 1, null, 1, 0, $oldPoints, $user->{User::POINTS}, $transactionData['id']);

        while (true) {
            $datas = [
                'amount' => $repetition ? $needPoint : $virtualCurrencyBalance['new_value'],
                'comment' => '이용자 포인트 업데이트',
                'project_id' => config('xsolla.projectKey'),
                'user_id' => $receipt->{Receipt::USER_ID},
            ];

            $response = json_decode($this->xsollaAPI->requestAPI('POST', 'projects/:projectId/users/'.$receipt->{Receipt::USER_ID}.'/recharge', $datas), true);

            if ($user->points !== $response['amount']) {
                $repetition = true;
                $needPoint = $user->{User::POINTS} - $response['amount'];
                continue;
            } else {
                break;
            }
        }

        return ['receipt_id' => $receipt->id];
    }
}
