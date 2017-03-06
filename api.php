<?php

/**
*	JS API Index
*
*	- POST params
*/

// check if valid request
if (empty($_GET['shop'])) {
	die('/*'.json_encode(array("Error" => "Invalid Request!")).'*/');
}

require 'config.php';
require 'lib/Api.php';
//use Retargeting\Lib\App;

$app = new Api( Config() );

if ($app->validRequest) {
	
	echo $app->dispatch();
}
