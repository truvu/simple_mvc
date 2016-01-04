<?php
require __DIR__.'/../../../app/config/define.php';
require CORE.'mvc/asset.php';
use Truvu\Mvc\Asset;

$asset = new Asset;

header("Content-type: text/javascript; charset: UTF-8");
ob_start('ob_gzhandler');
// header('Cache-Control: public');
// header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 1000) . ' GMT');

$list = require ASSET.'js/list.php';
$controller = $_GET['controller'];

$array = isset($list[$controller])?$list[$controller]:null;
if($array){
	$finish = $asset->finish($controller, 'js');
	if(TEST){
		$javascript = $asset->compress('js', $array);
		echo $javascript;
		file_put_contents($finish, $javascript);
	}else echo file_get_contents($finish);
}

ob_end_flush();