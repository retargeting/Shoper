<?php
//namespace Retargeting\Lib;

require 'DataBaseConnection.php';
require 'HttpAdapter.php';

class App {
	
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

		if ($this->params['method'] == "embedd") {

			return $this->_embedd();
		} else 
		if ($this->params['method'] == "setEmail") {

			return $this->_setEmail();
		} else
		if ($this->params['method'] == "sendCategory") {

			return $this->_sendCategory();
		} else
		if ($this->params['method'] == "sendBrand") {

			return $this->_sendBrand();
		} else
		if ($this->params['method'] == "sendProduct") {

			return $this->_sendProduct();
		} else
		if ($this->params['method'] == "mouseOverPrice") {

			return $this->_mouseOverPrice();
		} else
		if ($this->params['method'] == "visitHelpPages") {

			return $this->_visitHelpPages();
		} else
		if ($this->params['method'] == "saveOrder") {

			return $this->_saveOrder();
		}

		return '/*'.json_encode(array("Error" => "Invalid Method!")).'*/';
	}

	protected function _embedd() {

		$shopData = $this->db->getShopConfig($this->shopId);

		if ($shopData['status']) {

			if (!empty($shopData['domain_api_key'])) {
				return '
					(function(){
					var ra_key = "'.$shopData['domain_api_key'].'";
					var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
					document.location.protocol ? "https://" : "http://") + "retargeting-data.eu/rajs/" + ra_key + ".js";
					var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);})();
				';
			} else {
				return '
					(function(){
					var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
					document.location.protocol ? "https://" : "http://") + "retargeting-data.eu/" +
					document.location.hostname.replace("www.","") + "/ra.js"; var s =
					document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);})();
				';
			}
		}

		return '/*'.json_encode(array("Info" => "Retargeting App is disabled!")).'*/';
	}

	protected function _setEmail() {

		$shopData = $this->db->getShopConfig($this->shopId);

		if (empty($this->params['params']->id)) return '/*'.json_encode(array("Info" => "Invalid params!")).'*/';

		if ($shopData['status']) {

			$entityId = $this->params['params']->id;

			$shopData = $this->db->getShopData($this->shopId);

			$user = $this->http->getUser($shopData['shop_url'], $entityId);

			$name = array();
			if (!empty($user->firstname)) $name[] = $user->firstname;
			if (!empty($user->lastname)) $name[] = $user->lastname;

			return 'var _ra = _ra || {};
				_ra.setEmailInfo = {
					"email": "'.$user->email.'",
					"name": "'.implode(' ', $name).'"
				};
			';
		}

		return '/*'.json_encode(array("Info" => "Retargeting App is disabled!")).'*/';
	}

	protected function _sendCategory() {

		$shopData = $this->db->getShopConfig($this->shopId);

		if (empty($this->params['params']->id)) return '/*'.json_encode(array("Info" => "Invalid params!")).'*/';

		if ($shopData['status']) {

			$entityId = $this->params['params']->id;

			$shopData = $this->db->getShopData($this->shopId);

			$category = $this->http->getCategory($shopData['shop_url'], $entityId);

			$categoryTree = (object) array(
				'id' => -1,
				'children' => $this->http->getCategoryTree($shopData['shop_url'])
			);

			$categoryParent = 'false';
			if (!$category->root) {
				// build category breadcrumb
			}

			return 'var _ra = _ra || {};
				_ra.sendCategoryInfo = {
					"id": "'.$category->category_id.'",
					"name" : "'.$category->translations->pl_PL->name.'",
					"parent": '.$categoryParent.',
					"category_breadcrumb": []
				}

				if (_ra.ready !== undefined) {
					_ra.sendCategory(_ra.sendCategoryInfo);
				};
			';
		}

		return '/*'.json_encode(array("Info" => "Retargeting App is disabled!")).'*/';
	}

	protected function _sendBrand() {

		$shopData = $this->db->getShopConfig($this->shopId);

		if (empty($this->params['params']->id)) return '/*'.json_encode(array("Info" => "Invalid params!")).'*/';

		if ($shopData['status']) {

			$entityId = $this->params['params']->id;

			$shopData = $this->db->getShopData($this->shopId);

			$brand = $this->http->getBrand($shopData['shop_url'], $entityId);

			return 'var _ra = _ra || {};
				_ra.sendBrandInfo = {
					"id": "'.$brand->producer_id.'",
					"name": "'.$brand->name.'"
				};

				if (_ra.ready !== undefined) {
					_ra.sendBrand(_ra.sendBrandInfo);
				}
			';
		}

		return '/*'.json_encode(array("Info" => "Retargeting App is disabled!")).'*/';
	}

	protected function _sendProduct() {

		$shopData = $this->db->getShopConfig($this->shopId);

		if (empty($this->params['params']->id)) return '/*'.json_encode(array("Info" => "Invalid params!")).'*/';

		if ($shopData['status']) {

			$entityId = $this->params['params']->id;

			$shopData = $this->db->getShopData($this->shopId);

			$product = $this->http->getProduct($shopData['shop_url'], $entityId);

			$brand = $this->http->getBrand($shopData['shop_url'], $product->producer_id);

			$category = $this->http->getCategory($shopData['shop_url'], $product->category_id);
			$categoryParent = 'false';

			return 'var _ra_ProductId = "'.$product->product_id.'";
				var _ra = _ra || {};
				_ra.sendProductInfo = {
					"id": "'.$product->product_id.'",
					"name": "'.$product->translations->pl_PL->name.'",
					"url": "'.$product->translations->pl_PL->permalink.'",
					"img": "'.''.'",
					"price": "'.$product->stock->comp_price.'",
					"promo": "'.($product->stock->comp_promo_price != $product->stock->comp_price ? $product->stock->comp_promo_price : 0 ).'",
					"stock": '.($product->stock->stock > 0 ? 1 : 0).',
					"brand": {
						"id": "'.$brand->producer_id.'",
						"name": "'.$brand->name.'"
					},
					"category": {
						"id": "'.$category->category_id.'",
						"name" : "'.$category->translations->pl_PL->name.'",
						"parent": '.$categoryParent.'
					},
					"category_breadcrumb": []
				};
				
				if (_ra.ready !== undefined) {
					_ra.sendProduct(_ra.sendProductInfo);
				}
			';
		}

		return '/*'.json_encode(array("Info" => "Retargeting App is disabled!")).'*/';
	}

	protected function _mouseOverPrice() {

		$shopData = $this->db->getShopConfig($this->shopId);

		if (empty($this->params['params']->id)) return '/*'.json_encode(array("Info" => "Invalid params!")).'*/';

		if ($shopData['status']) {

			$entityId = $this->params['params']->id;

			$shopData = $this->db->getShopData($this->shopId);

			$product = $this->http->getProduct($shopData['shop_url'], $entityId);

			return '_ra.mouseOverPrice("'.$product->product_id.'", {
					"price": "'.$product->stock->comp_price.'",
					"promo": "'.($product->stock->comp_promo_price != $product->stock->comp_price ? $product->stock->comp_promo_price : 0 ).'",
				});
			';
		}

		return '/*'.json_encode(array("Info" => "Retargeting App is disabled!")).'*/';
	}

	protected function _visitHelpPages() {

		$shopData = $this->db->getShopConfig($this->shopId);

		if (empty($this->params['params']->id)) return '/*'.json_encode(array("Info" => "Invalid params!")).'*/';

		if ($shopData['status']) {

			$entityId = $this->params['params']->id;

			$pageHandles = explode(',', str_replace(' ', '', $shopData['help_pages']));

			foreach ($pageHandles as $pageHandle) {
				if ($entityId == $pageHandle || $entityId == '/'.$pageHandle) {
					return 'var _ra = _ra || {};
						_ra.visitHelpPageInfo = {
							"visit" : true
						}

						if (_ra.ready !== undefined) {
							_ra.visitHelpPage();
						}
					';
				}
			}
		} else {

			return '/*'.json_encode(array("Info" => "Retargeting App is disabled!")).'*/';
		}
	}

	protected function _saveOrder() {

		$shopData = $this->db->getShopConfig($this->shopId);

		if (empty($this->params['params']->id)) return '/*'.json_encode(array("Info" => "Invalid params!")).'*/';

		if ($shopData['status']) {

			$entityId = $this->params['params']->id;

			$shopData = $this->db->getShopData($this->shopId);

			$order = $this->http->getOrder($shopData['shop_url'], $entityId);

			$discount = number_format($order->sum * $order->discount_code / 100);

			return $order->sum.' * '.$order->discount_code.' = '.$discount.'
				var _ra_oDiscountCode = "'.$discount.'";
				_ra_saveOrder("'.$discount.'");
			';
		} else {

			return '/*'.json_encode(array("Info" => "Retargeting App is disabled!")).'*/';
		}
	}

	/*
	protected function searchCategoryTree($node) {

		$this->categoryPath[] = $node->id;
		
		$ret = false;

		foreach ($node->children as $childNode) {
			
			if ($childNode->id == $category->category_id) {
				// return $road;
			}

			$ret = $this->searchCategoryTree($childNode);
		}

		return $ret;
	}
	*/
}