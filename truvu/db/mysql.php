<?php
namespace Truvu\Db;
/*

	- in controller: function __construct:
	$this->mysql = DBMysql::connect(array());

	- select:
	$this->mysql->query('INSERT INTO user(fname,lname) values(:fname,:lname)', array('fname'=>$fname, 'lname'=>$lname));
	echo $this->mysql->insert_id;

	
	User::findFirst(1);

	User::find(array(
		'columns' => 'fname,lname',
		'where' => 'id=:id',
		'bind' => array('id'=>$id)
	));
	User::find(array(
		'columns' => 'user.fname,user.lname,u.email,u.pass',
		'where' => 'u.id=:id',
		'join' => array('user_login as u' => 'u.id=user.id'),
		'bind' => array('id'=>$id)
	));
	
	- update:
	$user = new User(1);
	$user->fname = 'new name';
	$user->save();

	- save: 
	$user = new User;
	$user->fname = 'Fname';
	$user->lname = 'Lname';
	$user->save();
	echo 'User.id = '.$user->id;

*/

class Mysql
{
	private static $db=null, $table=null, $list=[], $data=[], $insert_id;
	private $array; 
	public function __construct($array=null){
		if($array!==null) $this->array = $array;
	}
	public static function connect($array=array()){
		if(null===self::$db){
			try {
				if(!isset($array['driver'])) $array['driver'] = 'mysql';
				if(!isset($array['host'])) $array['host'] = 'localhost';
				if(!isset($array['port'])) $array['port'] = 3306;
				if(!isset($array['dbname'])) $array['dbname'] = 'test';
				if(!isset($array['username'])) $array['username'] = 'root';
				if(!isset($array['password'])) $array['password'] = '';
				if(!isset($array['charset'])) $array['charset'] = 'utf8';

				$a="{$array['driver']}:host={$array['host']};port={$array['port']};dbname={$array['dbname']}";
				self::$db=new \PDO($a, $array['username'], $array['password']);
				if(self::$db){
					self::$db->exec("SET CHARACTER SET {$array['charset']}");
			        self::$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	    			self::$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);		
				}
			} catch (\PDOException $e) {
				echo $e->getMessage();
				return false;
			}
		}
		return new self;
	}
	private static function table(){
		$name = get_called_class();
		if(!isset(self::$list[$name])){
			$b = preg_replace_callback('/[A-Z]+[a-z]+/', function($m){return strtolower($m[0]).'_';}, $name);
			$b = rtrim($b, '_');
			self::$list[$name] = $b;
		}
		return self::$list[$name];
	}
	public static function query($sql='', $data=[]){
		try{
			if($db=self::$db){
				$q = $db->prepare($sql);
				$q->setFetchMode(\PDO::FETCH_OBJ);
				$db->beginTransaction();
				if(preg_match('/INSERT INTO/', $sql)) {
					if(count($data)) $q->execute($data); else $q->execute();
					self::$insert_id = $db->lastInsertId();
					$db->commit();
					return self::$insert_id;
				}else{
					count($data)?$q->execute($data):$q->execute();
					$db->commit();
					return $q;
				}
			}
		}catch(PDOException $e){
			$db->rollback();
		}
	}
	private static function select($array=null){
		self::$table = self::table();
		if(is_numeric($array)){
			$sql='SELECT * FROM '.self::$table.' WHERE id=:id';
			self::$data['id']=$array;
		}elseif(is_array($array)){
			$sql = 'SELECT ';
			$sql.= isset($array['columns'])?$array['columns']:'*';
			$sql.= ' FROM '.self::$table;
			if(isset($array['join']))foreach($array['join'] as $table => $on)$sql.=" LEFT JOIN {$table} ON {$on}";
			if(isset($array['where']))$sql.=' WHERE '.$array['where'];
			if(isset($array['limit']))$sql.=' LIMIT '.$array['limit'];
			if(isset($array['order']))$sql.=' ORDER BY '.$array['order'];
			if(isset($array['bind']))self::$data=$array['bind'];
		}else $sql = 'SELECT * FROM '.self::$table;
		return self::query($sql, self::$data);
	}
	public static function findFirst($array=null){
    	if(!self::$db) return false;
		return self::select($array)->fetch();
	}
	public static function find($array=null){
    	if(!self::$db) return false;
		return self::select($array)->fetchAll();
	}

	public function insert() {
		self::$table = self::table();
    	$sql = 'INSERT INTO '.self::$table. '(';
    	$column = $value = [];
    	$array = (array)($this);
    	foreach ($array as $c => $v) {
    		$column[] = $c;
    		$value[] = ":$c";
    	}
    	$sql.= implode(',', $column).') VALUES('.implode(',', $value).')';
		$this->id = self::query($sql, $array);
		return $this->id;
    }
    public function update($where=[]) {
    	$sql = 'UPDATE '.self::$table.' SET ';
    	$array = (array)$this; $i=0;
    	foreach ($array as $key => $value) {
    		if($value) $sql.= "$key=:$key,";
    		else unset($array[$key]);
    	}
    	$sql = rtrim($sql, ',');
    	foreach($where as $key => $value) {
    		$sql.= ($i==0)?" WHERE $key=:$key":" AND $key=:$key";
    		$array[$key]=$value; 
    		$i++;
    	}
    	return self::query($sql, $array);
    }
    public function save()
    {
    	if(!self::$db) return false;
		self::$table = self::table(); $array = $this->array; unset($this->array);
    	if(count($array)){
    		if(is_numeric($array)) return $this->update(array('id'=>$array));
    		else return $this->update($array);
    	}else return $this->insert();
    }
    public function delete()
    {
    	if(!self::$db) return false;
		self::$table = self::table(); $array = count($this->array)?$this->array:(array)$this;
		$sql = 'DELETE FROM '.self::$table; $i=0;
		foreach ($array as $key => $value) {
    		if($value){$sql.= ($i==0)?" WHERE $key=:$key":" AND $key=:$key"; $i++;}
    		else unset($array[$key]);
		}
		return self::query($sql, $array);
    }
}
