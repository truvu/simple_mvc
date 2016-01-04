<?php
namespace Truvu\Http;
class Request
{
    private $method, $query;
    private $headers = [];
    public function __construct($query=null){
        $this->query = $query;
        $this->method = $_SERVER['REQUEST_METHOD'];
    }
    public function getValue($value, $ext, $default){
        switch($ext){
            case 'string':
                return empty($value)?$default:htmlentities($value);
            case 'number':
                return preg_match('/^[0-9]+$/', $value)?(float)$value:$default;
            case 'bool':
                if('true'===$value) return true;
                elseif('false'===$value) return false;
                else return $default;
            default:
                return $default;
        }
    }
    public function isPost(){
        return($this->method==='POST');
    }
    public function post($name='', $ext='string', $default=null){
        return(isset($_POST[$name])?$this->getValue($_POST[$name], $ext, $default):$default);
    }
    public function isGet(){
        return($this->method==='GET');
    }
    public function get($name='', $ext='string', $default=null){
        return(isset($_GET[$name])?$this->getValue($_GET[$name], $ext, $default):$default);
    }
    public function query($name='', $ext='string', $default=null){
        if(!$this->query){
            $uri = parse_url($_SERVER['REQUEST_URI']);
            if(isset($uri['query'])){
                parse_str($uri['query'], $out);
                $this->query = $out;
            }else return $default;
        }
        return($this->getValue($this->query[$name], $ext, $default));
    }
    public function isAjax(){
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) return false;
        return($_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest');
    }
    public function getHeader($name='')
    {
        if(!count($this->headers)){
            foreach($_SERVER as $key => $value) {
                if(substr($key, 0, 5) <> 'HTTP_')continue;
                $header = str_replace('_', '-', strtolower(substr($key, 5)));
                $this->headers[$header] = $value;
            }
        }
        return isset($this->headers[$name])?$this->headers[$name]:null;
    }
}
