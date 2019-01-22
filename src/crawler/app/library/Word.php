<?php namespace library;
use system\Dispatcher;
use system\Curl;
use system\Config;
use models\Keyword;
/**
 * 
 * @author zhanghang
 *
 */
class Word extends Dispatcher {
    protected function split($data,$max = 100,$flag = true) {
        if (empty($data)) {
            return [];
        }
        if (is_array($data)) {
            $data = implode(',', $data);
        }
        $res = Curl::post(Config::get('api.wordcut'),['content' => $data])->exec();
        $seglist = explode('/', $res);
        foreach($seglist as $seg_one){
            //过滤黑名单，配置文件中
            //if (in_array($seg_one, $blacklist)) {
                //continue;                
           // }
	    if(Keyword::find()->where(['keywords'=>$seg_one,'status'=>0])->one()) {
		continue;
	    }

	    if (preg_match('/[0-9a-zA-Z]/', $seg_one)) {
                continue;
            }
            //过滤数字
            if (is_numeric($seg_one)) {
                continue;
            }
            //过滤标点符号
            if (preg_match("/\s/", $seg_one)) {
                continue;
            }
            //过滤短字符
            if (mb_strlen($seg_one,'utf-8') <= 1) {
                continue;
            }
            //出现次数
            if (!isset($re_array[$seg_one])) {
                $re_array[$seg_one] = 1;
            } else {
                $re_array[$seg_one] += 1;
            }
        }
	if(empty($re_array)) {return [];}
        //uksort($re_array,[$this,'sort']);
        //$data = array_slice($re_array, 0,$max);
	$data = $re_array;
        return $flag === false ? array_keys($data) : $data;
    }
    
    /**
     * 按照键名长度排序
     *  
     * @param string $a
     * @param string $b
     * @return number
     */
    private function sort($a,$b) {
        $len_a = mb_strlen($a,'utf-8');
        $len_b = mb_strlen($b,'utf-8');
        return ($len_a < $len_b) ? 1 : -1;
    }
}
