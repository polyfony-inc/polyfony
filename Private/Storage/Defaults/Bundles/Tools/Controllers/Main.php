<?php


use Polyfony as Pf;

// new example class to realize tests
class MainController extends Pf\Controller {

	public function preAction() {
		
		Pf\Response::setMetas(array('title'=>'Bundles/Tools'));
		Pf\Response::setAssets('css','https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
		
	}

	public function indexAction() {

		// view the main index/welcome page
		$this->view('Index');
		
	}

	// cleanup the framework before a commit
	public function commitAction() {

		// vacuum the database
		$this->vacuumDatabaseAction();

		// purge the main cache
		$this->purgeCacheAction();

	}
	
	public function generateSymlinksAction() {

		// has error
		$has_error = false;
		// for each bundle
		foreach(Pf\Bundles::getAvailable() as $bundle_name) {
			// get assets for that bundle
			foreach(Pf\Bundles::getAssets($bundle_name) as $assets_type => $assets_path) {
				// get the correct relativeness
				$assets_path = "../../{$assets_path}";
				// set the root path
				$assets_root_path = "./Assets/{$assets_type}/";
				// create the public root path
				Pf\Filesystem::mkdir($assets_root_path, 0777, true) ?: $has_error = true;
				// set the symlink 
				$assets_symbolic_path = $assets_root_path . $bundle_name;
				// if the symlink does not already exists
				if(!Pf\Filesystem::isSymbolic($assets_symbolic_path, true)) {
					// create the symlink
					Pf\Filesystem::symlink($assets_path, $assets_symbolic_path, true) ?: $has_error = true;
				}
			}
		}

		// if the request is from a command line script
		if(pf\Request::isCli()) {
			// output some json
			pf\Response::setType('json');
			pf\Response::setContent(!$has_error);
			pf\Response::render();
		}
		// the request is done manually thru the web
		else {
			// set a notice depending on the presence of errors
			$this->notice = $has_error ? 
				new Bootstrap\Alert('danger','Error!','Please check the permissions and folder structure') :
				new Bootstrap\Alert('success','Success!','Symlinks have been created');
			// view the main index
			$this->view('Index');
		}

	}

	// purge cached elements
	public function purgeCacheAction() {

		// has error
		$has_error = false;
		// for each file in the cache folder
		foreach(Pf\Filesystem::ls(Pf\Config::get('cache', 'path'), true) as $full_path => $name) {
			// if the file is normal
			if(Pf\Filesystem::isFile($full_path, true) && Pf\Filesystem::isNormalName($name)) {
				// remove that file
				if(!Pf\Filesystem::remove($full_path, true)) {
					// set the error marker
					$has_error = true;
				}
			}
		}

		// set a notice depending on the presence of errors
		$this->notice = $has_error ? 
			new Bootstrap\Alert('Cache directory has not been emptied', 'Error!') :
			new Bootstrap\Alert('Cache directory has been emptied', 'Success!');

		// view the index
		$this->view('Index');

	}

	// clean the database
	public function vacuumDatabaseAction() {

		// close all sessions
		Pf\Database::query()
			->update('Accounts')
			->set(array(
				'session_expiration_date'	=>null,
				'session_key'				=>null,
				'last_login_origin'			=>null,
				'last_login_agent'			=>null,
				'last_login_date'			=>null,
				'last_failure_origin'		=>null,
				'last_failure_agent'		=>null,
				'last_failure_date'			=>null,
			))->execute();

		// remove all logs
		Pf\Database::query()->delete()->from('Logs')->execute();

		// remove all emails
		Pf\Database::query()->delete()->from('Mails')->execute();

		// remove all Store\Database record
		Pf\Database::query()->delete()->from('Store')->execute();

		// vacuum the database
		Pf\Database::query()->query('vacuum')->execute();

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

		// check if the database is writable
		if(!is_writable(Pf\Config::get('database', 'database'))) {
			// add a notice
			$this->errors->adopt(new Pf\Element('li',array('class'=>'list-group-item','text'=>'The database is not writable')));
		}

		$this->notice = $this->errors ? 
			new Bootstrap\Alert('danger',null,'Your configuration contains warnings or errors') :
			new Bootstrap\Alert('success',null,'Your configuration seems to be OK');

		// view the index
		$this->view('Index');

	}

}

?>
