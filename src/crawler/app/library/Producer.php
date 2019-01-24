<?php

namespace library;

use system\Dispatcher;
use Monolog\Logger;
use Monolog\Handler\StdoutHandler;
use library\Elog;

class Producer extends Dispatcher
{
    public $logger;
    public $topic = 'spider';
    public $key = 'spiderKey';

    /**
     * 设置日志和配置
     *
     */
    public function __construct()
    {
        $this->logger = new Logger('my_logger');
        $this->logger->pushHandler(new StdoutHandler());
        $this->setConfig();
    }
    /**
     * kafka配置
     *
     */
    private function setConfig()
    {
        $config = \Kafka\ProducerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(10000);
        $config->setMetadataBrokerList('kafka:9092');
        $config->setBrokerVersion('1.0.0');
        $config->setRequiredAck(1);
        $config->setIsAsyn(false);
        $config->setProduceInterval(500);
    }
    /**
     * 同步消息发送
     *
     *
     */
    protected function sync($data)
    {
        $producer = new \Kafka\Producer();
        //$producer->setLogger($this->logger);
        
        foreach($data as $d) {
            $producer->send([$d]);
        }
    }

    /**
     * 异步消息发送
     *
     *
     */
    protected function async($data)
    {
        $producer = new \Kafka\Producer(function() use ($data) {
            return $data;
        });
        
        $producer->setLogger($this->logger);
        
        $d = is_array($data) ? json_encode($data) : $data;
        
        $producer->error(function($errorCode) use ($d) {
            Elog::error('producer', "produce message failed($errorCode): $d");
            if ($errorCode == 1000) {
                echo "failed: the topic is not exist";
            } else {
                echo "failed with error code $errorCode";
            }
        });
        $producer->success(function($result) {
            echo 'success';
        });
        $producer->send(true);
    }

    /**
     * 启动producer
     *
     *
     */
    protected function start($urls, $sync = false) 
    {

        is_array($urls) or $urls = [$urls];

        $data = [];
        foreach($urls as $url) {
            $domain  = parse_url($url)['host'];
            if (empty($domain)) continue;

            $data[] = [
                'topic' => $domain,
                'value' => $url,
                'key'   => "$domainKey"
            ];
        }

        return $sync ? $this->sync($data) : $this->async($data);
    }
}
