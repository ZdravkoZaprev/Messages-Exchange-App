<?php

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class NotificationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private NotificationRepository $notificationRepository;
    private NotificationService $notificationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
        NotificationService $notificationService
    ) {
        $this->entityManager = $entityManager;
        $this->notificationRepository = $notificationRepository;
        $this->notificationService = $notificationService;
    }

    /**
     * @Route("/api/notifications", name="notification_list", methods={"GET"})
     */
    public function getNotifications(): JsonResponse
    {
        $email = $this->getUser()->getEmail();
        $notifications = $this->notificationService->getNotificationsByRecipientEmail($email);
        return $this->json($notifications);
    }

    /**
     * @Route("/api/notification", name="notification_create", methods={"POST"})
     */
    public function createNotification(Request $request): JsonResponse
    {
        // Retrieve the request body as an array
        $data = json_decode($request->getContent(), true);
        $notificationResult = $this->notificationService->createNotification($data);

        // Return a success response
        return $this->json($notificationResult);
    }

    /**
     * @Route("/api/notification/{id}", name="notification_patch", methods={"PATCH"})
     */
    public function updateNotification(int $id): JsonResponse
    {
        $notification = $this->notificationService->setNotificationAsRead($id);

        return $this->json($notification);
    }
}
