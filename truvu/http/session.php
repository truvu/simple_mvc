<?php
session_start();
namespace Truvu\Http;

class Session
{
	public function has($name='') {
		return isset($_SESSION[$name]);
	}
	public function set($name='') {
		return $_SESSION[$name]=$value;
	}
	public function get($name=''){
		return $_SESSION[$name];
	}
	public function delete($name='') {
		if($name) return isset($_SESSION[$name])?unset($_SESSION[$name]):false;
		else session_destroy();
	}
}
