<?php
// command line in  path\to\project\app\socket
// press php -q index.php

require __DIR__.'/../config/define.php';
use Truvu\Loader;
use Truvu\DI;
use Truvu\Lib\Socket;

$loader = new Loader;
$loader->register(CORE.'lib/');

Socket::listen(3000);

Socket::connect(function($data){
	Socket::setData($data);

	Socket::on('chat', function($data){
		Socket::emit('chat', $data);
	});
	Socket::on('time', function($data){
		Socket::emit('time', $data);
	});

});
