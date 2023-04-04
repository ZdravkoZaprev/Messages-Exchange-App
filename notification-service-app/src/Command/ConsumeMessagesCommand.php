<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use App\Service\RabbitMQService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ConsumeMessagesCommand extends Command
{
    private RabbitMQService $rabbitMQService;
    private NotificationService $notificationService;
    private UserRepository $userRepository;

    public function __construct(
        RabbitMQService $rabbitMQService,
        NotificationService $notificationService,
        UserRepository $userRepository
    ) {
        parent::__construct();
        $this->rabbitMQService = $rabbitMQService;
        $this->notificationService = $notificationService;
        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
        $this
            ->setName('rabbitmq:consume:messages')
            ->setDescription('Consume messages from RabbitMQ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->rabbitMQService->consumeMessage(
            'notification_exchange',
            'notification_queue',
            function ($data, $routingKey) use ($output) {
                $output->writeln(sprintf('Received message: %s', json_encode($data)));
                $this->processMessage($data, $routingKey);
            }
        );

        return Command::SUCCESS;
    }

    private function processMessage($data, $routingKey)
    {
        // TODO: Set const to 'registered_users_routing'
        // Add user to database
        if ($routingKey == 'registered_users_routing') {
            $user = new User();
            $user->setEmail($data['recipient']);
            $user->setRoles(['ROLE_USER']);
            $this->userRepository->save($user, true);
        }

        // Create a notification in the database
        $this->notificationService->createNotification($data);
    }
}
