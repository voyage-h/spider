<?php namespace exception;
use system\Config;
use system\View;
use system\Dispatcher;
/**
 * 
 * 
 * @author zhanghang
 *
 */
class Error extends Dispatcher {
    private $error;
    
    public function init() {
        if (!Config::get('debug')) {
            exit('Internal Error');
        }
        $message = func_get_args()[0];
        $this->error = new \Exception($message);
    }

    public function __call($method,$args) {
        $this->display($method);
        if ('fatal' == $method) {
            exit;
        }
    }
    
    private function display($type) {
        $trace = $this->error->getTraceAsString();
        $trace_arr = explode('#', $trace);
        View::render(BASEPATH.'/system/error/html.php',['message'=>strtoupper($type)." : ".$this->error->getMessage(),'trace'=>$trace_arr]);
    }
}