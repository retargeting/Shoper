<?php

/**
*	Feed Index
*
*	- POST params
*/

// check if valid request
if (empty($_GET['shop'])) {
	die('/*'.json_encode(array("Error" => "Invalid Request!")).'*/');
}

require 'config.php';
require 'lib/Feed.php';
//use Retargeting\Lib\Feed;

$feed = new Feed( Config() );

if ($feed->validRequest) {
	
	echo $feed->dispatch();
}
