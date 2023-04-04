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

    public function consumeMessage(string $exchangeName, string $queueName, callable $callback)
    {
        $this->channel->queue_declare($queueName, false, true, false, false);
        $this->channel->queue_bind($queueName, $exchangeName, $queueName);
        echo " [*] Waiting for messages with queue name '$queueName'. To exit press CTRL+C\n";
    
        $this->channel->basic_consume(
            $queueName,
            '',
            false,
            true,
            false,
            false,
            function (AMQPMessage $message) use ($callback) {
                $messageBody = json_decode($message->getBody(), true);
                $routingKey = $message->get('routing_key');
                $callback($messageBody, $routingKey);
            }
        );
    
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
