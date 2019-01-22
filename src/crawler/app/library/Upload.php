<?php namespace library;
use system\Dispatcher;
/**
 * 
 * @author zhanghang
 *
 */
class Upload extends Dispatcher {
    public $file = null;
    public $error;
    public $filetype;
    /**
     * 
     */
    public function __construct() {
        $this->file = current($_FILES);
        
        if (!empty($this->file['error']))
            return false;
    }
    
    /**
     * 
     */
    protected function check(string $type, int $maxsize) {
        $fileinfo = explode('/', $this->file['type']);
        
        //上传文件类型限制
        if (!isset($fileinfo[1]) || $fileinfo[0] != $type) {
            $this->error = '上传图片不支持该格式';
            return false;
        }
        
        //文件大小限制
        if ($this->file['size'] > $maxsize) {
            $this->error = '上传图片不能超过1M';
            return false;
        }
        
        $this->filetype = $fileinfo[1];
    }
    
    /**
     * 
     * @return number
     */
    protected function move(string $des) {
        $src = $this->file['tmp_name'];
        $file = $des.'.'.$this->filetype;
        if (move_uploaded_file($src, $file)) {
        }
        
    }
}