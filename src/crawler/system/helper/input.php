<?php namespace helper;
use system\Dispatcher;
class Input extends Dispatcher{
    public $data;
    public $key;
    
    public function init() {
        $this->key = md5('login');
        $this->data = isset($_SESSION[$this->key]) ? $_SESSION[$this->key] : null;
    }
    /**
     * 持久化数据
     * @param unknown $arr
     * @return boolean
     */
    protected function flush($arr = array()) {
        if (empty($arr)) 
            return false;
        
        foreach ($arr as $key => $d) {
            $this->data[$key] = $d;
        }
        $_SESSION[$this->key] = $this->data;
        return true;
    }
    
    /**
     * 获取之前的数据
     * 
     * @param unknown $key
     * @param string $default
     * @return multitype:|string
     */
    protected function old($key,$default = null) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    
    /**
     * 获取所有数据
     * 
     * @return multitype:
     */
    protected function all(){
        return $this->data;
    }
    
    
    /**
     * 判断是否存在key
     * 
     * @param string $key
     * @return boolean
     */
    protected function has($key)
    {
        if (isset($this->data[$key]))
        {
            return true;
        }
        return false;
    }
    
    
    /**
     * 删除数据
     * @param unknown $key
     * @return boolean
     */
    protected function remove($key)
    {
        if (isset($this->data[$key]))
        {
            unset($this->data[$key]);
            return true;
        }
        return false;
    }
}