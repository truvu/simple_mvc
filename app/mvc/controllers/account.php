<?php

class AccountController extends Controller
{
	public function login()
	{
		$email = $this->request->post('email');
		$pass = $this->request->post('pass');
		$type = $this->request->post('type');
		if(empty($email)||empty($pass)||empty($type)) return false;
		switch ($type) {
			case 'id':
				if(!is::id($email)) return false;
				if(is_string($email)) $email = (float)$email;
				$sql = 'SELECT id,pass,code FROM UserLogin WHERE id=:id';
				break;

			case 'email':
				if(!is::email($email)) return false;
				$sql = 'SELECT id,pass,code FROM UserLogin WHERE email=:id';
				break;

			case 'phone':
				if(!is::phone($email)) return false;
				$sql = 'SELECT id,pass,code FROM UserLogin WHERE phone=:id';
				break;

			case 'name':
				if(!is::username($email)) return false;
				$sql = 'SELECT u.id,u.pass,u.code FROM UserLogin AS u, User AS n WHERE n.name=:id';
				break;
			
			default: return false;
		}
		$user = $this->mysql->query($sql, array('id'=>$email))->fetch();
		if($user!==false){
			if(!is_numeric($user->id)) $user->id = (float)$user->id;
			if($this->security->checkHash($pass, $user->pass)){
				if($user->code) $array = array('error'=>true, 'type'=>'code');
				else $array = array('error'=>false, 'data'=>array('uid'=>$user->id, 'act'=>time()));
			}else $array = array('error'=>true, 'type'=>'pass');
		}else $array = array('error'=>true, 'type'=>$type);
		$this->response->setJsonContent($array);
		return $this->response;
	}

	public function confirm()
	{
		$email = $this->request->post('email');
		$code = $this->request->post('code');
		$type = $this->request->post('type');
		if(empty($email)||empty($code)||empty($type)) return false;
		if(!preg_match('/^[0-9]{5}$/', $code)) return false;
		switch ($type) {
			case 'email':
				if(!is::email($email)) return false;
				$sql = 'SELECT id,code FROM UserLogin WHERE email=:id';
				break;

			case 'phone':
				if(!is::phone($email)) return false;
				$sql = 'SELECT id,code FROM UserLogin WHERE phone=:id';
				break;
			
			default: return false;
		}
		$user = $this->mysql->query($sql, array('id'=>$email))->fetch();
		if($user!==false){
			if(!is_numeric($user->id)) $user->id = (float)$user->id;
			if($code==$user->code) $array = array('error'=>false, 'data'=>array('uid'=>$user->id, 'act'=>time()));
			else $array = array('error'=>true, 'type'=>'code');
		}else $array = array('error'=>true, 'type'=>$type);
		$this->response->setJsonContent($array);
		return $this->response;
	}
	public function forgot()
	{
		$email = $this->request->post('email');
		$type = $this->request->post('type');
		if(empty($email)||empty($type)) return false;
		switch ($type) {
			case 'email':
				if(!is::email($email)) return false;
				$sql = 'SELECT id,code FROM UserLogin WHERE email=:id';
				break;

			case 'phone':
				if(!is::phone($email)) return false;
				$sql = 'SELECT id,code FROM UserLogin WHERE phone=:id';
				break;
			
			default: return false;
		}
	}
	public function register()
	{
		# code...
	}
}
