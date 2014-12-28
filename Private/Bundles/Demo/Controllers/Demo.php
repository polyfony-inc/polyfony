<?php

use Polyfony as pf;

// new example class to realize tests
class DemoController extends pf\Controller {

	public function preAction() {
		
		pf\Response::setMetas(array('title'=>'Bundles/Demo'));
		pf\Response::setAssets('css','//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css');
		
	}

	public function welcomeAction() {
		// view the main index/welcome page
		$this->view('Index');
	}
	
	public function indexAction() {
		// view the main demo index
		$this->view('Demo');	
	}
	
	public function loginAction() {	
		$this->view('Login');
	}
	
	public function secureAction() {	
		pf\Security::enforce();
		$this->view('Secure');
	}
	
	public function localesAction() {
		$this->view('Locales');		
	}
	
	public function databaseAction() {
		// demo query
		$this->Results = pf\Database::query()
			->select()
			->from('Accounts')
			->where(array(
				'id_level'=>1
			))
			->limitTo(0,5)
			->execute();
			
		$this->view('Database');		
	}
	
	public function responseAction() {
		$this->view('Response');		
	}
	
	public function requestAction() {
		$this->view('Request');		
	}
	

}

?>
