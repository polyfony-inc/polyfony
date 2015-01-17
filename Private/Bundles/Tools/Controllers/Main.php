<?php


use Polyfony as Pf;

// new example class to realize tests
class MainController extends Pf\Controller {

	public function preAction() {
		
		Pf\Response::setMetas(array('title'=>'Bundles/Tools'));
		Pf\Response::setAssets('css','//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css');
		
	}

	public function indexAction() {

		// view the main index/welcome page
		$this->view('Index');
		
	}
	
	public function generateSymlinksAction() {

	}

	public function generateBundleAction() {

		// generate bundle input
		$this->bundleInput = Pf\Form::input(
			'bundle', 
			Pf\Request::post('bundle'), 
			array(
				'class'			=>'form-control',
				'placeholder'	=>'Mandatory'
			)
		);

		// generate table input
		$this->tableInput = Pf\Form::input(
			'table', 
			Pf\Request::post('table'), 
			array(
				'class'			=>'form-control',
				'placeholder'	=>'Mandatory'
			)
		);

		// if we posted our crud generation form
		if(Pf\Request::isPost()) {

			// if both parameters are set
			if(Pf\Request::post('bundle') && Pf\Request::post('table')) {




				// set a notice according to the status
				$this->Notice = new Pf\Notice\Success('Files have been generated', 'Success!');

			}
			// parameters are missing
			else {
				// show a notice
				$this->Notice = new Pf\Notice\Danger('Parameters are missing', 'Error!');
			}
		}

		// pass to the form view
		$this->view('GenerateBundle');

	}

	// purge cached elements
	public function purgeCacheAction() {

		// for each file in the cache folder
		foreach(Pf\Filesystem::ls('../Private/Store/Cache/') as $full_path => $name) {
			// if the file is normal
			if(Pf\Filesystem::ifFile($full_path)) {
				// remove that file
				Pf\Filesystem::remove($full_path);
			}
		}

		// redirect to the tools home page
		Pf\Response::setRedirect('/tools/');

	}

	// check the configuration of the framework, to see if everything is ok
	public function checkConfigurationAction() {

		// check for the presence of certain critical configuration parameters
		// ---

	}

}

?>
