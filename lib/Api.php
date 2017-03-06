<?php
//namespace Retargeting\Lib;

require 'DataBaseConnection.php';
require 'HttpAdapter.php';

class Api
{

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var bool
     */
    protected $db = false;

    /**
     * @var bool
     */
    protected $shopId = false;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var bool
     */
    public $validRequest = false;

    /**
     * @var
     */
    protected $http;


    /**
     * App constructor.
     * @param $config
     */
    public function __construct($config)
    {

        $this->config = $config;

        $this->init();

        $this->validRequest = $this->verifySender();

        return true;
    }

    /**
     * @return bool
     */
    public function verifySender()
    {

        if ($this->shopId && !empty($this->params['method'])) {

            return true;
        }

        return false;
    }

    /**
     * @return bool|\DBConn
     */
    private function init()
    {

        // Request Params
        $this->params = $_GET;
        $this->params['params'] = json_decode(urldecode($this->params['params']));

        // DB
        $this->db = new DBConn($this->config['db']['host'], $this->config['db']['user'], $this->config['db']['pass'],
            $this->config['db']['db']);

        // Shop ID
        $this->shopId = $this->db->getShopIdByUrl($this->params['shop']);

        // HTTP
        $tokens = $this->db->getShopTokens($this->shopId);

        $this->http = new HttpAdapter($this->config, $tokens);

        return $this->db;
    }

    /**
     * @return null|string
     */
    public function dispatch()
    {
        $requestMethod = $this->params['method'];

        switch ($requestMethod) {
            case 'embedd':
                return $this->_embedd();
                break;
            case 'setEmail':
                return $this->_setEmail();
                break;
            case 'sendCategory':
                return $this->_sendCategory();
                break;
            case 'sendBrand':
                return $this->_sendBrand();
                break;
            case 'sendProduct':
                return $this->_sendProduct();
                break;
            case 'visitHelpPages':
                return $this->_visitHelpPages();
                break;
            case 'saveOrder':
                return $this->_saveOrder();
                break;
            case 'querySelector':
                return $this->_querySelector();
                break;
            default:
                return '/*' . json_encode(array("Error" => "Invalid Method!")) . '*/';
                break;
        }
    }

    /**
     * @return string
     */
    protected function _embedd()
    {

        /** @noinspection PhpUndefinedMethodInspection */
        $shopData = $this->db->getShopConfig($this->shopId);

        if ($shopData['status']) {

            if (!empty($shopData['domain_api_key'])) {
                return '
					(function(){
					ra_key = "' . $shopData['domain_api_key'] . '";
					ra_params = {
						add_to_cart_button_id: "' . $shopData['qs_add_to_cart'] . '",
						price_label_id: "' . $shopData['qs_price'] . '",
					};
					var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
					document.location.protocol ? "https://" : "http://") + "tracking.retargeting.biz/v3/rajs/" + ra_key + ".js";
					var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);})();
				';
            } else {
                return '
					console.info("Retargeting Tracker: please set a proper Tracking API Key in the App\'s Configuration Area.");	
				';
            }
        }

        return '/*' . json_encode(["Info" => "Retargeting App is disabled!"]) . '*/';
    }

    /**
     * @return string
     */
    protected function _setEmail()
    {

        /** @noinspection PhpUndefinedMethodInspection */
        $shopData = $this->db->getShopConfig($this->shopId);

        if (empty($this->params['params']->id)) {
            return '/*' . json_encode(array("Info" => "Invalid params!")) . '*/';
        }

        if ($shopData['status']) {

            $entityId = $this->params['params']->id;

            /** @noinspection PhpUndefinedMethodInspection */
            $shopData = $this->db->getShopData($this->shopId);

            /** @noinspection PhpUndefinedFieldInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            $user = $this->http->getUser($shopData['shop_url'], $entityId);
            if (!$user) {
                return '/* Info: could not fetch Data because of invalid tokens. */';
            }

            $name = [];
            if (!empty($user->firstname)) {
                $name[] = $user->firstname;
            }
            if (!empty($user->lastname)) {
                $name[] = $user->lastname;
            }

            return 'var _ra = _ra || {};
				_ra.setEmailInfo = {
					"email": "' . $user->email . '",
					"name": "' . implode(' ', $name) . '"
				};
			';
        }

        return '/*' . json_encode(array("Info" => "Retargeting App is disabled!")) . '*/';
    }

    /**
     * @return string
     */
    protected function _sendCategory()
    {

        /** @noinspection PhpUndefinedMethodInspection */
        $shopData = $this->db->getShopConfig($this->shopId);

        if (empty($this->params['params']->id)) {
            return '/*' . json_encode(array("Info" => "Invalid params!")) . '*/';
        }

        if ($shopData['status']) {

            $entityId = $this->params['params']->id;

            /** @noinspection PhpUndefinedMethodInspection */
            $shopData = $this->db->getShopData($this->shopId);

            /** @noinspection PhpUndefinedFieldInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            $category = $this->http->getCategory($shopData['shop_url'], $entityId);
            if (!$category) {
                return '/* Info: could not fetch Data because of invalid tokens. */';
            }

            $categoryParent = 'false';
            if (!$category->root) {
                // build category breadcrumb
            }

            return 'var _ra = _ra || {};
				_ra.sendCategoryInfo = {
					"id": "' . $category->category_id . '",
					"name" : "' . htmlspecialchars($category->translations->pl_PL->name) . '",
					"parent": ' . $categoryParent . ',
					"breadcrumb": []
				}

				if (_ra.ready !== undefined) {
					_ra.sendCategory(_ra.sendCategoryInfo);
				};
			';
        }

        return '/*' . json_encode(array("Info" => "Retargeting App is disabled!")) . '*/';
    }

    /**
     * @return string
     */
    protected function _sendBrand()
    {

        /** @noinspection PhpUndefinedMethodInspection */
        $shopData = $this->db->getShopConfig($this->shopId);

        if (empty($this->params['params']->id)) {
            return '/*' . json_encode(array("Info" => "Invalid params!")) . '*/';
        }

        if ($shopData['status']) {

            $entityId = $this->params['params']->id;

            /** @noinspection PhpUndefinedMethodInspection */
            $shopData = $this->db->getShopData($this->shopId);

            /** @noinspection PhpUndefinedFieldInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            $brand = $this->http->getBrand($shopData['shop_url'], $entityId);
            if (!$brand) {
                return '/* Info: could not fetch Data because of invalid tokens. */';
            }

            return 'var _ra = _ra || {};
				_ra.sendBrandInfo = {
					"id": "' . $brand->producer_id . '",
					"name": "' . $brand->name . '"
				};

				if (_ra.ready !== undefined) {
					_ra.sendBrand(_ra.sendBrandInfo);
				}
			';
        }

        return '/*' . json_encode(["Info" => "Retargeting App is disabled!"]) . '*/';
    }

    /**
     * @return string
     */
    protected function _sendProduct()
    {

        /** @noinspection PhpUndefinedMethodInspection */
        $shopData = $this->db->getShopConfig($this->shopId);

        if (empty($this->params['params']->id)) {
            return '/*' . json_encode(array("Info" => "Invalid params!")) . '*/';
        }

        if ($shopData['status']) {

            $entityId = $this->params['params']->id;

            /** @noinspection PhpUndefinedMethodInspection */
            $shopData = $this->db->getShopData($this->shopId);

            /** @noinspection PhpUndefinedFieldInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            $product = $this->http->getProduct($shopData['shop_url'], $entityId);
            if (!$product) {
                return '/* Info: could not fetch Data because of invalid tokens. */';
            }

            $productImage = '';
            if ($product->main_image !== null) {
                $productImage = $shopData['shop_url'] . '/userdata/gfx/' . $product->main_image->unic_name . '.jpg';
            }

            /** @noinspection PhpUndefinedFieldInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            $category = $this->http->getCategory($shopData['shop_url'], $product->category_id);
            $categoryParent = 'false';

            return 'var _ra_ProductId = "' . $product->product_id . '";
				var _ra = _ra || {};
				_ra.sendProductInfo = {
					"id": "' . $product->product_id . '",
					"name": "' . htmlspecialchars($product->translations->pl_PL->name) . '",
					"url": "' . $product->translations->pl_PL->permalink . '",
					"img": "' . $productImage . '",
					"price": "' . $product->stock->price . '",
					"promo": "' . ($product->stock->price_special != $product->stock->price ? $product->stock->price_special : 0) . '",
					"brand": false,
					"category": [{
						"id": "' . $category->category_id . '",
						"name" : "' . $category->translations->pl_PL->name . '",
						"parent": ' . $categoryParent . ',
						"breadcrumb": []
					}],
					"inventory": {
						"variations": false,
						"stock": ' . ($product->stock->stock > 0 ? 1 : 0) . '
					}
				};
				
				if (_ra.ready !== undefined) {
					_ra.sendProduct(_ra.sendProductInfo);
				}
			';
        }

        return '/*' . json_encode(array("Info" => "Retargeting App is disabled!")) . '*/';
    }

    /**
     * @return string
     */
    protected function _visitHelpPages()
    {

        /** @noinspection PhpUndefinedMethodInspection */
        $shopData = $this->db->getShopConfig($this->shopId);

        if (empty($this->params['params']->id)) {
            return '/*' . json_encode(array("Info" => "Invalid params!")) . '*/';
        }

        if ($shopData['status']) {

            $entityId = $this->params['params']->id;

            if ($shopData['help_pages'] == '') {
                return '/*' . json_encode(array("Info" => "No help pages specified in Retargeting App!")) . '*/';
            }

            $pageHandles = explode(',', str_replace(' ', '', $shopData['help_pages']));

            foreach ($pageHandles as $pageHandle) {
                if ($entityId == $pageHandle || $entityId == '/' . $pageHandle) {
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
        }

        return '/*' . json_encode(["Info" => "Retargeting App is disabled!"]) . '*/';

    }

    /**
     * @return string
     */
    protected function _saveOrder()
    {

        /** @noinspection PhpUndefinedMethodInspection */
        $shopData = $this->db->getShopConfig($this->shopId);

        if (empty($this->params['params']->id)) {
            return '/*' . json_encode(array("Info" => "Invalid params!")) . '*/';
        }

        if ($shopData['status']) {

            $entityId = $this->params['params']->id;

            /** @noinspection PhpUndefinedMethodInspection */
            $shopData = $this->db->getShopData($this->shopId);

            /** @noinspection PhpUndefinedFieldInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            $order = $this->http->getOrder($shopData['shop_url'], $entityId);
            if (!$order) {
                return '/* Info: could not fetch Data because of invalid tokens. */';
            }

            $discount = number_format($order->sum * $order->discount_code / 100);

            return '
				var _ra_oDiscountCode = "' . $discount . '";
				_ra_saveOrder("' . $discount . '");
			';
        }
        return '/*' . json_encode(array("Info" => "Retargeting App is disabled!")) . '*/';
    }

    /**
     * @return string
     */
    protected function _querySelector()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $shopData = $this->db->getShopConfig($this->shopId);

        if (empty($this->params['params']->selector)) {
            return '/*' . json_encode(["Info" => "Invalid params!"]) . '*/';
        }

        if ($shopData['status']) {
            $selector = $this->params['params']->selector;

            switch ($selector) {
                case 'addToCart':
                    return $shopData['qs_add_to_cart'];
                    break;
                case 'setVariation':
                    return $shopData['qs_variation'];
                    break;
                case 'qs_add_to_wishlist':
                    return $shopData['qs_add_to_wishlist'];
                    break;
                case 'clickImage':
                    return ($shopData['qs_product_images'] == '') ?
                        'var _raClickImage = undefined;' :
                        'var _raClickImage = "' . $shopData['qs_product_images'] . '";';
                    break;
                case 'commentOnProduct':
                    return $shopData['qs_review'];
                    break;
                case 'promoPrice':
                    return $shopData['qs_price'];
                    break;
                case 'oldPrice':
                    return $shopData['qs_old_price'];
                default:
                    return '/*' . json_encode(["Info" => "Invalid Query Parameters!"]) . '*/';
                    break;
            }
        }
        return '/*' . json_encode(["Info" => "Invalid Query Parameters!"]) . '*/';
    }
}