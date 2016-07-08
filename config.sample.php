<?php
/**
*	Config
*
*	- returns cofinguration
*/
function Config() {

	return array(
		'appId' => '',
		'appSecret' => '',
		'appstoreSecret' => '',
		'db' => array(
			'host' => 'localhost',
			'user' => '',
			'pass' => '',
			'db' => ''
		),
		'debug' => false,
		'logFile' => "logs/application.log",
		'timezone' => 'Europe/Bucharest'
	);

}
