<?php namespace system;
defined('BASEPATH') || exit('no access to this file, using index.php instead');
class Cache extends Dispatcher {
    private $data;
    private $driver;
    
    /**
     * 设置缓存驱动（file,session,db）
     * 
     */
    protected function __construct() {
        $this->driver = ($driver = Config::get('cacher')) == null ? 'file' : $driver;
    }
    
    /**
     * 
     * @param string $key
     * @return boolean|mixed
     */
    protected function get($key,$default = null) {
        switch ($this->driver) {
            case 'array':
                $value = isset($this->data[$key]) ? $this->data[$key] : false;
                break;
            case 'redis':
                break;
                //file or other
            default:
                $file = CACHEDIR.md5($key);
                if (!file_exists($file)) {
                    return $default === null ? false : $default;
                }
                $value = json_decode(file_get_contents($file),true);
        }
        return $value;
    }
    
    /**
     * 
     * @param string $key
     * @return boolean
     */
    protected function has($key) {
        switch ($this->driver) {
            case 'array':
                $value = isset($this->data[$key]) ? true : false;
                break;
            case 'redis':
                break;
                //file or other
            default:
                $value = file_exists(CACHEDIR.md5($key)) ? true :false;
        }
        return $value;
    }    
    
    /**
     * 
     * @param string $key
     * @param mixed $data
     * @return number
     */
    protected function set($key,$data) {
        switch ($this->driver) {
            case 'array':
                $this->data[$key] = $data;
                break;
            case 'redis':
                break;
                //file or other
            default:
                if (!file_exists(CACHEDIR)) {
                    mkdir(CACHEDIR);
                }
                return file_put_contents(CACHEDIR.md5($key), json_encode($data));
        }
    }
    
    protected function append(string $key,$data) {
        return file_put_contents(CACHEDIR.md5($key), json_encode($data),FILE_APPEND);
    }
}