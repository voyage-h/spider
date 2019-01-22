<?php namespace system;
use exception\Error;
/**
 * 
 * @author zhanghang
 *
 */
class Dispatcher {
    protected static $container = array();
    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callstatic($method,$args) {
        //获取实例
        $class = get_called_class();
        if (!isset(self::$container[$class])) {
            self::$container[$class] = self::getIns();
        }
        
        //初始化操作
        if (method_exists(self::$container[$class], 'init')) {
            call_user_func_array([self::$container[$class], 'init'], $args);
        }
        //执行伪静态方法
        switch (count($args)) {
            case 0:
                return self::$container[$class]->$method();
            case 1:
                return self::$container[$class]->$method($args[0]);
            case 2:
                return self::$container[$class]->$method($args[0], $args[1]);
    
            case 3:
                return self::$container[$class]->$method($args[0], $args[1], $args[2]);
    
            case 4:
                return self::$container[$class]->$method($args[0], $args[1], $args[2], $args[3]);
    
            default:
                return call_user_func_array(array(self::$container[$class], $method), $args);
        }
    }
    /**
     * 
     * 
     * @return Dispatcher
     */
    private static function getIns() {
        $class = get_called_class();
        if (method_exists($class, 'getInstance')) {
            return call_user_func_array([$class,'getInstance'], []);
        }
        return new static();
    }

    /**
     *
     * @param string $key
     * @return string
     */
    public function __get(string $key) {
        switch ($key) {
            case 'request':
                $v = Application::register();
                break;
            case 'dbTable':
                $class = explode('\\', get_called_class())[1];
                return strtolower($class).'s';
            default :
                Error::fatal("Invalid property : $key"); 
                
        }
        return $v;
    }
}
