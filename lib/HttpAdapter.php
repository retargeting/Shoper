<?php
//namespace Retargeting\Lib;

class HttpAdapter {
	
	protected $http = null;

	protected $timeout = 30;

	protected $config = array();

	protected $tokens = array();

	public function __construct($config, $tokens = array()) {

		$this->config = $config;

		$this->tokens = $tokens;
		
		return true;
	}

	public function getAccessToken($shopUrl, $data) {

		$url = $shopUrl.'/webapi/rest/oauth/token?grant_type=authorization_code';

		$fields = array(
			'code' => $data
		);
		
		return $this->sendRequest($url, $fields);
	}

	public function getUser($shopUrl, $id) {

		$url = $shopUrl.'/webapi/rest/users/'.$id;
		
		return $this->sendBearerRequest($url);
	}

	public function getCategory($shopUrl, $id) {

		$url = $shopUrl.'/webapi/rest/categories/'.$id;
		
		return $this->sendBearerRequest($url);
	}

	public function getCategoryTree($shopUrl) {

		$url = $shopUrl.'/webapi/rest/categories-tree';
		
		return $this->sendBearerRequest($url);
	}

	public function getBrand($shopUrl, $id) {

		$url = $shopUrl.'/webapi/rest/producers/'.$id;
		
		return $this->sendBearerRequest($url);
	}

	public function getProduct($shopUrl, $id) {

		$url = $shopUrl.'/webapi/rest/products/'.$id;
		
		return $this->sendBearerRequest($url);
	}

	public function getOrder($shopUrl, $id) {

		$url = $shopUrl.'/webapi/rest/orders/'.$id;
		
		return $this->sendBearerRequest($url);
	}

	private function sendRequest($url, $data) {
		
		$res = false;

		$fields = $data;
		$fields_string = '';

		foreach($fields as $key => $value) { 
			$fields_string .= $key.'='.$value.'&'; 
		}
		rtrim($fields_string, '&');

		try {
			
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Basic " . base64_encode($this->config['appId'] . ":" . $this->config['appSecret'])));
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
			curl_setopt($ch, CURLOPT_POST, count($fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			$res = curl_exec($ch);
			
			if (false === $res)
				throw new Exception(curl_error($ch), curl_errno($ch));

			curl_close($ch);
		} catch (Exception $e) {

			trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
		}
			
		return json_decode($res);

    }

	private function sendBearerRequest($url) {
		
		if (empty($this->tokens['access_token'])) return false;

		try {
			
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $this->tokens['access_token']));
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			$res = curl_exec($ch);
			
			if (false === $res)
				throw new Exception(curl_error($ch), curl_errno($ch));

			curl_close($ch);

		} catch (Exception $e) {

			trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
		}

		return json_decode($res);

	}

}