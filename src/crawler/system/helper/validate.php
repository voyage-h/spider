<?php namespace helper;
use system\Dispatcher;

class Validate extends Dispatcher {
    
    protected function float($data,int $decimal = 2) {
        if (is_numeric($data)) {
            return round($data,$decimal);
        }
        return false;
    }
    
    protected function string($data) {
        return is_string($data) ? true : false;
    }
    
    protected function formatNum($num) {
        dd($num);
    }
    
    
}