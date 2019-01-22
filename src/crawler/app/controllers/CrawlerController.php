<?php 

namespace controllers;

use system\Controller;
use system\Rds;
use system\Config;
use library\Spider;
use library\Elog;
use library\Queue;
use library\Download;
use library\Producer;

class CrawlerController extends Controller 
{
    public $conf;

    public function __construct()
    {
        $this->conf = Config::get('toml');
    }
    /**
     * 启动爬虫
     * @param string $url
     * @return string
     */
    public function postStart() 
    {
        //prepare
        $post = $this->request->post;

        $urls = $post['url'];

        is_array($urls) or $urls = json_decode($urls, true);

        foreach($urls as $url) {
            $url = trim($url);
            substr($url,0,4) == 'http' or $url = 'http://'.$url;
    	    $domain  = parse_url($url)['host'];
    
        	$timeout = $this->conf['image']['expiretime'];
            $maxcount = $this->conf['image']['maxcount'];
    
            //set crawler expire time
            Rds::set(SPIDER_MAX_COUNT.$domain, $maxcount, $timeout);    

            Elog::info('spider',"Start spider : $url");
        }
        return Producer::start($urls);
    }
    /**
     * 爬虫处理
     * 回传url到producer
     *
     */
    public function postIndex() 
    {
        $url = trim($this->request->post['url']);
        $default_url = $url;
        $domain = parse_url($url)['host'];
        $left = Rds::get(SPIDER_MAX_COUNT.$domain);
        if (empty($left) || $left < 1) {
            Elog::info('spider', 
                (1 === Rds::hasKey(SPIDER_MAX_COUNT.$domain)) ? 
                "Image download enough: $domain" : 
                "Spider timeout: $domain");
            return;
        }
        /**
         * 爬虫处理
         * 
         */
        $result = Spider::start($url);
        if (empty($result)) {
            Elog::info('spider',"Nothing get from: $url");
            return;
        }
        /**
         * 产生的url放入kafka producer
         * 
         */
        if (!empty($result['url']) && is_array($result['url'])) {
            foreach($result['url'] as $key => $url_one){
                if (Rds::sismember(SPIDER_URL_CRAWLERD.$domain, $url_one)) {
                    Elog::info('spider',"Url already crawlerd: $url_one");
                } else {
                    Rds::sadd(SPIDER_URL_CRAWLERD.$domain, $url_one);
                    Producer::start($url_one);
                    Elog::info('spider',"Send to producer: $url_one");
                }
            }
        }
        /**
         * 处理爬取的图片
         * 
         */
        if (isset($result['image'])) {
            $title = empty($result['title']) ? 
                current($result['str']) : $result['title'];

            if (empty($title))
                return;
            
            foreach($result["image"] as $key => $img) {
                if(Rds::sismember(SPIDER_IMAGE_CRAWLERD.$domain, $img['url'])){
                    Elog::info('spider',"Image already crawlerd: ".$img['url']);
                } else {
		            Rds::sadd(SPIDER_IMAGE_CRAWLERD.$domain, $img['url']);
                    $img['url'] = trim(current(explode('?',trim($img['url']))));

                    //download
                    $download_conf = $this->conf['download'];

                    $local_path = '/workspace/images'.
                        ($download_conf['domainfolder'] ? "/$domain" : '').
                        ($download_conf['titlefolder'] ? "/$title" : '');

                    $filename = $download_conf['filename'] == 'title' ? $title : null;
		            $content = Download::local($img['url'], $local_path, $filename);

                    if ($content['status'] != 0) {
                        Elog::warn('spider',$content['info'].': '.$img['url']);
                        continue;
                    }
                    //总体计数
                    Rds::incr(SPIDER_TOTAL_COUNT);
                    Rds::decr(SPIDER_MAX_COUNT.$domain);
                }
            }            
        }
    }
    /**
     * 删除url缓存
     *
     */
    public function postClear()
    {
        $domain = trim($this->request->post['domain']);
        Rds::remove(SPIDER_URL_CRAWLERD.$domain); 
    }
    /**
     * 删除url和image缓存
     *
     */
    public function postTruncate()
    {
        $domain = $this->request->post['domain'];

        Rds::remove(SPIDER_URL_CRAWLERD.$domain);
        Rds::remove(SPIDER_IMAGE_CRAWLERD.$domain);
        Queue::remove($domain);
    }
}
