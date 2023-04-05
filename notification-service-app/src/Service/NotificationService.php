<?php

namespace App\Service;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotificationService
{
    private $entityManager;
    private NotificationRepository $notificationRepository;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->notificationRepository = $notificationRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function getNotificationsByRecipientEmail(string $email): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(sprintf('Invalid email address: %s', $email));
        }

        $notifications = $this->notificationRepository->findBy([
            'recipient' => $email,
            'isRead' => false,
        ]);

        if (empty($notifications)) {
            return [
                'success' => false,
                'message' => sprintf('No notifications found for email: %s', $email),
            ];
        }

        return [
            'success' => true,
            'notifications' => $notifications
        ];
    }

    public function createNotification(array $data): Notification
    {
        if (!isset($data['text']) || empty(trim($data['text']))) {
            return ['success' => false, 'result' => "Notification text cannot be empty"];
        }

        if (!isset($data['recipient'])) {
            return ['success' => false, 'result' => "Notification recipient must be set"];
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

    public function setNotificationAsRead(int $id)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        $email = $user->getEmail();

        $notification = $this->notificationRepository->find($id);
        if (!$notification) {
            return ['success' => false, 'result' => "Notification with id: {$id} not found"];
        }

        if ($notification->getRecipient() !== $email) {
            return ['success' => false, 'result' => 'Not authorized to read this notification'];
        }

        if ($notification->isRead()) {
            return ['success' => false, 'result' => 'Notification already readed'];
        }

        $notification->setIsRead(1);
        $this->entityManager->flush();

        return $notification;
    }
}
