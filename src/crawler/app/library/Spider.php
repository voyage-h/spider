<?php 

namespace library;

use system\Dispatcher;
use system\Curl;
use system\Config;

/**
 * 爬虫
 *
 */
class Spider extends Dispatcher 
{
    private $domain;
    /**
     * 解析url
     * 
     * @param string $this_url
     * @return Ambigous <string, unknown, multitype:Ambigous <unknown, string> >
     */
    protected function start(string $this_url, $cookie = null) 
    {
        $this_url = trim($this_url);
	    $http = Curl::get($this_url)->setTimeOut(Config::get('toml.url.timeout'));

	    if (!empty($cookie)) {
            $http = $http->withFollowlocation()
                ->withHeader(false)
                ->withSslVerify(false)
                ->setReferer($this_url)
                ->setCookies($cookie);
	    }
        $file = $http->exec();
        $info = curl_getinfo($http->ch);

        if (200 != $info['http_code']) {
            Elog::warn('spider',"HTTP request timeout: $this_url");
            return $info;
        }
        $tempu = parse_url($this_url);
        $domain = $tempu['host'] ?? $tempu['path'];

        //encode
        $file = $this->encoding($file,'UTF-8');
	    $wtitle = preg_match("/<title>(.*)<\/title>/isU",$file,$temp) ? $temp[1]:"";
        $wtitle = $this->getTitle($wtitle);

	    libxml_use_internal_errors(true);
	    $dom = new \DOMDocument();
        $dom->loadHTML($file);
        $xpath = new \DOMXPath($dom);
        $imgs = $xpath->query('//img');
        for ($i = 0; $i < $imgs->length; $i++) {
            $href = $imgs->item($i);
            $url = $href->getAttribute('src');
            if($href->getAttribute("data-bigimg") != null){
                $url = $href->getAttribute('data-bigimg');
            }
            $width = $href->getAttribute('width');
            $height = $href->getAttribute('height');

            $alt = $href->getAttribute('alt');
            // 保留以http开头的链接
            if(($width < 200 && $width > 0) || 
                ( $height > 0 &&  $height < 200)){
                continue;
            }
            $sign = md5($url);
            $result['image'][$sign]['url'] = substr($url, 0, 4) == 'http' ? $url : $this->formaturl($this_url, $url);
            $result['image'][$sign]['alt'] = $alt;
        }
        $entries = $xpath->query('//h1');
        foreach ($entries as $entry) {
            $str =  $entry->nodeValue;
            $result["str"][md5(trim($str))] = trim($str);
        }
        $entries = $xpath->query('(//h3|//h2|//div|//span|//p|//td|//*/self::header)/text()|//meta[@name="keywords" or @name="description"]/@content');
        foreach ($entries as $entry) {
            $str =  $entry->nodeValue;
            $len = strlen($str);
            if(empty($str)){
                continue;
            }
            $count_reduce = $this->substr_count_array($str,["	"," ","|","、",";","【","】","<",">","//",
                "论坛","下一页","公网安备","温馨提示","点击图片","热图推荐","更新时间","http","html","手机访问"
                ,"ICP备","未经授权","举报邮箱",$domain]);
            $count_add = $this->substr_count_array($str,["，","。","！","？"]);
            $len = $len - ($count_reduce * 20) + ($count_add * 50);
            if($len < 20){
                continue;
            }
            if($count_add < 2){
                //continue;
            }
            $result["str"][md5($str)] = trim($str);
        }
    
        $result['title'] = $wtitle;
        $hrefs = $xpath->query('/html/body//a');
        for ($i = 0; $i < $hrefs->length; $i++) {
            $href = $hrefs->item($i);
            $url = $href->getAttribute('href');
            // 保留以http开头的链接
            if($url == $this_url) continue;
            if(substr($url, 0, 4) == 'http'){
                $tempu=parse_url($url);
                $one_domain=$tempu['host'];
    
                if($one_domain == $domain){
                    $result["url"][md5($url)] = $url;
                }
            }else{
                $url = $this->formaturl($this_url,$url); //$tempu['scheme']."://".$domain.$url;
                $result["url"][md5($url)] = $url;
            }
    
        }
        return $result;
    }
    
    
    /**
     * 
     * 
     * @param unknown $url
     * @param unknown $str
     * @return multitype:Ambigous <string, unknown, multitype:Ambigous <unknown, string> > |unknown|string
     */
    private function formaturl($url, $str){
        if (is_array($str)) {
            $return = array();
            foreach ($str as $href) {
                $return[] = $this->formaturl($url, $href);
            }
            return $return;
        } else {
            if (stripos($str, 'http://')===0 || stripos($str, 'https://')===0) {
                return $str;
            }
            
            $str = str_replace('\\\\', '/', $str);
            $parseUrl = parse_url($url);

            $scheme = isset($parseUrl['scheme']) ? $parseUrl['scheme'] : 'http';
            $host = isset($parseUrl['host']) ? $parseUrl['host'] : $parseUrl['path'];
            $path = isset($parseUrl['path']) ? $parseUrl['path'] : '';
            if (strpos($str, '//')===0) {
                return $scheme.':'.$host.$str;
            }elseif (strpos($str, '/')===0) {
                return $scheme.'://'.$host.$str;
            } else {
                $part = explode('/', $path);
                array_shift($part);
                $count = substr_count($str, '../');
                if ($count>0) {
                    for ($i=0; $i<=$count; $i++) {
                        array_pop($part);
                    }
                }
                $path = implode('/', $part);
                $str = str_replace(array('../','./'), '', $str);
                $path = $path=='' ? '/' : '/'.trim($path,'/').'/';
                //return $scheme.'://'.$host.$path.$str;
                return $scheme.'://'.$host."/".$str;
            }
        }
    
    }
    
    
    /**
     * 
     * 
     * @param unknown $haystack
     * @param unknown $needle
     * @return number
     */
    private function substr_count_array( $haystack, $needle ) {
        $count = 0;
        foreach ($needle as $substring) {
            $count += substr_count( $haystack, $substring);
        }
        return $count;
    }

    private function encoding( $data, $to ) {
        $default_charset = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i",$data,$temp) ? strtolower($temp[1]) : "";
        if(!empty($default_charset)) {
            return $data;
        }
        $data = preg_replace("/<head>/","<head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>",$data);
        //$data = iconv($default_charset,"utf-8//IGNORE",$data);
        $encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5','JIS','eucjp-win','sjis-win','EUC-JP');
        $encoded = mb_detect_encoding($data, $encode_arr);
        if(strtolower($encode) != strtolower($to)) {
            $data = mb_convert_encoding($data,$to,$encoded);
        }
        return $data;
    }

    private function getTitle($title) {
        //是否乱码
        $title = false === json_encode($title) ? trim(iconv('gb2312','utf8',$title)) : trim($title);
        //过滤-,_
        $pattern = [
            '/\[.*\]/',
            '/_.*/',
            '/-.*/',
            '/\(.*\)/',
            '/\（.*\（/',
            '/\./',
        ];
        $t = trim(preg_replace($pattern,'',$title));
        return $t;
    }
    protected function getAlt($alt) {
        return $this->getTitle($alt);    
    }
}
