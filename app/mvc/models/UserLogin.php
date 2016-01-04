<?php
use Truvu\Db\Mysql;
class UserLogin extends Mysql
{
	public $id;
	public $email;
	public $phone;
	public $pass;
	public $code;
	public $lang;
}
