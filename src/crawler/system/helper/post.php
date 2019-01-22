<?php  namespace helper;
use system\Dispatcher;
class Post extends Dispatcher {
    
    protected function filter() {
        $post = $this->stripslashes_array($_POST);
        unset($_POST);
        return $post;
    }
    
    private function stripslashes_array(&$array) {
        while(list($key,$var) = each($array)) {
            if ($key != 'argc' && $key != 'argv' && (strtoupper($key) != $key || ''.intval($key) == "$key")) {
                if (is_string($var)) {
                    $array[$key] = stripslashes($var);
                }
                if (is_array($var)) {
                    $array[$key] = $this->stripslashes_array($var);
                }
            }
        }
        return $array;
    }
}
