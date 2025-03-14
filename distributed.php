<?php
declare(strict_types=1);

// This file shows a sample structure for scenarios such as distributed log management, asynchronous task processing, etc. with RabbitMQ or another message queue system.
// For example, you can use php-amqplib: composer require php-amqplib/php-amqplib

require_once __DIR__.'/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DistributedLogger {
    private $connection;
    private $channel;

    public function __construct() {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare('lg_logs', false, true, false, false);
    }

    public function log(string $message): void {
        $msg = new AMQPMessage($message, ['delivery_mode' => 2]);
        $this->channel->basic_publish($msg, '', 'lg_logs');
    }

    public function __destruct() {
        $this->channel->close();
        $this->connection->close();
    }
}

// Usage example:
// $logger = new DistributedLogger();
// $logger->log('New log entry from advanced looking glass.');
