<?php
//namespace Retargeting\Lib;

require_once 'DataBaseConnection.php';

class HttpAdapter {
	
	protected $http = null;

	protected $timeout = 30;

	protected $config = array();

	protected $tokens = array();

	protected $db = false;

	public function __construct($config, $tokens = array()) {

		$this->config = $config;

		$this->tokens = $tokens;
		
		return true;
	}

	public function checkIfTokenExpired($shopUrl, $response) {

		if (isset($response->error) && $response->error == 'unauthorized_client'
			&& isset($response->error_description) && $response->error_description == 'Provided access token has expired') {

			$tokens = $this->refreshAccessToken($shopUrl);

			if (empty($tokens->access_token)) {
				echo '/* Info: Please reinstall the Retargeting App as the tokens have expired. */';

				return 'tokens_expired';
			} else {

				// DB lazy init
				$this->db = new DBConn($this->config['db']['host'], $this->config['db']['user'], $this->config['db']['pass'], $this->config['db']['db']);
				
				$shopId = $this->db->getShopIdByUrl($shopUrl);
			
				$this->db->saveTokens($shopId, $tokens);

				// reinit Shop Tokens
				$this->tokens = $this->db->getShopTokens($shopId);
			}

			return true;
		}

		return false;
	}

	public function getAccessToken($shopUrl, $data) {

		$url = $shopUrl.'/webapi/rest/oauth/token?grant_type=authorization_code';

		$fields = array(
			'code' => $data
		);
		
		return $this->sendRequest($url, $fields);
	}

	public function refreshAccessToken($shopUrl) {

		$url = $shopUrl.'/webapi/rest/oauth/token?grant_type=refresh_token';

		$fields = array(
			'code' => $this->tokens['refresh_token']
		);

		return $this->sendRequest($url, $fields);
	}

	public function getUser($shopUrl, $id) {

		$url = $shopUrl.'/webapi/rest/users/'.$id;
		
		$response = $this->sendBearerRequest($url);

		$check = $this->checkIfTokenExpired($shopUrl, $response);
		if ($check == 'tokens_expired')
			return false;
		if ($check)
			$response = $this->sendBearerRequest($url);
		
		return $response;
	}

	public function getCategory($shopUrl, $id) {

		$url = $shopUrl.'/webapi/rest/categories/'.$id;
		
		$response = $this->sendBearerRequest($url);

		$check = $this->checkIfTokenExpired($shopUrl, $response);
		if ($check == 'tokens_expired')
			return false;
		if ($check)
			$response = $this->sendBearerRequest($url);

		return $response;
	}

	public function getCategoryTree($shopUrl) {

		$url = $shopUrl.'/webapi/rest/categories-tree';
		
		$response = $this->sendBearerRequest($url);

		$check = $this->checkIfTokenExpired($shopUrl, $response);
		if ($check == 'tokens_expired')
			return false;
		if ($check)
			$response = $this->sendBearerRequest($url);
		
		return $response;
	}

	public function getBrand($shopUrl, $id) {

		$url = $shopUrl.'/webapi/rest/producers/'.$id;
		
		$response = $this->sendBearerRequest($url);

		$check = $this->checkIfTokenExpired($shopUrl, $response);
		if ($check == 'tokens_expired')
			return false;
		if ($check)
			$response = $this->sendBearerRequest($url);
		
		return $response;
	}

	public function getProduct($shopUrl, $id) {

		$url = $shopUrl.'/webapi/rest/products/'.$id;
		
		$response = $this->sendBearerRequest($url);

		$check = $this->checkIfTokenExpired($shopUrl, $response);
		if ($check == 'tokens_expired')
			return false;
		if ($check)
			$response = $this->sendBearerRequest($url);
		
		return $response;
	}

	public function getProducts($shopUrl, $page) {

		$url = $shopUrl.'/webapi/rest/products?page='.$page;

		$response = $this->sendBearerRequest($url);

		$check = $this->checkIfTokenExpired($shopUrl, $response);
		if ($check == 'tokens_expired')
			return false;
		if ($check)
			$response = $this->sendBearerRequest($url);
		
		return $response;
	}

	public function getOrder($shopUrl, $id) {

		$url = $shopUrl.'/webapi/rest/orders/'.$id;
		
		$response = $this->sendBearerRequest($url);

		$check = $this->checkIfTokenExpired($shopUrl, $response);
		if ($check == 'tokens_expired')
			return false;
		if ($check)
			$response = $this->sendBearerRequest($url);
		
		return $response;
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