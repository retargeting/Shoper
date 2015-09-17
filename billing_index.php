<?php

/**
*	Billing Index
*
*	- POST params
*/

/*
$_POST = array(
	'action' => 'install',
	'application_code' => '21e6f0c7c42542af3bbf267cfde9763a',
	'application_version' => '4',
	'auth_code' => 'fc9ada2c29cfe4e10f89500b0219f128',
	'shop' => '2f7fea8b3e9231a80d5bbea019643a9bf656c63d',
	'shop_url' => 'https://devshop-63421.shoparena.pl',
	'timestamp' => '2015-06-25 10:14:16',
	'hash' => '065f07fd45d5c3332ab6adf2d6b4f7c352baa00c558c364df65bc4fe64c5d3284acf28de25d457d241b88c791f0cf90b2c8ae8c725e3f5edee7bd9ab86a097d4'
);
*/

/*
$f = file_get_contents('log.txt');
$f .= '--- '.date('d-m-Y')." ---\n\n".print_r($_POST, true)."\n\n\n";
file_put_contents('log.txt', $f);
*/

// check if valid request
if (empty($_POST['shop_url']) || empty($_POST['action'])) {
	die;
}

require 'config.php';
require 'lib/BillingSystem.php';
//use Retargeting\Lib\App;

$app = new App( Config(), $_POST );

if ($app->validRequest) {
	$app->dispatch();
}
