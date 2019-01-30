<?php 

namespace library;

use system\Dispatcher;
use system\Curl;
use system\Config;
/**
 * 
 * @author zhanghang
 *
 */
class Download extends Dispatcher 
{
    public $conf;

    public function __construct()
    {
        $this->conf = Config::get('toml');
    }
    
    /**
     * 
     * @param string $src
     * @param string $path
     * @param string $title
     * @return multitype:unknown string number
     */
    protected function local($src, $path, $title = null) 
    {
        //文件类型
        $pathinfo = pathinfo($src);
        if (empty($pathinfo['extension'])) {
            return ['status' => 1, 'info' => 'Invalid file type'];
        }
        $ext = false === ($ix = strpos($pathinfo['extension'], '?')) ? 
            $pathinfo['extension'] : 
            substr($pathinfo['extension'], 0, $ix);

	    if (!in_array(strtolower($ext), $this->conf['image']['type'])) {
	        return ['status' => 2, 'info' => "$ext is limited"];
	    }

        //文件下载
        if (false === ($stream = Curl::get($src)
            ->setTimeOut($this->conf['image']['timeout'])->exec())) {
            return ['status' => 3 ,'info' => 'Image download timeout'];
        }

        //图片大小
        $size = strlen($stream);
        if ($size < $this->conf['image']['minsize']) {
            return ['status' => 4,'info' => 'Image size is less than '.($this->conf['image']['minsize']/1000).'k'];
        } else if ($size > $this->conf['image']['maxsize']) {
            return ['status' => 5, 'info' => 'Image size is too big'];
        }

        //创建根目录
        if (!file_exists($path)) {
            if (false === mkdir($path, 0777, true)) {
                return ['status' => 6, 'info' => "Create folder failed: $path"];
            }
        }
        //文件名称
        $filename = $title ?? md5($src);

        //文件目录
        $filepath = rtrim($path,'/')."/$filename.$ext";

        //重复文件命名
        if($title) {
            $num = 1;
            while(file_exists($filepath)) {
                $filepath = rtrim($path,'/')."/$filename-$num.$ext";
                $num++;
            }
        }

        //写文件
        if (false === file_put_contents($filepath, $stream)) {
            return ['status' => 7, 'info' => "Write stream failed : $filepath"];
        }

        return ['status' => 0, 'size' => $size];
    }
    
    protected function server($src) 
    {
        return Curl::get($src)->setProxy(Config::get('api.image.writer'))->exec();
    }
}
