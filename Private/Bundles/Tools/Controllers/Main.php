<?php

use Polyfony as pf;

// new example class to realize tests
class MainController extends pf\Controller {

	public function preAction() {
		
		pf\Response::setMetas(array('title'=>'Bundles/Tools'));
		pf\Response::setAssets('css','//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css');
		
	}

	public function indexAction() {

		// view the main index/welcome page
		$this->view('Index');
		
	}
	

}

?>
