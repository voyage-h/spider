<?php namespace system;
use Predis;
class Rds extends Dispatcher {
    protected $redis;
    
    public function __construct() {
        Predis\Autoloader::register();
        $this->redis = new Predis\Client(Config::get('db.redis'));
    }
    
    public function __call($method,$args) {
        return call_user_func_array([$this->redis,$method], $args);
    }
    protected function set($key,$value,$expireTTL = null) {
        if (empty($expireTTL)) {
            return $this->redis->set($key, $value);
        }
        return $this->redis->setex($key,$expireTTL,$value);
    }
    protected function hasKey($key) {
        return $this->redis->exists($key);            
    }
    protected function remove($key) {
        $type = $this->redis->type($key);
        switch(current($type)) {
            case 'set':
                $num = $this->redis->scard($key);
                $this->redis->spop($key,$num);
            break;
            case 'list':
            break;
        }
        return true;
    }
}
