<?php

namespace App\Service;

use GuzzleHttp\Client;

class NotificationApiService
{
    private $apiBaseUri;
    private $client;

    public function __construct(string $apiBaseUri)
    {
        $this->apiBaseUri = $apiBaseUri;

        $this->client = new Client([
            'base_uri' => $this->apiBaseUri,
            'timeout' => 5.0,
            'verify' => false
        ]);
    }

    public function getNotifications(): string
    {
        $response = $this->client->request('GET', 'notifications');
        return $response->getBody()->getContents();
    }

    public function sendNotification($data)
    {
        $data = [
            'text' => 'sadasdasd',
            'type' => 'system',
        ];

        $jsonData = json_encode($data);
        $response = $this->client->request('POST', 'notification', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $jsonData,
        ]);

        return $response->getBody()->getContents();
    }
}
