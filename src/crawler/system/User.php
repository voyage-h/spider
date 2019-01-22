<?php namespace system;
use exception\Error;
use models\User as Admin;
class User extends Dispatcher {
    public $isGuest = true;
    public $isLogin = false;
    public $info;
    private $driver;
    
    /**
     * 
     * 
     * @throws Error
     */
    public function init() {
        $this->driver = Config::get('login');
        
        if (!in_array($this->driver,['cookie','file','db'])) {
            throw new Error("Invalid driver : [ $this->driver ] in file config.php");
        }
        
        $user = Session::get('user');
        
        if ($user && isset($user['username'])) {
            $this->isGuest = false;
            $this->isLogin = true;
            $this->info = $user;
        }
        
    }
    /**
     * 
     * 
     * @return \system\User
     */
    protected function getObject() {
        return $this;
    }

    /**
     * 
     * @param array $data
     * @return boolean|multitype:number string
     */
    public function login(array $data) {
        switch ($this->driver) {
            case 'cookie':
                Session::set('user',$data);
                break;
            case 'file':
                break;
            case 'db':default:
                $res = Admin::find()->where(['username'=>$data['username']])->one();
                if ($res) {
                    if ($res['password'] == md5($data['password'])) {
                        $tmp = explode('@', $data['username']);
                        
                        $res['email'] = isset($tmp[1]) ? $data['username'] : '';
                        
                        $res['username'] = $tmp[0];
                        
                        Session::set('user',$res);
                        return true;
                    }
                    return ['status'=>0,'info'=>"Invalid Password"];
                }
                return ['status'=>0,'info'=>$data['username']." doesn't exsit"];
                break;
        }
        return false;
    }
    public function getRole() {
    
    }
    public function setInfo(string $key,$value) {
        $user = Session::get('user');
        $user[$key] = $value;
        Session::set('user',$user);
    }
    public function hasRole() {
    
    }
    
}