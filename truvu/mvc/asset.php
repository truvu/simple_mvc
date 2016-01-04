<?php
namespace Truvu\Mvc;

class Asset
{
	public function fileCSS($a){
		return ASSET."css/$a.css";
	}
	public function fileJS($a){
		return ASSET."js/$a.js";
	}
	public function finish($a, $b='js'){
		return ASSET."$b/finish/$a.$b";
	}
	public function css($a, $async=false){
		$b = $async?'async':'';
		if(is_array($a)){
			for ($i=0, $n=count($a); $i < $n; $i++) { 
				echo '<link rel="stylesheet" href="'.URL.'asset/css/s/'.$a[$i].'.css" '.$b.'/>';
			}
		}else echo '<link rel="stylesheet" href="'.URL.'asset/css/s/'.$a.'.css" '.$b.'/>';
		return $this;
	}
	public function js($a, $async=false){
		$b = $async?'async':'';
		if(is_array($a)){
			for ($i=0, $n=count($a); $i < $n; $i++) { 
				echo '<script type="text/javascript" src="'.URL.'asset/js/'.$a[$i].'.js" '.$b.'></script>';
			}
		}else echo '<script type="text/javascript" src="'.URL.'asset/js/'.$a.'.js" '.$b.'></script>';
		return $this;
	}
	public function compress($type='css', $list){
		$buffer = '';
		if($type=='css'){
			for($i=0, $n=count($list); $i < $n; $i++) $buffer.= file_get_contents($this->fileCSS($list[$i]))."\n";
		}else{
			for($i=0, $n=count($list); $i < $n; $i++) $buffer.= file_get_contents($this->fileJS($list[$i]))."\n";
		}
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		if(COMPRESS){
			$array = array(
				"\n" => '',
				"\r" => '',
				"\t" => '',
				': ' => ':',
				', ' => ',',
				' = '=> '=',
				';}' => '}',
				'function (' => 'function(',
				') ' => ')'
			);
			$buffer = preg_replace('/\s{2,}/', '', $buffer);
			$buffer = str_replace(array_keys($array), array_values($array), $buffer);			
		}
		return $buffer;
	}
}
