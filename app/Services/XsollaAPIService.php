<?php

namespace App\Services;

use App\Http\Controllers\DiscordNotificationController;
use App\Models\Item;
use DB;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use Psr\Http\Message\StreamInterface;

class XsollaAPIService
{
    const SKU_PREFIX = [
        'baechubotv2' => 'cabv2',
        'sangchuv2' => 'letv2',
        'skilebot' => 'skb',
    ];

    /**
     * @var Client
     */
    protected Client $client;
    /**
     * @var string
     */
    protected string $merchantId;
    /**
     * @var string
     */
    protected string $projectId;
    /**
     * @var string
     */
    protected string $projectKey;
    /**
     * @var string
     */
    protected string $apiKey;
    /**
     * @var string
     */
    protected string $authKey;
    /**
     * @var string
     */
    protected string $endpoint;

    /**
     * XsollaAPIService constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->merchantId = config('xsolla.merchant_id');
        $this->projectId = config('xsolla.project_id');
        $this->projectKey = config('xsolla.project_key');
        $this->apiKey = config('xsolla.api_key');
        $this->authKey = base64_encode($this->merchantId.':'.$this->apiKey);
        $this->endpoint = 'https://api.xsolla.com/merchant/v2/';
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $bodyData
     * @return array|StreamInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $method, string $uri, array $bodyData)
    {
        if (Str::contains($uri, 'projectId')) {
            $uri = str_replace(':projectId', $this->projectId, $uri);
        } else {
            $uri = str_replace(':merchantId', $this->merchantId, $uri);
        }

        try {
            $response = $this->client->request($method, $this->endpoint.$uri, [
                'body' => json_encode($bodyData),
                'headers' => [
                    'Authorization' => 'Basic '.$this->authKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                ],
            ]);

            return $response->getBody();
        } catch (ClientException $exception) {
            app(DiscordNotificationController::class)->exception($exception, $bodyData);
        }
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function syncItems(): void
    {
        $addedItemNames = [];

        DB::beginTransaction();
        try {
            $xsollaByItems = json_decode($this->request('GET', 'projects/:projectId/virtual_items/items', []), true);

            foreach ($xsollaByItems as $xsollaByItem) {
                $xsollaByItemDetail = json_decode($this->request('GET', 'projects/:projectId/virtual_items/items/'.$xsollaByItem['id'], []), true);

                $item = [
                    Item::SKU => $xsollaByItemDetail['sku'],
                    Item::NAME => $xsollaByItemDetail['name']['ko'] ?? $xsollaByItemDetail['name']['en'],
                    Item::IMAGE_URL => $xsollaByItemDetail['image_url'],
                    Item::PRICE => $xsollaByItemDetail['virtual_currency_price'] ?? 0,
                    Item::ENABLED => $xsollaByItemDetail['enabled'] == true ? 1 : 0,
                    Item::CONSUMABLE => $xsollaByItemDetail['permanent'] == true ? 0 : 1,
                    Item::EXPIRATION_TIME => $xsollaByItemDetail['expiration'] ?? null,
                    Item::PURCHASE_LIMIT => $xsollaByItemDetail['purchase_limit'] ?? null,
                ];

                $existItem = Item::whereSku($item['sku']);
                if ($existItem->first()) {
                    // TODO: isDirty check
                    $existItem->update($item);
                } else {
                    $sku = $this->convertXsollaSkuToPrefix($item['sku']);

                    $item[Item::CLIENT_ID] = \App\Models\Client::whereName($sku)->value('id');

                    $addedItemNames[] = $item[Item::NAME];
                    Item::create($item);
                }
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();

            app(DiscordNotificationController::class)->exception($exception, []);
        }

        app(DiscordNotificationController::class)->sync($addedItemNames);
    }

    /**
     * @param string $sku
     * @return string
     */
    protected function convertXsollaSkuToPrefix(string $sku): string
    {
        return array_search(explode('_', $sku)[0], self::SKU_PREFIX);
    }
}
