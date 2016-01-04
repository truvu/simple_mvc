<?php
namespace Truvu\Http;
class Cookie
{
	private static function time($time){
		return strtotime('+'.(int)$time.' days');
	}
	public function has($name='') {
		return isset($_COOKIE[$name]);
	}
	public function set($name='', $value=1, $time=7, $path='/') {
		return setcookie($name, $value, self::time($time), $path);
	}
	public function get($name='') {
		return $_COOKIE[$name];
	}
	public function delete($name='', $time=7, $path='/') {
		return isset($_COOKIE[$name]) ? setcookie($name, '', self::time($time), $path) : false;
	}
}
