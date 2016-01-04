<?php
namespace Truvu\Db;
/*
/	- find first: UserComment::findFirst(array('file'=>1, 'id'=>1));
/
/	- find:	UserComment::find(array('file'=>1, 'id'=>1), array('order'=>desc, 'limit'=>10));
/
/	- save: 
/		$comment = new UserComment(1);
/		$comment->user = 1;
/		$comment->content = "text content";
/	=> insert: $comment->save();
/	=> update: $comment->save($id);
/	
/	- delete: $comment = new UserComment(1);
/		+ file: $comment->delete();
/		+ row : $comment->delete($id);
*/

class File
{
	private static $dir;
	private $file;
	public function __construct($file=null){
		if($file!==null) $this->file = $file;
	}
	public static function connect($dir=null){
		self::$dir = $dir; return new self;
	}
	private static function file($id){
		return self::$dir.'/'.get_called_class()."/{$id}.json";
	}
	public static function has($id){
		return file_exists(self::file($id));
	}
	public static function findFirst($condition=array()){
		if(!isset($condition['file'])||empty($condition['file'])) return false;
		$file = self::file($condition['file']);
		$handle = fopen($file, 'r');
		if($handle){
			if(isset($condition['id'])){
				while($line = fgets($handle)){
			        if($line){
			        	$array = json_decode($line, true);
			        	if($condition['id']===$array['id']){
			        		$data = $array; fclose($handle); break; 
			        	}
			        }
			    }
			}else{
				while($line = fgets($handle)){
			        if($line){ $data = json_decode($line, true); fclose($handle); break; }
			    }
			}
			return isset($data)?(object)$data:false;
		}else return false;
	}
	public static function find($condition=array(), $more=array()){
		if(!isset($condition['file'])||empty($condition['file'])) return false;
		$file = self::file($condition['file']);
		$list = file($file); $i = count($list)-1; $start = 0;
		$end = isset($more['limit'])?$more['limit']:20;
		$array = array();
		if(isset($condition['id'])){
			$id = $condition['id'];
			if(is_array($id)){
				if($id[0]==='<'){
					for(; $i>=0; $i--){
						if($end===$start) break;
						$b = json_decode($list[$i]);
						if($b->id<$id[1]) array_push($array, $b);
						$start++;
					}
				}elseif($id[0]==='>'){
					for(; $i>=0; $i--){
						if($end===$start) break;
						$b = json_decode($list[$i]);
						if($b->id>$id[1]) array_push($array, $b);
						$start++;
					}
				}
			}elseif(is_numeric($id)){
				for(; $i>=0; $i--){
					$a = json_decode($list[$i]);
					if($id===$a->id) {
						$array = array($a);
						break;
					}
				}	
			}
		}else{
			for(; $i>=0; $i--){
				if($end===$start) break;
				array_push($array, json_decode($list[$i]));
				$start++;
			}
		}
		return count($array)?$array:false;
	}
	public function save($id=null){
		if($this->file==null) return false;
		$file = self::file($this->file);
		if($id===null){
			unset($this->file); 
			return file_put_contents($file, json_encode($this)."\n", FILE_APPEND);
		}elseif(is_bool($id)){
			unset($this->file);
			$json = file_get_contents($file);
			$row = json_decode($json, true);
			foreach($this as $key => $value){
	  			if(is_array($value)){
	  				if(isset($value['$push'])) array_push($row[$key], $value['$push']);
	  				elseif(isset($value['$pull'])) $row[$key] = array_diff($row[$key], array($value['$pull']));
	  				elseif(isset($value['$pullAll'])) $row[$key] = array_diff($row[$key], $value['$pullAll']);
	  				elseif(isset($value['$set'])){
	  					foreach($value['$set'] as $k => $v) $row[$key][$k] = $v;
	  				}else $row[$key] = $value;
	  			}else $row[$key] = $value;
	  		}
	  		return file_put_contents($file, json_encode($row));
		}else{
			$reading = fopen($file, 'r');
			$tmp = self::file($this->file.'-tmp');
			$writing = fopen($tmp, 'w');
			$replaced = false; unset($this->file);
			while(!feof($reading)){
				if($line = fgets($reading)){
					$row = json_decode($line, true);
				  	if($id===$row['id']){
				  		foreach($this as $key => $value){
				  			if(is_array($value)){
				  				if(isset($value['$push'])) array_push($row[$key], $value['$push']);
				  				elseif(isset($value['$pull'])) $row[$key] = array_diff($row[$key], $value['$pull']);
				  				elseif(isset($value['$set'])){
				  					foreach($value['$set'] as $k => $v) $row[$key][$k] = $v;
				  				}else $row[$key] = $value;
				  			}else $row[$key] = $value;
				  		}
				  		$line = json_encode($row);
				  		$replaced = true;
				  	}
				  	fputs($writing, $line);
				}
			}
			fclose($reading); fclose($writing);
			// might as well not overwrite the file if we didn't replace anything
			if($replaced) return rename($tmp, $file);
			else return unlink($tmp);
		}
	}
	public function delete($id=null){
		if($this->file==null) return false;
		$file = self::file($this->file);
		if($id===null) return unlink($file);
		else{
			$reading = fopen($file, 'r');
			$tmp = self::file($this->file.'-tmp');
			$writing = fopen($tmp, 'w');
			$replaced = false;
			while (!feof($reading)) {
				if($line = fgets($reading)){
				  	if(preg_match("#{$id}#", $line)){
				  		$line = '';
				  		$replaced = true;
				  	}
				  	fputs($writing, $line);	
				}
			}
			fclose($reading); fclose($writing);
			// might as well not overwrite the file if we didn't replace anything
			if($replaced) return rename($tmp, $file);
			else return unlink($tmp);
		}
	}
}

?>