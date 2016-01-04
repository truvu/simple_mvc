<?php 

return array(
	'file' => __DIR__.'/../../database',
	'mysql' => array(
			'host' => 'localhost',
			'port' => 3306,
			'username' => 'root',
			'password' => '',
			'dbname' => 'thuan',
			'charset' => 'utf8'
		),
	'mongo' => array(
			'host' => 'localhost',
			'port' => 27017,
			'dbname' => 'test',
			'file' => __DIR__.'/../../database/id/'
		)
);
