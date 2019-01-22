<?php namespace system;

use exception\Error;
use controllers;
defined('BASEPATH') || exit('no access to this file, using index.php instead');
/**
 * 路由解析
 * 
 * @author zhanghang
 *
 */
class Router extends Dispatcher {
    private $url;
    private $args;
    private $routes;
    private $method;
    
    /**
     * 
     */
    public function init() {
        $params = func_get_args();
        if (!empty($params)) {
            $this->url = $params[0] == '/' ? '/index' : $params[0];
            $this->args = $params[1];
        }
    }
    /**
     * GET
     */
    protected function get() {
        $this->routes['GET'][$this->url] = $this->args;
    }
    /**
     * POST
     */
    protected function post() {
        $this->routes['POST'][$this->url] = $this->args;
        
    }
    /**
     * BIND
     */
    protected function bind() {
        $this->routes['BIND'][$this->url] = $this->args;
    }
    /**
     * 
     * @throws Error
     * @return multitype:string multitype:multitype:  Ambigous <string, unknown>
     */
    protected function start($url, $method) {
        if (isset($this->request->getUrl)) {
            $url = $this->request->getUrl;
        } else {
            $this->request->getUrl = $url;
        }
        $this->request->getMethod = $method;
        
        $urls = explode('?', $url);
        
        $uri = trim($urls[0],'/');
        
        $uri_tmp = explode('/', $uri);
        $r = ((empty($uri_tmp[0]) || '/' == $uri_tmp[0])?'/index':$url);
        $action = ((isset($uri_tmp[1]) && !empty($uri_tmp[1]))? ucwords($uri_tmp[1]) : Config::get('default_action'));
        $action = strtolower($method).ucfirst(explode('?', $action)[0]);
        
        /**
         * 自定义路由
         * 
         * 路由匹配顺序：绝对匹配(get|post > bind)->正则匹配(get|post > bind)
         * 
         */
        //绝对匹配
        if (isset($this->routes[$method][$r])) {
            //执行必包函数
            if (is_object($this->routes[$method][$r])) {
                $res = call_user_func($this->routes[$method][$r]);
                echo $res;
                exit();
            }
            //IndexController@getIndex
            $con_act = explode('@', $this->routes[$method][$r]);
            $controller = $con_act[0];
            $action = (isset($con_act[1]) && !empty($con_act[1]))? $con_act[1] : $action;
        } elseif (isset($this->routes['BIND'][$r])){
            $controller = $this->routes['BIND'][$r];
        } else {
            //正则匹配
            
            //默认路由
            $controller = empty($uri)? Config::get('default_controller') : ucfirst($uri_tmp[0]).'Controller';
        }
        $this->request->getController = $controller;
        //bind controller
        if (isset($this->routes['BIND'][$controller])) {
//             $controller = $this->routes['BIND'][$controller];
        }
        $controller_file = BASEPATH.'/app/controllers/'.$controller.'.php';
        $params = isset($urls[1]) ? $this->checkParams($urls[1]) : [];
        
        $this->request->getAction = $action;
        $this->request->getParams = $params;
        
        return ['controller'=>$controller,'action'=>$action,'params'=>$params];
        
    }
    /**
     * 
     * @param string $url
     * @return multitype:multitype:
     */
    private function checkParams($params) {
        $param_arr = [];
        $param_tmp = explode('&', $params);
        foreach ($param_tmp as $param) {
            if (strpos($param, '=')) {
                list($key,$value) = explode('=', $param);
                //变量名是否复合规则
                if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                    $param_arr[$key] = $value;
                }
            }
        }
        return $param_arr;
    }
}