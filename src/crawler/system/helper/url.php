<?php namespace helper;
use system\Dispatcher;

class Url extends Dispatcher {
    
    protected function setParam($url, $key, $value) {
        //没有参数
        if (strpos($url, '?') === false) {
            return $url."?$key=$value";    
        //有参数
        } else {
            //有{$key}参数
            $params = $this->request->getParams;
            if (isset($params[$key])) {
                $url = str_replace("$key=".$params[$key], "$key=$value", $url);
            //没有{$key}参数
            } else {
                //空参数
                $url .= substr($url, -1) == '?' ? "$key=$value" : "&$key=$value";
            }
        }
        return $url;
    }
}