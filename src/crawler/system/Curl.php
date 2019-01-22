<?php namespace system;
/**
 * 
 * @author zhanghang
 *
 */
class Curl extends Dispatcher {
    
    public $ch;
    
    public function init() {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
    }
    /**
     * curl post
     * 
     * @param string $url
     * @param string $data
     * @return mixed
     */
    protected function post(string $url, $data) {
        curl_setopt ( $this->ch, CURLOPT_URL, $url );
        curl_setopt ( $this->ch, CURLOPT_POST, 1 );
        curl_setopt ( $this->ch, CURLOPT_POSTFIELDS, $data );
        
        return $this;
    }
    
    public function setReturnTransfer($data = 1) {
	curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, $data);
	return $this;
    }
    
    /**
     * curl get
     * 
     * @param string $url
     * @return \system\Curl
     */
    protected function get(string $url) {
        curl_setopt ( $this->ch, CURLOPT_URL, $url );
        return $this;
    }
    /**
     * set header
     * 
     * @param array $data
     * @return \system\Curl
     */
    public function setHeader(array $data) {
        curl_setopt ($this->ch,  CURLOPT_HTTPHEADER, $data);
        return $this;
    }
 
    public function setProxy($proxy) {
        curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
        return $this;
    }  
    public function setAgent($agent) {
	curl_setopt ($this->ch, CURLOPT_USERAGENT, $agent);
	return $this;
    }
    
    public function setReferer($url) {
        curl_setopt($this->ch, CURLOPT_REFERER, $url);
        return $this;
    }
    public function withFollowlocation() {
	curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
	return $this;
    }

    public function setCookies($cookie) {
	curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
	return $this;
    }

    public function withSslVerify($ssl = true) {
	curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $ssl);
	curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $ssl);
	return $this;
    }
    
    /**
     * output header
     * 
     * @return \system\Curl
     */
    public function withHeader($header = true) {
        curl_setopt ( $this->ch, CURLOPT_HEADER, $header);
        return $this;
    }
    
    
    public function setTimeOut($time) {
        curl_setopt ( $this->ch, CURLOPT_TIMEOUT, $time );
        return $this;
    }
    
    
    /**
     * exec curl method
     * 
     * @return mixed
     */
    public function exec() {
        return curl_exec ( $this->ch );
    }
    
    
    
    /**
     * close curl resource
     * 
     */
    public function __destruct() {
        curl_close($this->ch);
    }
}
