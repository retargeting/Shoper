<?php
/**
*	Config
*
*	- returns cofinguration
*/
function Config() {

	return array(
		'appId' => '21e6f0c7c42542af3bbf267cfde9763a',
		'appSecret' => 'bf064df5bd47e443ed40893e2c7a242f68773cb6',
		'appstoreSecret' => 'f7884a56d0afc2c522d5ce9c75735a5f9bdf85f7',
		'db' => array(
			'host' => 'localhost',
			'user' => 'retarbiz_shopify',
			'pass' => 'R*F2;nTGMwqf',
			'db' => 'retarbiz_shoper'
		),
		'debug' => false,
		'logFile' => "logs/application.log",
		'timezone' => 'Europe/Bucharest'
	);

}
