<?php

namespace App\Controller;

use App\Service\NotificationApiService;
use App\Service\RabbitMQService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends  AbstractController
{

    private $notificationApiService;

    public function __construct(NotificationApiService $notificationApiService)
    {
        $this->notificationApiService = $notificationApiService;
    }

    /**
     * @Route("/api/notifications", name="get_notifications", methods={"GET"})
     */
    public function getNotifications()
    {
        return new Response('ok');
        $json = $this->notificationApiService->getNotifications();

        return new JsonResponse($json, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/notification", methods={"POST"})
     */
    public function sendNotification(Request $request, RabbitMQService $rabbitMQService): JsonResponse
    {
        $input = json_decode($request->getContent(), true);
        $userId = $input['user_id'];
        $message = $input['message'];

        // Validate input
        if (!$userId || !$message) {
            return new JsonResponse(['error' => 'Invalid input'], Response::HTTP_BAD_REQUEST);
        }

        // Define the message to publish
        $data = [
            'user_id' => $userId,
            'message' => $message
        ];
        $routingKey = 'notifications_routing';

        // Publish the message to the notification_exchange with the user ID as the routing key
        $rabbitMQService->publishMessage('notification_exchange', $routingKey, $data);
        // Close the RabbitMQ connection
        $rabbitMQService->close();

        $this->notificationApiService->sendNotification($data);

        return new JsonResponse(['Notification sent!']);
    }
}
