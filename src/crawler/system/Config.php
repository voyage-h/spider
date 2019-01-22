<?php namespace system;
defined('BASEPATH') || exit('no access to this file, using index.php instead');
/**
 * 
 * 
 * @author zhanghang
 *
 */
class Config extends Dispatcher {
    private $conf;
    private $data;
    /**
     * 与init方法的区别在于：该方法只在第一次初始化执行。
     * 而init方法在每次方法调用前执行全局操作。
     * 
     */
    public function __construct() {
        $this->data = Loader::config();
    }
    /**
     * 
     * @param string $key
     * @return Ambigous <NULL, unknown>|NULL
     */
    protected function get($key=null) {
        if (!empty($key)) {
            if (strpos($key, '.')) {
                $conf = $this->data;
                $key_arr = explode('.', $key);
                foreach ($key_arr as $k) {
                    $conf = isset($conf[$k])?$conf[$k]:null;
                }
                return $conf;
            }
            return isset($this->data[$key]) ? $this->data[$key] : null;
        }
        return $this->data;
    }
    /**
     * 
     * @param string $key
     * @param string $data
     */
    protected function set($key,$data) {
        if (isset($this->data[$key])) {
            $this->data[$key] = array_merge($this->data[$key],$data);
        } else {
            $this->data[$key] = $data;
        }
    }
}