<?php 

namespace library;

use system\Dispatcher;
use system\Config;

/**
 * 
 * @author zhanghang
 *
 */
class Elog extends Dispatcher 
{
    private $driver;
    private $table;
    private $path;
    private $suffix = 'log';
    public $prefix;
    private $time;
    
    /**
     * 只在实例化的时候执行一次
     * 
     */
    public function __construct() 
    {
        $conf = Config::get('log');
        $this->driver = $conf['driver'];
        switch ($conf['driver']) {
            case 'db':
                $this->table = $conf['table'];
                break;
            case 'file':
                $this->path = $conf['path'];
                break;
        }
    }
    
    /**
     * 每次都会执行初始化操作
     * 
     */
    protected function init() 
    {
        $now = time();
        $this->time = date('Y-m-d H:i:s', $now);
        $this->prefix = '-'.date('Y-m-d', $now);
    }
    //time [info] [process] [desc]
    protected function info(string $filename, string $data) 
    {
        return $this->write($filename, $this->time.' [info] '.$data.PHP_EOL);
    }
    /**
     * warn
     *
     */
    protected function warn(string $filename, string $data) 
    {
        return $this->write($filename, $this->time.' [warn] '.$data.PHP_EOL);
    }

    /**
     * error
     *
     */
    protected function error(string $filename, string $data) 
    {
        return $this->write($filename, $this->time.' [error] '.$data.PHP_EOL);
    }    

    /**
     * wirte to file or redis
     *
     */
    private function write(string $filename, string $data) 
    {
        $filename = $filename.$this->prefix;
        switch ($this->driver) {
            case 'db':break;
            case 'file':default:
                $file = rtrim($this->path,'/').'/'.$filename.'.'.$this->suffix;
                $res = file_put_contents($file, print_r($data,true), FILE_APPEND);
        }
    }
    
}
