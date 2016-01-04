<?php
namespace Truvu\Http;
class Response
{
	private $content;
	public function json($array){
		header('Content-Type: application/json; Charset: utf-8');
		return (is_array($array)||is_object($array))?json_encode($array):$array;
	}
	public function redirect($uri='/'){
		return header('Location: '+$uri);
	}
	public function setHeader($key=null, $value=''){
		if(is_array($key)) foreach($key as $k => $v)header("$k: $v");
		else header("$key: $value");
	}
	public function content($html){
		header('Content-Type: text/plain; Charset: utf-8');
		return (is_array($html)||is_object($html))?json_encode($html):$html;
	}
}
