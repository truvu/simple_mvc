<?php
use Truvu\Db\Mysql as DBMysql;

class User extends DBMysql
{
    public $id;
    public $name = null;
    public $fname;
    public $lname;
    public $avatar;
    public $sex;
}
