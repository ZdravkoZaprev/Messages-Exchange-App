<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\NotificationApiService;
use App\Service\NotificationValidatorService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends  AbstractController
{
    private NotificationApiService $notificationApiService;
    private NotificationValidatorService $notificationValidatorService;
    private UserRepository $userRepository;

    public function __construct(
        NotificationApiService $notificationApiService,
        NotificationValidatorService $notificationValidatorService,
        UserRepository $userRepository
    ) {
        $this->notificationApiService = $notificationApiService;
        $this->notificationValidatorService = $notificationValidatorService;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/api/notifications", name="get_notifications", methods={"GET"})
     */
    public function getNotifications()
    {
        return new Response($this->notificationApiService->getNotifications());
    }

    /**
     * @Route("/api/notification", methods={"POST"})
     */
    public function sendNotification(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $notificationData = $this->notificationValidatorService->validatePostMsgData($data);

        return new Response($this->notificationApiService->sendNotification($notificationData));
    }

    /**
     * @Route("/api/notification/{id}", name="notification_patch", methods={"PATCH"})
     */
    public function updateNotification(int $id)
    {
        return new Response($this->notificationApiService->updateNotification($id));
    }
}
