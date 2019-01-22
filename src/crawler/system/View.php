<?php namespace system;
use exception\Error;
defined('BASEPATH') || exit('no access to this file, using index.php instead');
/**
 * 
 * @author zhanghang
 *
 */
class View extends Dispatcher{
    /**
     * 输出html
     * 
     * @param string $folder
     * @param string $filename
     * @param array $data
     * @param string $layout
     * @throws Error
     * @return string
     */
    protected function display(string $folder, string $filename, array $data = [], string $layout) {
        $path = VIEW_PATH.$folder;
        if (!file_exists($path)) {
            mkdir($path);
        }
        $file = $path.'/'.$filename.'.enz.php';

        if (!file_exists($file)) {
            throw new Error("File doesn't exist : $file");
        }
        if (!empty($data)){
            extract($data);
        }
        ob_start();
        require $file;
        $content = ob_get_clean();
        //加载layout
        if ($layout) {
            $layout = Config::get('default_layout');
            if (!empty($layout)) {
                $layout_file = VIEW_PATH.'/layout/'.$layout.'.enz.php';
                if (!file_exists($layout_file)) {
                    throw new Error();
                }
                ob_start();
                require $layout_file;
                $content = ob_get_clean();
            }
        }
        //debug
        if (true === Config::get('debug') && $layout) {
            return $content.$this->debug();
        }
        return $content;
    }
    
    protected function debug() {
        $sql_str = '';
        if (isset($this->request->getSqls)) {
            $sqls = $this->request->getSqls;
            $sort = ['Fisrt','Second','Third'];
            foreach ($sqls as $k => $sql) {
                $sql_str .= "<div class='debug-row'><div class='debug-header'>".(isset($sort[$k]) ? $sort[$k] : '...')." Query</div><div class='debug-body'>$sql</div></div>";
            }
	} else {
	    $sqls = [];	
            $sql_str = "<div class='debug-row'><div class='debug-header'>No query</div></div>";
	}
	$count = count($sqls);
        $dom = "<div class='debug'><div>Total sqls: $count</div>$sql_str</div>";
        return $dom.$js;
    }
    /**
     * 
     * @param string $errorfile
     * @param array $data
     */
    protected function render(string $errorfile, array $data = []) {
        if (!empty($data)) {
            extract($data);
        }
        require $errorfile;
    }
}
