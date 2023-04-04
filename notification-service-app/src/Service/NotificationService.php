<?php

namespace App\Service;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private $entityManager;
    private NotificationRepository $notificationRepository;

    public function __construct(EntityManagerInterface $entityManager, NotificationRepository $notificationRepository)
    {
        $this->entityManager = $entityManager;
        $this->notificationRepository = $notificationRepository;
    }

    public function createNotification(array $data): Notification
    {
        if (!isset($data['text']) || empty(trim($data['text']))) {
            throw new \InvalidArgumentException('Notification text cannot be empty');
        }

        if (!isset($data['recipient'])) {
            throw new \InvalidArgumentException('Notification recipient must be set');
        }

        $notification = new Notification();
        $notification->setText($data['text']);
        $notification->setType($data['type']);
        $notification->setIsRead(false);
        $notification->setRecipient($data['recipient']);

        if (isset($data['sender'])) {
            $notification->setSender($data['sender']);
        }

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    public function getNotificationsByRecipientEmail(string $email): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(sprintf('Invalid email address: %s', $email));
        }

        $notifications = $this->notificationRepository->findBy([
            'recipient' => $email
        ]);

        if (empty($notifications)) {
            return [
                'success' => false,
                'message' => sprintf('No notifications found for email: %s', $email),
            ];
        }

        return $notifications;
    }
}
