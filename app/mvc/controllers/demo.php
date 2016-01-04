<?php
use Truvu\Mvc\Controller;

class DemoController extends Controller
{
	public function chat(){
		$this->view->title = 'Chat example';
		$this->view->js = 'chat';
	}
	public function __destruct(){
		$this->view->render('demo');
	}
}
