<?php
namespace Truvu;

class DI
{
	public function set($var, $cb){
		$this->{$var} = is_callable($cb)?$cb():$cb;
		return $this;
	}
	public function get(){
		return get_object_vars($this);
	}
}
