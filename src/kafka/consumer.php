<?php

declare(strict_types=1);

require './vendor/autoload.php';

use Kafka\Consumer;
use Kafka\ConsumerConfig;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('PRC');

//topic
$topics = array_slice($argv, 1);
if (empty($topics)) {
    exit("Topic cann't be empty");
}

//log
$logger = new Logger('my_logger');
$date = date('Y-m-d', time());
$logger->pushHandler(new StreamHandler("/workspace/logs/consumer-$date.log", Logger::WARNING));

$config = ConsumerConfig::getInstance();
$config->setMetadataRefreshIntervalMs(20000);
$config->setMetadataBrokerList("kafka:9092");
$config->setGroupId('spider');
$config->setBrokerVersion('1.0.0');

$config->setTopics($topics);
$config->setOffsetReset('earliest');//latest|earliest

$consumer = new Consumer();
$consumer->setLogger($logger);

$consumer->start(function ($topic, $part, $message): void {
    $url = $message['message']['value'];

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, 'crawler/crawler');
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, ['url'=>$url]);
    $data = curl_exec($curl);
    curl_close($curl);
});
