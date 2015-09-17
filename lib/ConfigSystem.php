<?php
//namespace Retargeting\Lib;

require 'DataBaseConnection.php';
require 'HttpAdapter.php';

class App {
	
	protected $config = array();

	protected $db = false;

	protected $http = false;

	public $validRequest = false;

	public $shop = '';

	public $shopKey = '';

	public $status = 'Disabled';

	public $domainApiKey = '';

	public $discountsApiKey = '';

	public $helpPages = '';

	public $querySelectors = array();
	
	public function __construct($config) {

		$this->config = $config;

		$this->validRequest = $this->verifySender();

		if ($this->validRequest) {

			$this->init();

			$this->initView();
		}

        return true;
    }

	public function verifySender() {

		$params = array(
			'place' => $_GET['place'],
			'shop' => $_GET['shop'],
			'timestamp' => $_GET['timestamp']
			);

		ksort($params);

		$param_array = array();
		foreach($params as $k => $v){
		    $param_array[] = $k."=".$v;
		}

		$param_string = join("&", $param_array);

		$hash = hash_hmac('sha512', $param_string, $this->config['appstoreSecret']);

		if ($_GET['hash'] == $hash) return true;

		return false;
    }

	private function init() {

		// DB
		$this->db = new DBConn($this->config['db']['host'], $this->config['db']['user'], $this->config['db']['pass'], $this->config['db']['db']);

		// HTTP
		$this->http = new HttpAdapter($this->config);

		return $this->db;	
	}

	private function initView() {

		// get shop Id
		$shopId = $this->db->getShopId($_GET['shop']);

		// get shop View
		$shopConfig = $this->db->getShopConfig($shopId);

		$this->status = $shopConfig['status'];
		$this->init = $shopConfig['init'];
		$this->domainApiKey = $shopConfig['domain_api_key'];
		$this->discountsApiKey = $shopConfig['discounts_api_key'];
		$this->querySelectors['addToCart'] = $shopConfig['qs_add_to_cart'];
		$this->helpPages = $shopConfig['help_pages'];

		// get shop Key & URL
		$shopData = $this->db->getShopData($shopId);

		$this->shop = $shopData['shop_url'];
		$this->shopKey = $shopData['shop'];

		return true;
	}

}