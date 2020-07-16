<?php

namespace App\Services;

use App\Http\Controllers\DiscordNotificationController;
use App\Http\Controllers\XsollaWebhookController;
use App\Jobs\XsollaRechargeJob;
use App\Models\Client;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use DB;
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
     * @throws Exception
     * @throws \Throwable
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_payment
     */
    public function payment(array $data): JsonResponse
    {
        $userData = $data['user'];
        $purchaseData = $data['purchase'];
        $transactionData = $data['transaction'];

        if (Receipt::whereTransactionId($transactionData['id'])->exists()) {
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
            $user = User::findOrFail((int) $userData['id']);

            if (isset($purchaseData['virtual_items'])) {
                foreach ($purchaseData['virtual_items']['items'] as $item) {
                    $this->userItemService->save($user, Item::convertSkuToId($item[Item::SKU]), Client::XSOLLA);
                }
            } else {
                $oldPoint = $user->points;
                $quantity = $purchaseData['virtual_currency']['quantity'];

                $user->points += $quantity;
                $user->save();

                Receipt::store($user->id, 1, null, 1, 0, $oldPoint, $user->points, $transactionData['id']);

                $userAction = [
                    'name' => $userData,
                    'purchase' => $purchaseData,
                ];

                app(DiscordNotificationController::class)->xsollaUserAction('Payment', $userAction);
            }

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
     * @param array $data
     * @return mixed
     * @throws Exception
     * @throws \Throwable
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_payment
     */
    public function userBalanceOperation(array $data)
    {
        $operationType = $data['operation_type'];

        switch ($operationType) {
            case XsollaWebhookController::TYPE_COUPON:
            case XsollaWebhookController::TYPE_REFUND:
            case XsollaWebhookController::TYPE_PAYMENT:
                $this->operationPayment($data);
                break;
            case XsollaWebhookController::TYPE_IN_GAME_PURCHASE:
                $this->operationPurchase($data);
                break;
            default:
                throw new UnexpectedValueException();
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
     * @throws \Throwable
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_purchase
     */
    private function operationPurchase(array $data)
    {
        $userData = $data['user'];
        $userId = (int) $userData['id'];
        $user = User::findOrFail($userId);

        $items = $data['items'];

        DB::beginTransaction();
        try {
            if ($data['items_operation_type'] === 'add') {
                foreach ($items as $item) {
                    $this->userItemService->save($user, Item::convertSkuToId($item[Item::SKU]), Client::XSOLLA);
                }
            }

            $user->points = $data['virtual_currency_balance']['new_value'];
            $user->save();
            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            app(DiscordNotificationController::class)->exception($exception, $data);
        }

        $userAction = [
            'name' => $userData['name'] ?? $userData['email'],
            'items' => $items,
            'balance' => $data['virtual_currency_balance'],
        ];

        app(DiscordNotificationController::class)->xsollaUserAction('Item Purchase', $userAction);

        return new JsonResponse([
            'message' => 'Success',
        ], Response::HTTP_OK);
    }

    /**
     * User Balance Operation Central Point Processing Function.
     * @param array $data
     * @return array
     */
    private function operationPointRelevant(array $data): array
    {
        $userData = $data['user'];
        $transactionData = $data['transaction'];
        if (Receipt::whereTransactionId($transactionData['id'])->exists()) {
            throw new ConflictHttpException('Duplicated point relevant');
        }

        $virtualCurrencyBalance = $data['virtual_currency_balance'];
        $user = User::findOrFail($userData['id']);

        $oldPoints = $user->points;
        $user->points += $virtualCurrencyBalance['new_value'] - $virtualCurrencyBalance['old_value'];
        $user->save();

        $receipt = Receipt::store($user->id, 1, null, 1, 0, $oldPoints, $user->points, $transactionData['id']);

        Queue::pushOn('xsolla-recharge', new XsollaRechargeJob($user, $virtualCurrencyBalance['new_value'], '이용자 포인트 업데이트'));

        return ['receipt_id' => $receipt->id];
    }
}
