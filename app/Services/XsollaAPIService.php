<?php

namespace App\Services;

use App\Http\Controllers\DiscordNotificationController;
use App\Models\Item;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
    protected $client;
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
     * @var
     */
    protected $authKey;
    /**
     * @var
     */
    protected $endpoint;
    /**
     * @var
     */
    protected $command;

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

            throw new BadRequestHttpException($exception->getMessage());
        }
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncItems()
    {
        $this->print('== Xsolla Sync from Forte Items Start ==');

        $count = 0;
        $xsollaItemsSku = [];
        $xsollaItemIds = [];
        $xsollaDuplicateItemsSku = [];

        try {
            $xsollaItems = json_decode($this->request('GET', 'projects/:projectId/virtual_items/items', []), true);

            foreach ($xsollaItems as $item) {
                if (! Item::ofSku($item['sku'])->first()) {
                    $xsollaDuplicateItemsSku[] = $item['sku'];
                }
                $xsollaItemsSku[] = $item['sku'];
                $xsollaItemIds[] = $item['id'];
            }

            Item::whereNotIn(Item::SKU, $xsollaItemsSku)->delete();

            // 각 아이템 고유 ID에 대해 세부 페이지에 접속해서 동기화시킨다.
            foreach ($xsollaItemIds as $xsollaItemId) {
                $count++;
                $xsollaDetailItem = json_decode($this->request('GET', 'projects/:projectId/virtual_items/items/'.$xsollaItemId, []), true);
                $items = [
                    Item::NAME => $xsollaDetailItem['name']['ko'] ?? $xsollaDetailItem['name']['en'],
                    Item::IMAGE_URL => $xsollaDetailItem['image_url'],
                    Item::PRICE => $xsollaDetailItem['virtual_currency_price'] ?? 0,
                    Item::ENABLED => $xsollaDetailItem['enabled'] == true ? 1 : 0,
                    Item::CONSUMABLE => $xsollaDetailItem['permanent'] == true ? 0 : 1,
                    Item::EXPIRATION_TIME => $xsollaDetailItem['expiration'] ?? null,
                    Item::PURCHASE_LIMIT => $xsollaDetailItem['purchase_limit'] ?? null,
                ];

                // Forte DB 에 아이템이 없을 경우 생성
                if (! Item::ofSku($xsollaDetailItem['sku'])->first()) {
                    $convertSku = array_search(explode('_', $xsollaDetailItem['sku']), self::SKU_PREFIX);
                    $items = array_merge($items,
                        [
                            Item::CLIENT_ID => \App\Models\Client::whereName($convertSku)->value('id'),
                        ],
                        [
                            Item::SKU => $xsollaDetailItem['sku'],
                        ],
                    );

                    Item::create($items);
                } else {
                    Item::ofSku($xsollaDetailItem['sku'])->update($items);
                }
            }
        } catch (Exception $exception) {
            app(DiscordNotificationController::class)->exception($exception, $xsollaItemsSku);

            return $exception->getMessage();
        }

        app(DiscordNotificationController::class)->sync($count, $xsollaDuplicateItemsSku);
        $this->print('== End Xsolla Sync from Forte Items ==');
    }

    /**
     * @param string $message
     */
    private function print(string $message)
    {
        if ($this->command) {
            $this->command->info($message);
        } else {
            dump($message);
        }
    }
}
