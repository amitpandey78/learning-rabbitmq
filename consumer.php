<?php
/*
 * This file run as server side
 * */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

$connection = RMQConnection();
if($connection) {
    $channel = RMQChannel($connection);
    $channel->basic_consume(QUEUE_REQUEST, NULL, FALSE, TRUE, FALSE, FALSE, $callback);

    while (count($channel->callbacks)) {
        try {
            $channel->wait();
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
echo '<meta http-equiv="refresh" content="5; URL=http://' . $url . '">';