<?php
require __DIR__.'/../../../app/config/define.php';
require CORE.'mvc/asset.php';
use Truvu\Mvc\Asset;

$asset = new Asset;

header("Content-type: text/css; charset: UTF-8");
ob_start('ob_gzhandler');
// header('Cache-Control: public');
// header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 1000) . ' GMT');

if(!isset($_GET['name'])) return false;
if(preg_match('/(index|account)/', $_GET['name'], $a)){
	$name = $a[0];
	$finish = $asset->finish($name, 'css');
	if(TEST){
		$css = $asset->compress('css', array('b/bootstrap', "s/$name"));
		echo $css;
		file_put_contents($finish, $css);
	}else echo file_get_contents($finish);
}

ob_end_flush();