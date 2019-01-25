<?php 

namespace system;

defined('BASEPATH') || exit('no access to this file, using index.php instead');
defined('CONFPATH') || define('CONFPATH', BASEPATH.'/app/conf/');

/**
 * 加载类，所有需要手动require或自动导入类
 * 均由该类提供特定方法操作
 * 
 * @author zhanghang
 *
 */
class Loader extends Dispatcher 
{
    /**
     * 手动导入配置
     * 
     */
    protected function config() 
    {
        $files = scandir(CONFPATH);
        $conf = [];
        foreach ($files as $file) {
            if ($file == 'config.php') {
                $conf += require CONFPATH.'config.php';
            } else {
                if (substr($file, -4) == '.php') {
                    $key = explode('.', $file)[0];
                    $conf[$key] = require CONFPATH.$file;
                }    
            }
        }
        return $conf;
    }
    
}
