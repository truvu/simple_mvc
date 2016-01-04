<?php
require __DIR__.'/../app/config/define.php';

use Truvu\Loader;
use Truvu\DI;
use Truvu\Db\Mysql as DBMysql;
use Truvu\Mvc\Application;

$database = require CONFIG.'database.php';

$loader = new Loader;
$loader->register(__DIR__.'/../app/mvc/models/');

$di = new DI;
$di->set('mysql', function() use($database){
	return DBMysql::connect($database['mysql']);
});

$app = new Application($di);

// $app->get('/', function(){
// 	echo "Index Using Callback Function";
// });

$app->handle();
