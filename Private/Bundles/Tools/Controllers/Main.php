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

		// add a success notice
		$this->notice = new Pf\Notice\Success('Symlinks have been created', 'Success!');

		// view the main index
		$this->view('Index');

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
				$this->notice = new Pf\Notice\Success('Files have been generated', 'Success!');

			}
			// parameters are missing
			else {
				// show a notice
				$this->notice = new Pf\Notice\Danger('Parameters are missing', 'Error!');
			}
		}

		// pass to the form view
		$this->view('GenerateBundle');

	}

	// purge cached elements
	public function purgeCacheAction() {

		// has error
		$has_error = false;
		// for each file in the cache folder
		foreach(Pf\Filesystem::ls('../Private/Storage/Cache/') as $full_path => $name) {
			// if the file is normal
			if(Pf\Filesystem::isFile($full_path) && Pf\Filesystem::isNormalName($full_path)) {
				// remove that file
				if(!Pf\Filesystem::remove($full_path)) {
					// set the error marker
					$has_error = true;
				}
			}
		}

		// set a notice depending on the presence of errors
		$this->notice = $has_error ? 
			new Pf\Notice\Danger('Cache directory has not been emptied', 'Error!') :
			new Pf\Notice\Success('Cache directory has been emptied', 'Success!');

		// view the index
		$this->view('Index');

	}

	// check the configuration of the framework, to see if everything is ok
	public function checkConfigurationAction() {

		// list of errors 
		$this->errors = new Pf\Element('ul', array('class'=>'list-group'));

		// check that the security salt has been changed
		if(Pf\Config::get('security', 'salt') == '0X6B2HS71JQNWDH68700QKPWANY') {
			// add a notice
			$this->errors->adopt(new Pf\Element('li',array('class'=>'list-group-item','text'=>'You should change the [security] salt in Config.ini')));
		}

		// check that the keys salt has been changed
		if(Pf\Config::get('keys', 'salt') == 'VF6RV2087B0D6GJ*Tg6!Smx-2dS') {
			// add a notice
			$this->errors->adopt(new Pf\Element('li',array('class'=>'list-group-item','text'=>'You should change the [keys] salt in Config.ini')));
		}

		$this->notice = $this->errors ? 
			new Pf\Notice\Danger('Your configuration contains warnings or errors', 'Error!') :
			new Pf\Notice\Success('Your configuration seems to be OK', 'Success!');

		// view the index
		$this->view('Index');

	}

}

?>
