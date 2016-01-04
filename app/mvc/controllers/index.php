<?php
use Truvu\Mvc\Controller;

class IndexController extends Controller
{
	public function index(){
		$this->view->users = User::find(1);
		$this->view->title = 'Home Page';
		$this->view->render('index');
	}
}

?>