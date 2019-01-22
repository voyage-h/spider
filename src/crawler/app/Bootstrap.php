<?php 

use system\Application;
use plugins\UserCheck;

/**
 * bootstrap类中所有以init开头的方法均被依次调用
 * 
 * @author zhanghang
 * 
 *
 */
class Bootstrap 
{
    public function init_helper() 
    {
        //加载辅助函数
        require BASEPATH.'/system/helper/function.php';
    }
    /**
     * you can define some const or your own configeration.
     * 
     * 
     */
    public function init_config() 
    {
        define('VIEW_PATH', BASEPATH.WDS.'app'.WDS.'views'.WDS);
        define('CACHEDIR', BASEPATH.'/app/runtime/cache/');
        define('SCHEME',ENV == 'dev' ? 'http://172.18.100.161:10010' : '');
    }
    
    public function init_redis_key() 
    {
        define("SPIDER_URL_CRAWLERD", "spider:crawlerd:url:");
        define("SPIDER_IMAGE_CRAWLERD", "spider:crawlerd:image:");
        define("SPIDER_TOTAL_COUNT", "spider:count:total");
        define("SPIDER_MAX_COUNT", "spider:count:max:");
        define("SPIDER_LAST_URL", "spider:lasturl:");
	    define("SPIDER_COOKIE", "spider:cookie:");
    }
}
