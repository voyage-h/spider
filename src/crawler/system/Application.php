<?php namespace system;

use exception\Error;
use helper\Post;
class Application {
    
    
    /**
     * instance of the application
     * 
     * @var object
     */
    private static $instance = null;

    
    
    /**
     * 
     * @var object
     */
    private $boot;
    
    
    
    /**
     * 
     * @var array
     */
    private $request;
    
    
    
    /**
     * 
     * @var object
     */
    private $plugin;
    
    
    
    /**
     * 
     * @var boolen
     */
    private $routerstartup = false;
    
    
    
    /**
     * 
     * @var boolen
     */
    private $routershutdown = false;
    
    
    
    /**
     * 注册应用实例
     * 
     * @return Application
     */
    public static function register() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
         return self::$instance;  
    }
    
    
    
    /**
     * 私有化构造方法，防止直接new操作
     * 加载核心文件，注册自动加载器
     * 
     */
    private function __construct() {
        $this->boot = new \Bootstrap();
    }
    
    
    /**
     * startting the application
     * 
     */    
    public function run() {
        //加载用户自定义启动文件bootstrap
        $funcs = get_class_methods($this->boot);
        //注册插件需在路由之前
        foreach ($funcs as $func) {
            if(0 === strpos($func, 'init')) {
                call_user_func([$this->boot,$func]);
            }
        }
        //路由解析前，插件动作
        if ($this->routerstartup) {
            $this->plugin->routerStartup();
        }
        //路由解析
        require BASEPATH.'/app/routes.php';
        
        $routes = Router::start($_SERVER['REQUEST_URI'],$_SERVER['REQUEST_METHOD']);
        //路由解析后，插件动作
        if ($this->routershutdown){
            $this->plugin->routerShutdown();
        }
        //namespace
        $ctl = 'controllers\\'.$routes['controller'];
        
        try {
            $reflect = new \ReflectionMethod($ctl, $routes['action']);
        } catch (\Exception $e) {
            Error::fatal($e->getMessage());
        }
        $params = [];
        foreach ($reflect->getParameters() as $need) {
            if(!$need->isDefaultValueAvailable() && !isset($routes['params'][$need->name])) {
                throw new Error('action [ '.$routes['action'].' ] needs params [ $'.$need->name.' ]');
            }
            $params[$need->name] = isset($routes['params'][$need->name]) ? $routes['params'][$need->name] : ($need->isDefaultValueAvailable() ? $need->getDefaultValue() : null);
        }
        echo $reflect->invokeArgs(new $ctl, $params);
    }
    

    
    /**
     * register a plugin
     * 
     * @param object $plugin
     */
    public function plugin($plugin) {
        $this->plugin = $plugin;
        if (method_exists($plugin, 'routerStartup')) {
            $this->routerstartup = true;
        }
        if (method_exists($plugin, 'routerShutdown')) {
            $this->routershutdown = true;
        }
    }

    
    
    /**
     * 
     * @param string $key
     * @throws Error
     * @return unknown|multitype:
     */
    public function __get($key) {
        switch ($key) {
            case 'getUser':
                return User::getObject();
            case 'post':
                return Post::filter();
	    case 'isAjax':
                $v = isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) =="xmlhttprequest" ? true:false;
                return $v;
            default:
                throw new Error("Undefined params : $key");
        }
        return $this->request[$key];
    }
    
    
    
    /**
     * avoid clone the application instance
     *
     */
    public function __clone(){
        trigger_error('Clone is not allowed!',E_USER_ERROR);
    }
}
