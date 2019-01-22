<?php namespace system;
session_start();
class Session extends Dispatcher {
    private $data;
    private $driver;
    /**
     * 
     * @param string $key
     * @return Ambigous <boolean, unknown>
     */
    protected function get(string $key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
    }
    
    /**
     * 
     * @param string $key
     * @param mixed $data
     * @return boolean
     */
    protected function set(string $key, $data) {
        $_SESSION[$key] = $data;
        return $this->has($key);
    }
    
    /**
     * 
     * @param string $key
     * @return boolean
     */
    protected function has(string $key) {
        return isset($_SESSION[$key]) ? true : false;
    }
    
    protected function remove(string $key) {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }
    
    protected function removeAll() {
        foreach ($_SESSION as $key => $s) {
            unset($_SESSION[$key]);
        }
    }
}