<?php
namespace Truvu\Mvc;
class View
{
	public function setVar($name=null, $value=null){
		$this->{$name} = $value;
		return $this;
	}
	public function setVars($array=[]){
		foreach($array as $name => $value) {
			$this->setVar($name, $value);
		}
		return $this;
	}
	private function getVars(){
		return get_object_vars($this);
	}
	private function compress($buffer){
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		$buffer = preg_replace('/[\n\r\t]+/', '', $buffer);
		$buffer = preg_replace('/\s{2,}/', '', $buffer);
		return $buffer;
	}
	public function render($list=null, $data=null){
		if(is_array($data)) $this->setVars($data);
		ob_start('ob_gzhandler');
		header('Cache-Control: public');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 5000) . ' GMT');
		ob_start(array($this, 'compress'));

		foreach($this->getVars() as $var => $value) ${$var} = $value;
		if(is_array($list)){
			foreach($list as $file) require VIEW.$file.'.php';
		}else require VIEW.$list.'.php';
		
		ob_end_flush();
	}
}
