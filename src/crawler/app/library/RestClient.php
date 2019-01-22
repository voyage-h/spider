<?php
/**
 * rest客户类
 *
 */
class RestClient {
	private static $configDict = array ();
	private $module;
	private $config;
	
	/**
	 * 生成伪对象
	 * 
	 * @param string $module        	
	 * @param string $restService        	
	 * @return RestClient
	 */
	static public function factory($module=null)
	{
		$obj         = new self ();
		if (isset($module)) {
		    $obj->module = $module;
		}
		return $obj;
	}
	
	/**
	 * 代理伪对象的方法调用
	 * 
	 * @param string $func        	
	 * @param array $args        	
	 * @return boolean Ambigous boolean, mixed>
	 */
	public function __call($func, $args) {
		$data = array (
				'clientID' => $this->config ['clientID'],
				'mod' => $this->module,
				'func' => $func,
				'args' => serialize ( $args ),
				'timestamp' => time (),
				'returnFormat' => 'json' 
		);
		$data ['sign'] = md5 ( $data ['args'] . $data ['timestamp'] . $this->config ['shareKey'] );
		
		$result = $this->request ( $data );
		if ($result === false)
			return false;
		
		if ($data ['returnFormat'] == 'json') {
			$result = json_decode ( $result, true );
			if (empty ( $result )) {
				return 'json 解码失败';
			}
			
			if ($result ['code'] > 1) {
				return '[' . $result['code'] . '] ' . $result['message'];
			}
			
			$result = $result ['data'];
		}
		
		return $result;
	}
	
	/**
	 * http请求
	 * 
	 * @param array $data        	
	 * @return boolean mixed
	 */
	private function request($data) {
		$ch = curl_init ();
		
		curl_setopt ( $ch, CURLOPT_URL, $this->config ['apiUrl'] );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_FRESH_CONNECT, 1 );
		curl_setopt ( $ch, CURLOPT_FORBID_REUSE, 1 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 4 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
		
		$result = curl_exec ( $ch );

		curl_close ( $ch );
		try {
			if (empty ( $result )) {		
				throw new Exception('登录失败');
			}
		} catch (Exception $e) {
		}
		return $result;
	}
}
