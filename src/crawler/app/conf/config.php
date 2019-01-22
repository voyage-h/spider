<?php 
/**
 * 所有该目录下的配置文件都会被加载为config数组，键名为文件名
 * 
 */
return [
    /*
     |--------------------------------------------------------------------------
     | Application Debug Mode
     |--------------------------------------------------------------------------
     |
     | When your application is in debug mode, detailed error messages with
     | stack traces will be shown on every error that occurs within your
     | application. If disabled, a simple generic error page is shown.
     |
     */
    'debug' => true,
    /*
     |--------------------------------------------------------------------------
     | Default Controller
     |--------------------------------------------------------------------------
     | You can set other controller as default 
     |
     */
    'default_controller' => 'IndexController',
    /*
     |--------------------------------------------------------------------------
     | Default Aciton
     |--------------------------------------------------------------------------
     | You can set other action as default
     |
     */
    'default_action' => 'index',
    /*
     |--------------------------------------------------------------------------
     | Default View Layout
     |--------------------------------------------------------------------------
     | You can set views 
     |
     */
    'default_layout' => 'base',
    /*
     |--------------------------------------------------------------------------
     | Login by
     |--------------------------------------------------------------------------
     | 'cookie','file','db'
     |
     */
    'login' => 'db',
    /*
     |--------------------------------------------------------------------------
     | Cache driver
     |--------------------------------------------------------------------------
     | 'file','session','db'
     |
     */
    'cache' => 'file',
    /*
     |--------------------------------------------------------------------------
     | Log
     |--------------------------------------------------------------------------
     |
     */
    'log' => [
        'driver' => 'file',
        'path' => '/workspace/logs/',//nessesary when driver is file
        'table' => 'log'//nessesary when driver is db
    ],
];
