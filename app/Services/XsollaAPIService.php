<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class XsollaAPIService
{
    /**
     * @var Client
     */
    protected $client;
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
    /**
     * @var $authKey
     */
    protected $authKey;
    /**
     * @var $endpoint
     */
    protected $endpoint;

    /**
     * XsollaAPIService constructor.
     * @param Client $client
     */
    public function __construct(Client $client) {
        $this->client = $client;
        $this->merchantId = config('xsolla.merchantId');
        $this->projectId = config('xsolla.projectId');
        $this->projectKey = config('xsolla.projectKey');
        $this->apiKey = config('xsolla.apiKey');
        $this->authKey = base64_encode($this->merchantId . ':' . $this->apiKey);
        $this->endpoint = 'https://api.xsolla.com/merchant/v2/';
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $datas
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function requestAPI(string $method, string $uri, array $datas) {
        if (strpos($uri, ':projectId') !== false) {
            $uri = str_replace(':projectId', $this->projectId, $uri);
        } else {
            $uri = str_replace(':merchantId', $this->merchantId, $uri);
        }

        try {
            $response = $this->client->request($method, $this->endpoint . $uri, [
                'body' => json_encode($datas),
                'headers' => [
                    'Authorization' => 'Basic ' . $this->authKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                ],
            ]);

            return $response->getBody();
        } catch (GuzzleException $e) {
            (new \App\Http\Controllers\DiscordNotificationController)->exception($e, $datas);
            return $e->getMessage();
        }
    }
}
