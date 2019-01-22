<?php  namespace library;
use system\Dispatcher;
use system\Config;
/**
 * rest客户类
 *
 */
class Queue extends Dispatcher {
    
    public static $db = 0;
    private $data = [];
    //只在实例化的时候执行 
    protected function __construct() {
        \Resque::setBackend(Config::get('db.redis.host').":".Config::get('db.redis.port'),self::$db);
    }
    
    protected function view($domain) {
        $data = \Resque::pop($domain);
        \Resque::push($domain, $data);
        return $data;
    }
    
    protected function viewAll($domain) {
        while (($item = \Resque::pop($domain)) != null) {
            $this->data[] = $item;
        }
        return $this->data;
    }
    
    protected function pop($domain) {
        return \Resque::pop($domain);
    }
    
    protected function push($domain,$data) {
        return \Resque::push($domain, $data);
    }
    
    protected function size($domain) {
        return \Resque::size($domain);
    }
    
    protected function items() {
        return \Resque::queues();
    }

    protected function remove($domain) {
    	return \Resque::removeQueue($domain);
    }
}
