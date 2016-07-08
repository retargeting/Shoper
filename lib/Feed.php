<?php
//namespace Retargeting\Lib;

require 'DataBaseConnection.php';
require 'HttpAdapter.php';

class Feed {
	
	protected $config = array();

	protected $db = false;

	protected $shopId = false;

	protected $params =  array();

	public $validRequest = false;

	public function __construct($config) {

		$this->config = $config;

		$this->init();

		$this->validRequest = $this->verifySender();

        return true;
    }

	public function verifySender() {

		if ($this->shopId && !empty($this->params['method'])) {

			return true;
		}

		return false;
    }

	private function init() {

		// Request Params
		$this->params = $_GET;
		$this->params['params'] = json_decode(urldecode($this->params['params']));

		// DB
		$this->db = new DBConn($this->config['db']['host'], $this->config['db']['user'], $this->config['db']['pass'], $this->config['db']['db']);

		// Shop ID
		$this->shopId = $this->db->getShopIdByUrl($this->params['shop']);

		// HTTP
		$tokens = $this->db->getShopTokens($this->shopId);

		$this->http = new HttpAdapter($this->config, $tokens);

		return $this->db;	
	}

	public function dispatch() {

		if ($this->params['method'] == "products") {

			return $this->_products();
		}

		return '/*'.json_encode(array("Error" => "Invalid Method!")).'*/';
	}

	protected function _products() {

		$shopData = $this->db->getShopConfig($this->shopId);

		if ($shopData['status']) {

			$entityId = $this->params['params']->id;

			$shopData = $this->db->getShopData($this->shopId);

			$products = $this->http->getProducts($shopData['shop_url'], 1);
			if (!$products) return '/* Info: could not fetch Data because of invalid tokens. */';

			$xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>';
			$xmlFeed .= '<products>';
			
			$xmlFeed .= $this->_products_listFromResponse($products);
			
			for ($page = 2; $page <= $products->pages ; $page ++) { 
				$products = $this->http->getProducts($shopData['shop_url'], $page);
				$xmlFeed .= $this->_products_listFromResponse($products);
			}

			$xmlFeed .= '</products>';
			
			return $xmlFeed;
		}

		return '/*'.json_encode(array("Info" => "Retargeting App is disabled!")).'*/';
	}

	private function _products_listFromResponse($response) {

		$xml = '';

		foreach ($response->list as $product) {
			$xml .= '
			<product>
				<id>'.$product->product_id.'</id>
				<price>'.$product->stock->price.'</price>
				<promo>'.($product->stock->price_special != $product->stock->price ? $product->stock->price_special : 0 ).'</promo>
				<inventory>
					<variations>0</variations>
					<stock>'.($product->stock->stock > 0 ? 1 : 0).'</stock>
				</inventory>
			</product>';
		}

		return $xml;
	}
}