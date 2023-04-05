<?php

namespace App\Command;

use App\Entity\User;
use App\Service\RabbitMQService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MessageSendToCommand extends Command
{
    private $entityManager;
    private $rabbitMQService;

    public function __construct(EntityManagerInterface $entityManager, RabbitMQService $rabbitMQService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->rabbitMQService = $rabbitMQService;
    }

    protected function configure()
    {
        $this
            ->setName('app:message:send:to')
            ->setDescription('Sends a message to a specific user')
            ->addArgument('user_id', InputArgument::REQUIRED, 'The ID of the user to receive the message')
            ->addArgument('message', InputArgument::REQUIRED, 'The message to be sent')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('user_id');
        $message = $input->getArgument('message');

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $output->writeln(sprintf("User with ID $userId not found"));
            return Command::FAILURE;
        }

        $userEmail = $user->getEmail();

        $messageData = [
            'user_id' => $userId,
            'text' => $message,
            'type' => 'system',
            'recipient' => $userEmail,
        ];

        $routingKey = 'notifications_routing';
        $this->rabbitMQService->publishMessage('notification_exchange', $routingKey, $messageData);
        $this->rabbitMQService->close();

        $output->writeln(sprintf("Message sent to user with email: $userEmail"));

        return Command::SUCCESS;
    }
}
