<?php namespace helper;
use system\Dispatcher;

class Strings extends Dispatcher {
    protected function truncate(string $str, int $len) {
        if (strlen($str) <= $len) {
            return $str;
        }
        $tmpstr = "";
        $start = 0;
        $strlen = $start + $len;
        $t = 0;
        for ($i=0;$i<$start;$i++) {
            $str = substr($str, ord($str) > 127 ? 3 : 1 );
        }
        for($i = 0; $i < $len; $i++){
            if(ord(substr($str, $t, 1)) > 127){
                $tmpstr.=substr($str, $t, 3);
                $t += 2;
            }else {
                $tmpstr.= substr($str, $t, 1);
            }
            $t++;
        }
        return strip_tags($tmpstr).'...';
    }
}