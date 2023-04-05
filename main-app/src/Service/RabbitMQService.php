<?php

namespace App\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    private $connection;
    private $channel;

    public function __construct(string $host, int $port, string $user, string $password)
    {
        $this->connection = new AMQPStreamConnection($host, $port, $user, $password);
        $this->channel = $this->connection->channel();
    }

    public function publishMessage(string $exchangeName, string $routingKey, array $message)
    {
        $messageBody = json_encode($message);
        $this->channel->basic_publish(new AMQPMessage($messageBody), $exchangeName, $routingKey);
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}