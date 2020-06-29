<?php

namespace App\Services;

use App\Http\Controllers\DiscordNotificationController;
use App\Http\Controllers\PointController;
use App\Jobs\XsollaRechargeJob;
use App\Models\Client;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Queue;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use UnexpectedValueException;

/**
 * Class XsollaWebhookService.
 * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_webhooks_list
 */
class XsollaWebhookService
{
    protected $merchantId;
    protected $projectId;
    protected $projectKey;
    protected $apiKey;
    /**
     * @var XsollaAPIService
     */
    protected XsollaAPIService $xsollaAPI;
    /**
     * @var UserService
     */
    protected UserService $userService;
    /**
     * @var ReceiptService
     */
    protected ReceiptService $receiptService;
    /**
     * @var UserItemService
     */
    protected UserItemService $userItemService;

    /**
     * XsollaWebhookService constructor.
     * @param XsollaAPIService $xsollaAPI
     * @param UserService $userService
     * @param ReceiptService $receiptService
     * @param UserItemService $userItemService
     */
    public function __construct(
        XsollaAPIService $xsollaAPI,
        UserService $userService,
        ReceiptService $receiptService,
        UserItemService $userItemService
    ) {
        $this->xsollaAPI = $xsollaAPI;
        $this->merchantId = config('xsolla.merchant_id');
        $this->projectId = config('xsolla.project_id');
        $this->projectKey = config('xsolla.project_key');
        $this->apiKey = config('xsolla.api_key');

        $this->userService = $userService;
        $this->receiptService = $receiptService;
        $this->userItemService = $userItemService;
    }

    /**
     * it identifies the presence of users in the game system.
     *
     * @param array $data
     * @return array|JsonResponse
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_validation
     */
    public function userValidation(array $data): JsonResponse
    {
        if (! $this->userService->show((int) $data['user']['id'])) {
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
     * @throws Exception
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_payment
     */
    public function payment(array $data): JsonResponse
    {
        $userData = $data['user'];
        $purchaseData = $data['purchase'];
        $transactionData = $data['transaction'];

        if (Receipt::ofTransactionCount($transactionData['id']) > 0) {
            // This payment is a duplicate payment.
            return new JsonResponse([
                'success' => [
                    'code' => 'SUCCESS_PAYMENT',
                    'message' => 'The payment has already handled.',
                ],
            ], Response::HTTP_OK);
        }

        DB::beginTransaction();
        try {
            $user = $this->userService->show((int) $userData['id']);

            if (isset($purchaseData['virtual_items'])) {
                foreach ($purchaseData['virtual_items']['items'] as $item) {
                    $this->userItemService->save($user, Item::convertSkuToId($item[Item::SKU]), Client::XSOLLA);
                }
            } else {
                $oldPoint = $user->points;
                $quantity = $purchaseData['virtual_currency']['quantity'];
                $user->points += $quantity;

                Receipt::store($user->id, 1, null, 1, 0, $oldPoint, $user->points, $transactionData['id']);

                $userAction = [
                    'name' => $userData,
                    'purchase' => $purchaseData,
                ];

                app(DiscordNotificationController::class)->xsollaUserAction('Payment', $userAction);
            }

            $user->save();

            DB::commit();

            return new JsonResponse([
                'success' => [
                    'code' => 'SUCCESS_PAYMENT',
                    'message' => 'The payment has been completed successfully.',
                ],
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
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
     * @return array|array
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
     * @throws Exception
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_payment
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
     * @throws Exception
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_purchase
     */
    private function operationPurchase(array $data)
    {
        $userData = $data['user'];
        $userId = (int) $userData['id'];
        $user = $this->userService->show($userId);

        $items = $data['items'];

        DB::beginTransaction();
        try {
            if ($data['items_operation_type'] === 'add') {
                foreach ($items as $item) {
                    $this->userItemService->save($user, Item::convertSkuToId($item[Item::SKU]), Client::XSOLLA);
                }
            }

            $user->{USER::POINTS} = $data['virtual_currency_balance']['new_value'];
            $user->save();
            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            app(DiscordNotificationController::class)->exception($exception, $data);

            throw new UnexpectedValueException($exception->getMessage());
        }

        $userAction = [
            'name' => $userData['name'] ?? $ $userData['email'],
            'items' => $items,
            'balance' => $data['virtual_currency_balance'],
        ];

        (new DiscordNotificationController)->xsollaUserAction('Item Purchase', $userAction);

        return new JsonResponse([
            'message' => 'Success',
        ], Response::HTTP_OK);
    }

    /**
     * @param array $data
     * @return array
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_redeem_coupon
     */
    private function operationCoupon(array $data): array
    {
        return $this->operationPointRelevant($data);
    }

    /**
     * @param array $data
     * @return array
     * @deprecated
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_manual_update
     */
    private function operationInternal(array $data): array
    {
        return $this->operationPointRelevant($data);
    }

    /**
     * @param array $data
     * @return array
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_refund
     */
    private function operationRefund(array $data): array
    {
        return $this->operationPointRelevant($data);
    }

    /**
     * User Balance Operation Central Point Processing Function.
     * @param array $data
     * @return array|array
     */
    private function operationPointRelevant(array $data): array
    {
        $userData = $data['user'];
        $transactionData = $data['transaction'];
        if (Receipt::ofTransactionCount($transactionData['id']) > 0) {
            throw new ConflictHttpException('Duplicated point relevant');
        }

        $virtualCurrencyBalance = $data['virtual_currency_balance'];
        $user = $this->userService->show($userData['id']);

        $oldPoints = $user->points;
        $user->points += $virtualCurrencyBalance['new_value'] - $virtualCurrencyBalance['old_value'];
        $user->save();

        $receipt = Receipt::store($user->id, 1, null, 1, 0, $oldPoints, $user->points, $transactionData['id']);

        Queue::push(new XsollaRechargeJob($user, $virtualCurrencyBalance['new_value'], '이용자 포인트 업데이트'));

        return ['receipt_id' => $receipt->{Receipt::ID}];
    }
}
