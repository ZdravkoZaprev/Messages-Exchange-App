<?php

namespace App\Service;

use GuzzleHttp\Client;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotificationApiService
{
    private $apiBaseUri;
    private $client;
    private JWTTokenManagerInterface $jwtManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        string $apiBaseUri,
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->apiBaseUri = $apiBaseUri;
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;

        $this->client = new Client([
            'base_uri' => $this->apiBaseUri,
            'timeout' => 5.0,
            'verify' => false
        ]);
    }

    public function getNotifications(): string
    {
        return $this->makeRequest("GET", "notifications");
    }

    public function sendNotification($data)
    {
        return $this->makeRequest("POST", "notification", $data);
    }

    public function updateNotification($id): string
    {
        return $this->makeRequest("PATCH", "notification/{$id}");
    }

    private function makeRequest(string $method, string $uri, $data = null)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        $token = $this->jwtManager->create($user);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        $response = $this->client->request($method, $uri, [
            'headers' => $headers,
            'body' => $data ? json_encode($data) : null,
        ]);

        return $response->getBody()->getContents();
    }
}
