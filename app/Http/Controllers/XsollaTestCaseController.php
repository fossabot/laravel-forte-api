<?php
/**
 * Created by PhpStorm.
 * User: solaris
 * Date: 2019-06-28
 * Time: 17:09.
 */

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Models\UserItem;
use Illuminate\Http\Request;

class XsollaTestCaseController
{
    public function index(Request $request)
    {
        switch ($request['notification_type']) {
            case 'user_balance_operation':
                try {
                    return $this->operationPurchase($request->all());
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
                break;
        }
    }

    /**
     * @param array $data
     * @return string
     * @see https://developers.xsolla.com/ko/api/v2/getting-started/#api_webhooks_user_balance_purchase
     * @throws \Exception
     */
    public function operationPurchase(array $data)
    {
        $userData = $data['user'];
        $items = $data['items'];

        try {
            if ($data['items_operation_type'] == 'add') {
                foreach ($items as $item) {
                    UserItem::scopePurchaseUserItem((int) $userData['id'], Item::scopeSkuParseId($item['sku']), 'xsolla');
                }
            }
        } catch (\Exception $exception) {
            (new \App\Http\Controllers\DiscordNotificationController)->exception($exception, $data);

            return $exception->getMessage();
        }
    }
}
