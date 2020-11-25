<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

define('HOST', '127.0.0.1');
define('PORT', 5672);
define('USER', 'guest');
define('PASS', 'guest');
define('VHOST', '/');
define('QUEUE_REQUEST', 'request');
date_default_timezone_set("Asia/Kolkata");

function dd($msg)
{
    echo "<pre>";
    print_r($msg);
    echo "</pre>";
}

function RMQConnection()
{
    $connection = NULL;
    try {
        $connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
    } catch (PhpAmqpLib\Exception\AMQPProtocolConnectionException $e) {
        die("Unable to connect with gateway.");
    } catch (PhpAmqpLib\Exception\AMQPTimeoutException $e) {
        die("Time out while connecting to gateway.");
    } catch (Exception $e) {
        die("Some error while connecting with gateway.");
    }
    if ($connection) {
        return $connection;
    }
}

function RMQChannel($connection)
{
    if ($connection) {
        $channel = $connection->channel();
        $channel->queue_declare(QUEUE_REQUEST, FALSE, FALSE, FALSE, FALSE);
        return $channel;
    }
}

function RMQMessage($queueResponse)
{
    $msg = ['Hi amit', 'How are you', 'Request send to server at ' . date('Y-m-d H:i:s')];
    $data = json_encode(['type' => $queueResponse, 'message' => $msg]);
    $message = new AMQPMessage();
    $message->body = $data;
    $message->body_size = strlen($data);
    return $message;
}

$callback = function ($message) {
    global $channel;

    $requestBody = json_decode($message->body);
    $queueResponse = $requestBody->type;

    $requestBody->message[] = 'Response from server at ' . date('Y-m-d H:i:s');
    $jsonMsg = json_encode($requestBody->message);

    $messageR = new AMQPMessage();
    $messageR->body = $jsonMsg;
    $messageR->body_size = strlen($jsonMsg);

    $channel->basic_publish($messageR, NULL, $queueResponse);
};