<?php
/*
 * This file run as client side
 * */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

$queueResponse = 'response.login_' . uniqid();
$timeStart = microtime(TRUE);
$requestTimeOut = 5;
$message = RMQMessage($queueResponse);

$connection = RMQConnection();
if($connection) {
    $channel = RMQChannel($connection);
    $channel->queue_declare($queueResponse, FALSE, FALSE, FALSE, TRUE);
    $channel->basic_publish($message, '', QUEUE_REQUEST);

    do {
        $responseMessage = $channel->basic_get($queueResponse);
        $timeSpent = microtime(TRUE) - $timeStart;
    } while ($responseMessage == NULL && $timeSpent < $requestTimeOut);

    if ($responseMessage) {
        dd($responseMessage->body);
        $channel->queue_delete($queueResponse);
    }

    $channel->close();
    $connection->close();
}