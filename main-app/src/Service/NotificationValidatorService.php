<?php

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotificationValidatorService
{
    private UserRepository $userRepository;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        UserRepository $userRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function validatePostMsgData($data)
    {
        if (!isset($data['to'])) {
            throw new BadRequestHttpException('Recipient ID is missing');
        }

        if (!isset($data['message'])) {
            throw new BadRequestHttpException('Message is missing');
        }

        $recipientId = $data['to'];
        $message = $data['message'];

        $userRecipient = $this->userRepository->find($recipientId);
        if (!$userRecipient) {
            throw new NotFoundHttpException("User with id $recipientId not found");
        }

        $recipient = $userRecipient->getEmail();
        $currentUser = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        $sender = $this->userRepository->find($currentUser)->getEmail();
        
        return [
            'text' => $message,
            'type' => 'user',
            'recipient' => $recipient,
            'sender' => $sender
        ];
    }
}
