<?php
	$conn = mysqli_connect("DBHOST","DBUSER","DBPASS","DBNAME");

	if (mysqli_connect_errno()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
		die();
	}

	function saveShopDetails($conn, $shop, $token) {

		$sql = "INSERT INTO shops (shop, token) VALUES ('".$shop."', '".$token."') ON DUPLICATE KEY UPDATE token='".$token."'";

		if ($conn->query($sql) === TRUE) {
			return true;
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
			die();
		}
	}

	function getShopDetails($conn, $shop) {

		$sql = "SELECT * FROM shops WHERE shop='".$shop."'";
		
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			return $row;
		} else {
			return false;
		}
	}

	function verifyShopStatus($conn, $shop) {

		if ($shop = getShopDetails($conn, $shop)) {
			if ($shop['token'] != '') return $shop;
		}

		return false;
	}

	function updateShopDetails($conn, $shop, $token, $domain, $domainApiKey, $discountsApiKey, $changeStatus, $newHelpPages, $newAddToCart, $newVariation, $newAddToWishlist, $newImage, $newReview, $newPrice) {
		$res = array();
		$res['errors'] = array();

		$sql = "UPDATE shops SET domain='".$domain."', domain_api_key='".$domainApiKey."', discounts_api_key='".$discountsApiKey."', status=".($domainApiKey == '' ? '0' : ($changeStatus ? 'NOT' : '').' status').", help_pages='".$newHelpPages."', qs_add_to_cart='".$newAddToCart."', qs_variation='".$newVariation."', qs_add_to_wishlist='".$newAddToWishlist."', qs_image='".$newImage."', qs_review='".$newReview."', qs_price='".$newPrice."' WHERE shop='".$shop."' AND token='".$token."'";

		if ($conn->query($sql) !== TRUE) {
			$res['errors'][] = "Error updating record: " . $conn->error;
		}

		return $res;
	}
