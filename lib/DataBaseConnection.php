<?php
//namespace Retargeting\Lib;

class DBConn {
	
	// ! add mysqli_real_escape to all params

	protected $db = null;

	public function __construct($host, $user, $pass, $db) {
        
        $this->db = mysqli_connect($host, $user, $pass, $db);

		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
			return false;
		}

		return true;
    }

	public function saveShop($shop, $shop_url, $version) {
		
		$sql = "INSERT INTO shops (shop, shop_url, version) VALUES ('".$shop."', '".$shop_url."', '".$version."')";

		if ($this->db->query($sql) === TRUE) {
			
			$shopId = $this->db->insert_id;

			$sql = "INSERT INTO configurations (shop_id) VALUES ('".$shopId."')";

			if ($this->db->query($sql) === TRUE) {

				return $shopId;
			}	
		}

		return false;
	}

	public function saveTokens($shopId, $tokens) {

		$expirationDate = date('Y-m-d H:i:s', time() + $tokens->expires_in);

		$sql = "INSERT INTO access_tokens (shop_id, expires_at, access_token, refresh_token) VALUES ('".$shopId."', '".$expirationDate."', '".$tokens->access_token."', '".$tokens->refresh_token."')";

		return $this->db->query($sql);
	}

	public function deleteShop($shopId) {
		
		$sql = "DELETE FROM shops WHERE id = ".(int)$shopId;

		return $this->db->query($sql);
	}

	public function deleteTokens($shopId) {
		
		$sql = "DELETE FROM access_tokens WHERE id = ".(int)$shopId;

		return $this->db->query($sql);
	}

	public function deleteConfig($shopId) {
		
		$sql = "DELETE FROM configurations WHERE shop_id = ".(int)$shopId;

		return $this->db->query($sql);
	}

	public function updateShop($shopId, $appVer) {
		
		$sql = "UPDATE shops set version = '".$appVer."' WHERE id = ".(int)$shopId;

		return $this->db->query($sql);
	}

	public function getShopId($shop) {
    	
    	$sql = "SELECT id FROM shops WHERE shop='".$shop."' LIMIT 1";
		
		$res = $this->db->query($sql);

		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			return $row['id'];
		} else {
			return false;
		}
    }

    public function getShopIdByUrl($shop) {
    	
		$sql = "SELECT id FROM shops WHERE shop_url='".$shop."' OR shop_url='http://".$shop."' OR shop_url='https://".$shop."' LIMIT 1";
		
		$res = $this->db->query($sql);

		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			return $row['id'];
		} else {
			return false;
		}
    }

    public function getShopConfig($shopId) {
    	
    	$sql = "SELECT status, init, domain_api_key, discounts_api_key, qs_add_to_cart, help_pages FROM configurations WHERE shop_id='".$shopId."' LIMIT 1";
		
		$res = $this->db->query($sql);

		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			return $row;
		} else {
			return false;
		}
    }

    public function getShopData($shopId) {
    	
    	$sql = "SELECT shop, shop_url FROM shops WHERE id='".$shopId."' LIMIT 1";
		
		$res = $this->db->query($sql);

		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			return $row;
		} else {
			return false;
		}
    }

    public function getShopTokens($shopId) {
    	
    	$sql = "SELECT access_token, refresh_token FROM access_tokens WHERE shop_id='".$shopId."' LIMIT 1";
		
		$res = $this->db->query($sql);

		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			return $row;
		} else {
			return false;
		}
    }

    public function updateShopConfig($shopId, $status, $domainApiKey, $discountsApiKey, $helpPages, $qsAddToCart) {
    	
    	if ($status) {
    		$changeStatusSQL = '';
    	}
    	
    	$sql = "UPDATE configurations set ".($status ? 'status = NOT status, ' : '')."domain_api_key = '".$domainApiKey."', discounts_api_key = '".$discountsApiKey."', help_pages = '".$helpPages."', qs_add_to_cart = '".$qsAddToCart."' WHERE shop_id = ".(int)$shopId;
    	
		return $this->db->query($sql);
    }

    public function disableInit($shopId) {
    	
    	$sql = "UPDATE configurations set init = true WHERE shop_id = ".(int)$shopId;
    	
		return $this->db->query($sql);
    }
}