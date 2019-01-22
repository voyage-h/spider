<?php 

date_default_timezone_set('PRC');
/*
 |--------------------------------------------------------------------------
 | Define constance
 |--------------------------------------------------------------------------
 |This is very important,there are several files using APP_PATH under /core.
 |
 */
defined('WDS') or define('WDS', DIRECTORY_SEPARATOR);
defined('BASEPATH') or define('BASEPATH',  realpath(dirname(__FILE__).WDS.'..'.WDS));
defined('ENV') or define('ENV','test');
//error_reporting(~E_NOTICE);
/*
 |--------------------------------------------------------------------------
 | Register The Auto Loader
 |--------------------------------------------------------------------------
 |
 | Composer provides a convenient, automatically generated class loader
 | for our application. We just need to utilize it! We'll require it
 | into the script here so that we do not have to worry about the
 | loading of any our classes "manually". Feels great to relax.
 |
 */
require BASEPATH.'/vendor/autoload.php';
require BASEPATH.'/app/Bootstrap.php';
require BASEPATH.'/system/Application.php';
/*
 |--------------------------------------------------------------------------
 | Register a new application
 |--------------------------------------------------------------------------
 |initial an application object:
 |get the required file
 |register a loader function
 |
 */
system\Application::register()->run();
