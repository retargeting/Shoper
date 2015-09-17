<?php

/**
* Configuration Ajax Route
*
* - route for configuration changes in the App's Administration section
*/

if (empty($_POST['shop']) || empty($_POST['shopKey'])) {
	die('<p>Unauthorized request!</p>');
}
if ((!isset($_POST['domainApiKey']) || !isset($_POST['discountsApiKey'])) && (empty($_POST['disableInit']))) {
	die('<p>Invalid request!</p>')
}

require 'config.php';
require 'lib/Ajax.php';

$app = new App(Config());

if ($app->validRequest) {

	die($app->dispatch());
}