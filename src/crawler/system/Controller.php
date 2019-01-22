<?php namespace system;
class Controller extends Dispatcher {
    public $model;
    public $user;
    public $viewData = [];
    
    /**
     * 父类初始化
     * 
     */
    public function __construct() {
        $this->user = $this->request->getUser;
        if (method_exists($this, 'init')) {
            $this->init();
        }
    }
    
     public function error($message) {
        return View::display('error', 'index', ['content'=>$message], false);
    }
    
    /**
     * render file and layouts
     * 
     * @param string $file
     * @param array $data
     */
    public function render(string $file, array $data = [], bool $cache = false) {
        return $this->display($file, array_merge($data,$this->viewData), true);
    }
    
    /**
     * 
     * @param unknown $url
     */
    public function redirect($url) {
        return header('Location:'.$url);
    }
    
    /**
     * just render file
     * 
     * @param string $file
     * @param array $data
     */
    public function renderFile(string $file, array $data = []) {
        return $this->display($file, array_merge($data,$this->viewData), false);
    }
    
    /**
     * display file
     * 
     * @param string $file
     * @param arrya $data
     * @param bool $layout
     */
    private function display(string $file, array $data, bool $layout) {
        $folder = substr($this->request->getController, 0,-10);
        return View::display(strtolower($folder), $file, $data, $layout);
    }
}