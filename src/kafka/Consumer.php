<?php

declare(strict_types=1);

require './vendor/autoload.php';

use Kafka\Consumer;
use Kafka\ConsumerConfig;
date_default_timezone_set('PRC');

$config = ConsumerConfig::getInstance();
$config->setMetadataRefreshIntervalMs(20000);
$config->setMetadataBrokerList("kafka:9092");
$config->setGroupId('spider');
$config->setBrokerVersion('1.0.0');

//
//$topics = file('.topic');
//foreach($topics as $key => $topic) {
//    $topics[$key] = trim($topic);
//}

$config->setTopics(array_slice($argv, 1));
$config->setOffsetReset('earliest');

$consumer = new Consumer();

$consumer->start(function ($topic, $part, $message): void {
    $url = $message['message']['value'];

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, 'crawler/crawler');
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, ['url'=>$url]);
    $data = curl_exec($curl);
    curl_close($curl);
});
