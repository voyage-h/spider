<?php
use system\Router;
use system\Rds;
/**
 * 用户自定义路由，目前提供三种方式：
 * 
 * Router::get(url,controller@action) for http method 'GET'
 * 
 * Router::post(url,controller@action) for http method 'POST'
 * 
 * Router::bind(url,controller) for both http method 'GET' and 'POST'
 * 
 * 优先级由高到底：GET|POST > BIND
 * 例如：
 * Router::get('/','IndexController@getIndex');
 * 会覆盖 Router::bind('/','UserController');
 * 
 * 
 * 
 */
/**
 * 支持匿名函数
 * 
 */
