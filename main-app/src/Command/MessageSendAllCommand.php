<?php

namespace App\Command;

use App\Entity\User;
use App\Service\RabbitMQService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MessageSendAllCommand extends Command
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
            ->setName('app:message:send:all')
            ->setDescription('Sends a message to all registered users')
            ->addArgument('message', InputArgument::REQUIRED, 'The message to be sent')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = $input->getArgument('message');
        $users = $this->entityManager->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            $userEmail = $user->getEmail();

            $messageData = [
                'user_id' => $user->getId(),
                'text' => $message,
                'type' => 'system',
                'recipient' => $userEmail,
            ];

            $routingKey = 'notifications_routing';
            $this->rabbitMQService->publishMessage('notification_exchange', $routingKey, $messageData);
            $output->writeln(sprintf("Sent message to user $userEmail"));
        }
        
        $this->rabbitMQService->close();

        $output->writeln(sprintf("Message $message sent to all registered users"));

        return Command::SUCCESS;
    }
}
