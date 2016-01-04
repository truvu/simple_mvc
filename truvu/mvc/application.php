<?php
namespace Truvu\Mvc;
use Truvu\Http\Request;
use Truvu\Http\Response;
use Truvu\Mvc\Asset;

class Application
{
	private $uri;
	private $path;
	private $query;
	private $controller = 'index';
	private $action = 'index';
	private $next = 1;
	private $getted;
	private $posted;

	public function __construct($di=null){
		$this->uri = $_SERVER['REQUEST_URI'];
		$uri = parse_url($this->uri);
		if(isset($uri['query'])){
			parse_str($uri['query'], $get);
			$this->query = $get;
		}
		$this->path = explode('/', trim($uri['path'], '/'));
		if(!empty($this->path[0])) $this->controller = $this->path[0];
		if(isset($this->path[1])) $this->action = $this->path[1];

		$this->request = new Request($this->query);
		$this->response = new Response();
		$this->view = new View();
		$this->view->asset = new Asset;
		if($di) foreach($di->get() as $name => $value) $this->{$name}=$value;
	}
	public function handle()
	{
		if($this->next){
			$file = CONTROLLER.$this->controller.'.php';
			if(file_exists($file)){
				require __DIR__.'/controller.php';
				require $file;
				$controller = ucfirst($this->controller).'Controller';
				$controller = new $controller;
				if(method_exists($controller, $this->action)){
					if(isset($this->path[2])){
						$old = array($this->path[0], $this->path[1]);
						$params = array_diff($this->path, $old);
						$controller->{$this->action}($params);
					}else $controller->{$this->action}();
					$this->next = 0;
				}else echo "{$this->controller} controller -> {$this->action} not found";
			}else echo "{$this->controller} controller not found";	
		}
		return $this;
	}

	public function before($cb){
		$this->next=$cb();
	}
	private function parse($match, $array){
		if($this->next){
			if(preg_match('/[\(\|]/', $match)){
				$match = str_replace(['/', '?'], ['\/', '\?'], $match);
				if(preg_match('/^'.$match.'$/', $this->uri, $m)){
					$this->getted = 1; unset($m[0]);
					if(is_array($array)){
						if(!isset($array[1])){$array[1]=$m[1];unset($m[1]);}
						$controller = ucfirst($array[0]).'Controller';
						(new $controller)->$array[1]($m);
					}else call_user_func_array($array, $m);
					$this->next = 0;
				}else $this->next = 1;
			}else{
				if($match===$this->uri){
					$this->getted = 1;
					if(is_array($array)){
						$controller = ucfirst($array[0]).'Controller';
						(new $controller)->$array[1]();
					}else call_user_func($array);
					$this->next = 0;
				}else $this->next = 1;
			}
		}
	}
	public function get($match, $array){
		if($this->request->isGet()&&!$this->getted)return $this->parse($match, $array);
	}
	public function post($match, $array){
		if($this->request->isPost()&&!$this->posted)return $this->parse($match, $array);
	}
	public function notFound($cb=null){
		if($this->getted||$this->posted)exit();
		if($cb)return call_user_func($cb);
	}
}
