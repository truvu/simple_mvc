<?php
namespace Truvu\Db;
class Mongo
{
	private static $db=null, $collection=null, $file=null, $list=[];
    public function __construct($name=null){
        if($name!==null) $this->fileName = $name;
    }
    public function getId(){
        $file = self::$file.$this->fileName.'.txt';
        $id = file_get_contents($file);
        if(is_string($id)) $id = (float)$id;
        $id = $id+1;
        file_put_contents($file, $id);
        return $id;
    }
	private static function collection(){
		$a = get_called_class();
		if(!isset(self::$list[$a])){
			$b = preg_replace_callback('/[A-Z]+[a-z]+/', function($m){return strtolower($m[0]).'_';}, $a);
			$b = rtrim($b, '_');
			self::$list[$a] = $b;
		}
        self::$collection = self::$list[$a];
		return self::$db->{self::$collection};
	}
	public static function connect($array=array()){
		if(null===self::$db){
            if(!isset($array['host'])) $array['host'] = 'localhost';
            if(!isset($array['port'])) $array['port'] = '27017';
            if(!isset($array['name'])) $array['name'] = 'test';
            if(isset($array['file'])) self::$file = $array['file'];
            $url = "mongodb://{$array['host']}:{$array['port']}";
            self::$db=(new \MongoClient($url))->{$array['dbname']};
        }
		return new self;
	}
	public static function find(){
        $array = func_get_args();
		$collection = self::collection();
		switch(count($array)) {
            case 1: return $collection->find($array[0]);
            case 2: return $collection->find($array[0], $array[1]);
            default: return $collection->find();
        }
	}
    public static function findFirst(){
        $array = func_get_args();
		$collection = self::collection();
		switch(count($array)) {
            case 1: return $collection->findOne($array[0]);
            case 2: return $collection->findOne($array[0], $array[1]);
            default: return $collection->findOne();
        }
    }
    public static function getRef($a){
        return self::collection()->getDBRef($a);
    }
    public static function insert($array=null){
       return self::collection()->insert($array);
    }
    public function save(){
        $array = $this;
        if(isset($array->fileName)){
            $array->_id = $this->getId();
            unset($array->fileName);
        }
        return self::collection()->insert($array);
    }
    public static function update(){
        $array = func_get_args();
		$collection = self::collection();
        switch(count($array)) {
            case 1: return $collection->update($array[0]);
            case 2: return $collection->update($array[0], $array[1]);
            case 3: return $collection->update($array[0], $array[1], $array[2]);
            default: return false;
        }
    }
    public static function delete($array=null){
		$collection = self::collection();
        if(count($array)>0) return $collection->remove($array);
        else return $collection->remove();
    }
}

?>