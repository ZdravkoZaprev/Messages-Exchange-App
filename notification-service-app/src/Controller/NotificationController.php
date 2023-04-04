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


        dd($asd);

        return $this->json(['sssssssssssssssssssssssssss' => true]);

        // // Retrieve the request body as an array
        // $data = json_decode($request->getContent(), true);

        // // Create a new Notification object and set its properties from the request data
        // $notification = new Notification();
        // $notification->setText($data['title']);
        // $notification->setMessage($data['message']);

        // // Save the new Notification to the database
        // $this->entityManager->persist($notification);
        // $this->entityManager->flush();

        // // Return a success response
        // return $this->json(['success' => true]);
    }

    /**
     * @Route("/api/notification/{id}", name="notification_patch", methods={"PATCH"})
     */
    public function updateNotification(int $id): JsonResponse
    {
        $notification = $this->notificationRepository->find($id);
        if (!$notification) {
            throw $this->createNotFoundException("Notification with id: {$id} not found");
        }

        if ($notification->isRead()) {
            return $this->json(['Notification already readed']);
        }

        $notification->setIsRead(1);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }
}
