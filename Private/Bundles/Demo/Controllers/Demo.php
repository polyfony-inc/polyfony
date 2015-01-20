<?php

use Polyfony as pf;

// new example class to realize tests
class DemoController extends pf\Controller {

	public function preAction() {
		
		// set some common metas and assets
		pf\Response::setMetas(array('title'=>'Bundles/Demo'));
		pf\Response::setAssets('css','//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css');
		
		// get the currently browsed tab or null if none
		// this doesn't sounds safe, using a get parameter, right ?
		// actually it is, since the route won't match types that are not in that route's restrictions
		$this->CurrentTab = Polyfony\Request::get('type', null);

		// share a header
		$this->view('Header');
		
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

		// add a notice
		$this->Notice = new pf\Notice('only one account exists by default : root/toor','Notice :');

		// build input field
		$this->LoginInput = pf\Form::input(pf\Config::get('security','login'), null, array(
			'class'			=>'form-control',
			'id'			=>'inputLogin',
			'placeholder'	=>'Email'
		));;
		
		// build input field
		$this->PasswordInput = pf\Form::password(pf\Config::get('security','password'), null, array(
			'class'			=>'form-control',
			'id'			=>'inputPassword',
			'placeholder'	=>'*************'
		));;
		
		// cache the page for 24 hours
		pf\Response::enableOutputCache(24);

		// view
		$this->view('Login');
	}
	
	public function secureAction() {	

		// if the « something » parameter from the route is « exit », close our session
		!pf\Request::get('something') == 'exit' ?: pf\Security::disconnect();

		// enforce security for this action
		pf\Security::enforce();

		// grab some informations
		$this->Id 		= pf\Security::get('id');
		$this->Login 	= pf\Security::get('login');
		$this->Level 	= pf\Security::get('id_level');
		$this->Modules 	= implode(', ',pf\Security::get('modules_array'));

		// normal view
		$this->view('Secure');
	}
	
	public function localesAction() {
		$this->view('Locales');		
	}
	
	public function databaseAction() {
		
		// retrieve specific account
		$this->RootAccount = new pf\Record('Accounts',1);
		// change something
		$this->UpdateStatus = $this->RootAccount
			->set('last_login_date',rand(time()-1/10*time(),time()+1/10*time()))
			->set('password',pf\Security::getPassword('toor'))
			->save();
		
		// create a new account
		$this->NewAccount = new pf\Record('Accounts');
		// set something
		$this->NewAccount
			->set('login','test')
			->set('id_level','5')
			->set('last_failure_date',time());
		// save the record
		$this->CreateStatus = $this->NewAccount->save();
		// delete the new account
		$this->DeleteStatus = $this->NewAccount->delete();
		
		// demo query
		$this->Accounts = pf\Database::query()
			->select()
			->from('Accounts')
			->where(array(
				'id_level'=>1
			))
			->limitTo(0,5)
			->execute();
		
		// demo query from a model
		$this->AnotherList = Bundles\Demo\Model\Accounts::all();
		$this->AnotherList = Bundles\Demo\Model\Accounts::recentlyCreated();
		$this->AnotherList = Bundles\Demo\Model\Accounts::disabled();
		$this->AnotherList = Bundles\Demo\Model\Accounts::forcedRecently();
		
		// simple view	
		$this->view('Database');		
	}
	
	public function responseAction() {
		// add the JS portion of bootstrap
		pf\Response::setAssets('js',array(
			'//code.jquery.com/jquery-1.11.2.min.js',
			'//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js'
		));
		// add some metas
		pf\Response::setMetas(array('description'=>'An awesome page'));
		// manually change the status
		pf\Response::setStatus(202);
		// set manual header
		pf\Response::setHeaders(array('X-Knock-Knock'=>'Who\'s there ?'));
		// simply import the view
		$this->view('Response');		
	}
	
	public function requestAction() {
		
		// check if something has been posted
		$this->Feedback = pf\Request::isPost() ? new pf\Notice\Success('You posted : '. pf\Request::post('test'),'Congrats!') : null;
		
		// create a new input, using posted data if available
		$this->InputExample = pf\Form::input(
			'test',
			pf\Request::post('test',null),
			array(
				'placeholder'	=>'Type something in there',
				'class'			=>'form-control',
				'id'			=>'exampleField'
			)
		);
		
		// use the view
		$this->view('Request');		
	}
	
	public function exceptionAction() {
		
		// enhanced exceptions with automatic HTTP status code and formatting using the « exception » route declared in the tools bundle
		Throw new pf\Exception('This is a custom exception with proper status code',502);
		
	}

	public function jsonAction() {

		pf\Response::setType('json');
		pf\Response::setContent(array(
			'edible'=>array('Hoummus','Mango','Peach','Cheese'),
			'not_edible'=>array('Dog','Cow','Rabbit','Lizard')
		));
		pf\Response::render();

	}
	

}

?>
