<?php
namespace Truvu;

class Loader
{
	public function __construct(){
		spl_autoload_register(function($core){
			$a = __DIR__.'/../'.strtolower(str_replace('\\', '/', $core)).'.php';
			if(is_readable($a)) require $a;
		});
	}
	public function register($dir, $fn='strtolower'){
		spl_autoload_register(function($name) use($dir){
			$a = $dir.strtolower(str_replace('\\', '/', $name)).'.php';
			if(is_readable($a)) require $a;
		});
	}
}
