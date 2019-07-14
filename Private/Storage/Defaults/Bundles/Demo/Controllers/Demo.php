<?php

use Polyfony as pf;
use Polyfony\Exception as Exception;
use Polyfony\Security as Security;
use Polyfony\Response as Response;
use Polyfony\Request as Request;
use Polyfony\Element as Element;
use Polyfony\Router as Router;
use Polyfony\Form as Form;
use Polyfony\Config as Config;
use Polyfony\Logger as Logger;
use Polyfony\Form\Captcha as Captcha;
use Polyfony\Form\Token as Token;
use Models\Accounts as Accounts;

use Bootstrap\Alert as Alert;
use Bootstrap\Alert\Success as OK;
use Bootstrap\Alert\Failure as KO;

// new example class to realize tests
class DemoController extends pf\Controller {

	public function preAction() {

		// set some common metas and assets
		Response\HTML::set([
			'links'	=>[
				'//maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css',
				'//use.fontawesome.com/releases/v5.0.6/css/all.css'
			],
			'scripts'	=>[
				'//cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js',
				'//maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js'
			],
			'metas'	=>[
				'title'			=>'Bundles/Demo',
				'description'	=>'Demo bundle with example of most features'
			]
		]);
		
		// allow the framework to cache that page for 24 hours
//		Response::enableOutputCache(24);
		// but forbid the browser to do so (allows us to purge the cache earlier)
//		Response::disableBrowserCache();

		// share a header
		$this->view('Header');
		
	}

	public function welcomeAction() {

		// view the main index/welcome page
		$this->view('Index');
	}
	
	public function indexAction() {

		// allow the framework to cache that page for 24 hours
		//	Response::enableOutputCache(24);
		// but forbid the browser to do so (allows us to purge the cache earlier)
		//	Response::disableBrowserCache();

		// if you need to manually create accounts
		// before having  built a CRUD for accounts
		// feel free to create them as bellow
		
		// Accounts::create([
		// 	'id'			=>1,
		// 	'id_level'		=>1,
		// 	'is_enabled'	=>1,
		// 	'login'			=>'root@domain.local',
		// 	'password'		=>Security::getPassword('toor'),
		// 	'modules_array'	=>[],
		// 	'creation_date'	=>time()
		// ]);

		// view the main demo index
		$this->view('Demo');
	
	}

	public function emailsAction() {

		// if you've posted the form
		if(Request::isPost()) {

			// prevent double-posting
			Token::enforce();

			// create a new email object
			(new Models\Emails)
				->set([
					'to'			=>Request::post('to_email'), // this is validated by PHPMailer
					'reply_to'		=>'someone@somewhere.com',
					'subject'		=>'Look at this nice README file !',
					'body'			=>'It\'s in the attachment',
					'format'		=>'text',
					'charset'		=>'utf-8',
					'files_array'	=>['../README.md'=>'README.md']
				])
				->save() ? // this instanciates and save basic bootstrap alerts
					(new OK) : 
					(new KO); 

		}

		// get the list of all emails stored in the database
		$this->emails = Models\Emails::search();

		$this->view('Emails');

	}

	public function routerAction() {

		// generating an url using its route
		$this->url = Router::reverse('demo', ['type'=>'locales'], true, true);
		$this->view('Router');

	}


	public function loginAction() {	

		// add a notice
		(new Bootstrap\Alert([
			'message'	=>'A default account is provided',
			'footer'	=>'That account\'s login is : ' . 
				(new Models\Accounts(1))
					->get('login') .
			 ' its password is toor. You can change it from the database tab of this demo'
		]))->save();

		// view
		$this->view('Login');
	}
	
	public function secureAction() {	

		// enforce XSS protection for this action
		Token::enforce();

		// enforce Captcha protection for this action
		Captcha::enforce();

		// enforce security for this action
		Security::enforce();

		// grab some informations
		$this->Id 		= Security::get('id');
		$this->Login 	= Security::get('login');
		$this->Level 	= Security::get('id_level');

		// normal view
		$this->view('Secure');
	}

	public function disconnectAction() {

		// close the opened session
		Security::disconnect();

	}
	
	public function localesAction() {
		$this->view('Locales');		
	}

	public function logsAction() {

		Logger::debug('This is a log event that will not be logged in Prod, using default parameters');

		Logger::info('This is a generic purpose info level log');

		Logger::notice(
			'Creating a dummy account, but without saving it', 
			(new Accounts)
				->set(['login'=>'test@test.com'])
		);

		Logger::warning(
			'Kind of serious log event, such as someone trying to log in with a wrong password',
			908729038
		);

		Logger::critical(
			'Now a hardcore serious issue',
			[['tri','fon','lehérisson'],['flip','flap','lagirafe']]
		);

		$this->view('Logs');		
	}
	
	public function databaseAction() {
		
		$this->MinPasswordLength = 6;

		// retrieve specific account
		$this->RootAccount = new Accounts(1);

		if(Request::isPost()) {

			// CSRF protection
			Token::enforce();

			// update some fields
			$this->RootAccount->set([
				'login'						=>Request::post('Accounts')['login'],
				'id_level'					=>Request::post('Accounts')['id_level'],
				'is_enabled'				=>Request::post('Accounts')['is_enabled'],
				'account_expiration_date'	=>Request::post('Accounts')['account_expiration_date']
			]);

			// in trusted cases you can do
			// $this->RootAccount->set(Request::post('Accounts'));
			// which updates all columns
			// though this should be kept for super admins, and you could overide the id and a bunch of stuff.
			// an alternative to that is defining a setSafely()
			// which will unset a number of variables before doing the actual ->set

			if(
				// if the password is to be updated
				Request::post('password') && 
				// basic "password policy" would be 6 chars here
				strlen(Request::post('password')) >= $this->MinPasswordLength
			) {
				// update the password
				$this->RootAccount->set([
					// convert it to a secured hash thru the Security class
					'password'=>Security::getPassword(Request::post('password'))
				]);
			}
			// save it and depending on the success of the operation, put an alert in the flashbag
			$this->RootAccount->save() ? 

				(new Bootstrap\Alert([
					'class'=>'success',
					'message'=>'Account modified'
				]))->save() : 
			
				(new Bootstrap\Alert([
					'class'=>'danger',
					'message'=>'Failed to modify account'
				]))->save();

		}

		// demo query
		$this->Accounts = Accounts::_select()
			->limitTo(0,5)
			->execute();
		
		// fully verbose/normal alternative
		// $this->Accounts = pf\Database::query()
		// 	->select()
		// 	->from('Accounts')
		// 	->limitTo(0,5)
		// 	->execute();

		// demo query from a model
		$this->AnotherList = Accounts::all();
		$this->AnotherList = Accounts::recentlyCreated();
		$this->AnotherList = Accounts::disabled();
		$this->AnotherList = Accounts::withErrors();
		
		// simple view	
		$this->view('Database');		
	}
	
	public function responseAction() {
		
		// new response setters shortcuts
		Response::set([
			'status'	=>202,
			'metas'		=>['description'	=>'An awesome page'],
			'headers'	=>['X-Knock-Knock'	=>'Who\'s there ?']
		]);
		
		// simply import the view
		$this->view('Response');
	}
	
	public function requestAction() {
		
		// this will make sure a CSRF token is present, and is valid (in case of a post)
		Token::enforce();

		// check if something has been posted
		Request::isPost() ? 
			(new Bootstrap\Alert([
				'message'	=>'You use ' . Request::server('HTTP_USER_AGENT') . ' and posted the following string',
				'footer'	=>Request::post('test')
			]))->save() : null;

		
		// use the view
		$this->view('Request');		
	}
	
	public function exceptionAction() {
		
		// enhanced exceptions with automatic HTTP status code and formatting using the « exception » route declared in the tools bundle
		Throw new Exception('This is a custom exception with proper status code',502);
		
	}

	public function jsonAction() {

		Response::set([
			'type'		=>'json',
			'content'	=>[
				'edible'	=>['Hoummus','Mango','Peach','Cheese'],
				'not_edible'=>['Dog','Cow','Rabbit','Lizard']
			]
		]);

		/*
		Alternatively you can also use this longer syntax

		Response::setType('json');
		Response::setContent([
			'edible'	=>['Hoummus','Mango','Peach','Cheese'],
			'not_edible'=>['Dog','Cow','Rabbit','Lizard']
		]);
		Response::render();
		*/

	}

	public function vendorBootstrapAction() {

		/*

		bootstrap/alert replaces the old polyfony/notice
		
		$container = new pf\Element('div',['class'=>'col-10 offset-1']);
		echo $container->adopt(new Polyfony\Notice\Success('Description', 'Title'));

		*/

		// an alert is created a stored, for being flashed at a later time
		(new Alert([
			'message'=>'Hi, I was created in a previous action, when you visited the Vendor/Bootstrap demo',
			'dismissible'=>true
		]))->save();

		// just a bootstrap container to align the alerts
		$container = new Element('div', ['class'=>'col-10 offset-1']);

		$successAlert = new Alert([
			'class'		=>'success',
			'title'		=>'Lorem ipsum',
			'message'	=>'Dolor sit amet sed ut perspicadis',
			'footer'	=>'Footer'
		]);

		$warningAlert = new Alert([
			'class'		=>'warning',
			'title'		=>'Dolor sit amet sed ut perspicadis',
			'footer'	=>'footer'
		]);

		$dangerAlert = new Alert([
			'class'		=>'danger',
			'message'	=>'Lorem ipsum, dolor sit amet',
			'dismissible'=>true
		]);

		$modal = new Bootstrap\Modal();
		$modal
			->setTitle([
				'text'=>'Which do you prefer ?'
			],'fa fa-car')
			->setBody([
				'html'=>'I mean <strong>good</strong> cars ?'
			])
			->addOption([
				'text'=>'Me likey Porsche',
				'class'=>'btn btn-secondary'
			],'fa fa-car')
			->addOption([
				'text'=>'Me likey Ferrari',
				'class'=>'btn btn-danger'
			],'fa fa-car')
			->addOption([
				'text'=>'Me likey McLaren',
				'class'=>'btn btn-warning'
			],'fa fa-car')
			->setTrigger([
				'text'=>' Do you like cars ?',
				'class'=>'btn btn-primary'
			], 'fa fa-car');

		// adopt the alerts and dump them
		echo $container
			->adopt($successAlert)
			->adopt($warningAlert)
			->adopt($dangerAlert)
			->adopt($modal);

	}

	public function vendorGoogleAction() {

		echo new Element('h1', ['text'=>'Demo of Vendor/Google']);
		echo new Element('p', ['text'=>'Please note that if you get errors, it is because you should provide a Google API key.']);
		// address geocoder
		$address = 'Arc de triomphe, Paris';
		echo new Element('code', ['text'=>"Google\Position::address('{$address}')"]);
		var_dump(
			Google\Position::address($address)
		);
		
		echo new Element('hr');

		// reverse geocoder
		echo new Element('code', ['text'=>"Google\Position::reverse('48.873','2.292')"]);
		var_dump(
			Google\Position::reverse(48.873,2.292)
		);

		echo new Element('hr');

		// google map
		echo new Element('code', ['text'=>"new Google\Map('roadmap',400,12, 48.873, 2.292)"]);
		echo new Element('br');

		$map = new Google\Map('roadmap',400,12, 48.873, 2.292);
		echo new Element('a', [
			'href'	=>$map->url(),
			'target'=>'_blank',
			'text'	=>'Open map image',
			'class'	=>'btn btn-outline-primary'
		]);

		echo new Element('hr');

		// google street view
		echo new Element('code', ['text'=>"new Google\Photo(400)->position(48.873,2.292)"]);
		echo new Element('br');

		$photo = (new Google\Photo(400, 90, 10))->position(48.873,2.292);
		echo new Element('a', [
			'href'	=>$photo->url(),
			'target'=>'_blank',
			'text'	=>'Open streetview image',
			'class'	=>'btn btn-outline-primary'

		]);


	}
	

}

?>
