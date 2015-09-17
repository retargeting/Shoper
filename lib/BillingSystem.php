<?php
//namespace Retargeting\Lib;

require 'DataBaseConnection.php';
require 'HttpAdapter.php';

class App {
	
	protected $config = array();

	protected $params = array();

	protected $db = false;

	protected $http = false;

	public $validRequest = false;

	public function __construct($config, $params = array()) {
        
        $this->config = $config;
        $this->params = $params;

		$this->validRequest = $this->verifySender();

		if ($this->validRequest) {
        	
        	return $this->init();

        }

        return false;
    }

    public function verifySender() {

		$params = $_POST;

		unset($params['hash']);
		ksort($params);
		
		$param_array = array();
		foreach($params as $k => $v){
			$param_array[] = $k."=".$v;
		}

		$param_string = join("&", $param_array);
		
		$hash = hash_hmac('sha512', $param_string, $this->config['appstoreSecret']);

		if ($_POST['hash'] == $hash) return true;

		return false;
    }

    public function dispatch($data = null) {
    	
    	if ($data == null) $data = $_POST;

    	if ($data['action'] == 'install') {
			
			// shop installation
			$shopId = $this->db->saveShop($data['shop'], $data['shop_url'], $data['application_version']);
			
			// get OAuth tokens
			// - stdClass Object ( [refresh_token] => 5dc3824cba8058c6be9af77ba383d186c4154556 [access_token] => 0a5c2fc245b38f35512728e08e6b46743adb926b [expires_in] => 7776000 [token_type] => bearer [scope] => )
			$tokens = $this->http->getAccessToken($data['shop_url'], $this->params['auth_code']);
			
			// store tokens
			if (!empty($tokens->access_token)) {
				
				$this->db->saveTokens($shopId, $tokens);

				return true;
			}
		}

		if ($data['action'] == 'uninstall') {

			// get shop Id
			$shopId = $this->db->getShopId($data['shop']);

			if ($shopId) {

				// delete shop's data
				$this->db->deleteShop($shopId);
				$this->db->deleteTokens($shopId);
				$this->db->deleteConfig($shopId);
				// $this->db->deleteSubscription($shopId);
				// $this->db->deleteBilling($shopId);
			}
		}

		if ($data['action'] == 'upgrade') {

			// get shop Id
			$shopId = $this->db->getShopId($data['shop']);

			if ($shopId) {

				// update shop's version
				$this->db->updateShop($shopId, $data['application_version']);
			}
		}

		if ($data['action'] == 'billing_install') {
			// not mandatory as App is free
		}
		if ($data['action'] == 'billing_subscription') {
			// not mandatory as App is free
		}

    	return false;
    }

	private function init() {

		// DB
		$this->db = new DBConn($this->config['db']['host'], $this->config['db']['user'], $this->config['db']['pass'], $this->config['db']['db']);

		// HTTP
		$this->http = new HttpAdapter($this->config);

		return $this->db;	
	}
}