<?php

// from the framework
use \Polyfony\{ 
	Exception, Security, Response, Request, Element,
	Router, Form, Config, Logger, Controller,
	Form\Captcha, Form\Token
};

// from the models
use \Models\{ Accounts, Emails };

// from the vendor
use \Bootstrap\{ Modal, Alert,
	Alert\Success as OK,
	Alert\Failure as KO
};

// from the vendor
use \Illuminate\Support\Arr;

// from the vendor
use \Google\{ Map, Photo, Geocoder };

// new example class to realize tests
class DemoController extends Controller {

	public function before() {

		// set some common metas and assets
		Response\HTML::set([
			'links'		=>Config::get('response', 'links'),
			'scripts'	=>Config::get('response', 'scripts'),
			'metas'		=>[
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

	public function welcome() {

		// view the main index/welcome page
		$this->view('Index');
	}
	
	public function index() {

		// allow the framework to cache that page for 24 hours
		//	Response::enableOutputCache(24);
		// but forbid the browser to do so (allows us to purge the cache earlier)
		//	Response::disableBrowserCache();

		// if you need to manually create accounts
		// before having  built a CRUD for accounts
		// feel free to create them as bellow
		
		// Accounts::create([
		// 	'id'			=>1,
		// 	'is_enabled'	=>1,
		// 	'creation_date'	=>time()
		// 	'login'			=>'root@domain.local',
		// 	'password'		=>Security::getPassword('toor'),
		// ]);

		/*
		$result = \Polyfony\Database::query()
			->query(
				'SELECT SUM(is_enabled * id) as messedup '.
				'FROM Accounts'
			)
			->first()
			->execute();

		Logger::debug('PassThru', $result);

		*/

		// view the main demo index
		$this->view('Demo');
	
	}

	public function emails() {

		// if you've posted the form
		if(Request::isPost()) {

			// prevent double-posting
			Token::enforce();

			// create a new email object
			(new Emails)
				->set([
					'to'		=>Request::post('to_email'), // this is validated by PHPMailer
					'reply_to'	=>'someone@somewhere.com',
					'subject'	=>'Look at this nice README file !',
					'format'	=>'html',
					'charset'	=>'utf-8',
					'view'		=>'Demo',
					'css'		=>['Demo'],
					'body'		=>[
						'number'	=>898724,
						'names'		=>['John','Mike','Tom','Steeve']
					],
					'files_array'	=>['../README.md'=>'README.md']
				])
				->save() ? // this instanciates and save basic bootstrap alerts
					(new OK) : 
					(new KO); 

		}

		// get the list of all emails stored in the database
		$this->view('Emails', [
			'emails'=>Emails::search()
		]);

	}

	public function router() {

		// generating an url using its route
		$this->view('Router');

	}


	public function login() {	

	//	(new Models\Accounts(1))->setPassword('toor')->save();

		// add a notice
		(new Alert([
			'message'	=>'A default account is provided',
			'footer'	=>'That account\'s login is : ' . 
				(new \Models\Accounts(1))
					->get('login') .
			 ' its password is toor. You can change it from the database tab of this demo'
		]))->save();

		// view
		$this->view('Login');
	}
	
	public function secure() {	

		// enforce XSS protection for this action
		Token::enforce();

		// enforce Captcha protection for this action
		Captcha::enforce();

		// enforce security for this action
		Security::authenticate();

		$account = Security::getAccount();

		Logger::debug(
			'Permissions', 
			$account->getPermissions(true)
		);

		Logger::debug(
			'Roles', 
			$account->getRoles()
		);

		// pass some info to the view
		$this->view('Secure', [
			'login'			=>$account->get('login'),
			'id'			=>$account->get('id'),
			'permissions'	=>$account->getPermissions(true),
			'roles'			=>$account->getRoles(),
			'accounts'		=>Accounts::all()
		]);
	}

	public function disconnect() {

		// close the opened session
		Security::disconnect();

	}
	
	public function locales() {
		$this->view('Locales');		
	}

	public function logs() {

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
	
	public function database() {
		
		// this should obviously be a constant of the Accounts class
		$minimumPasswordLength = 6;

		// retrieve specific account
		// in this case, by it's id
		// but you could also pass ['login'=>'root@domain.com'] to retrieve it
		$rootAccount = new Accounts(1);

		if(Request::isPost()) {

			// CSRF protection
			Token::enforce();

			// update some fields
			// contrary to ->set(à, ->oset() [OnlySet] 
			// only sets a subset of the provided array
			$rootAccount->oset(
				Request::post('Accounts'), 
				[
					'login', 
					'is_enabled',
					'is_expiring_on',
					'firstname',
					'lastname'
				]
			);

			// in trusted cases you can do
			// $rootAccount->set(Request::post('Accounts'));
			// which updates all columns
			// though this should be kept for super admins, and you could overide the id and a bunch of stuff.

			if(
				// if the password is to be updated
				Request::post('password') && 
				// basic "password policy" would be 6 chars here
				strlen(Request::post('password')) >= $minimumPasswordLength
			) {
				// update the password (Accounts are special object, the password gets hashed automatically)
				$rootAccount->setPassword(Request::post('password'));
			}
			// save it and depending on the success of the operation, put an alert in the flashbag
			$rootAccount->save() ? 
				(new OK)->save() : 
				(new KO)->save();

		}
		
		// simple view	
		$this->view('Database', [
			'minimumPasswordLength'		=>$minimumPasswordLength,
			'rootAccount'				=>$rootAccount,
			'accounts'					=>Accounts::_select()->limitTo(0,5)->execute(),
			'allAccounts'				=>Accounts::all(),
			'recentlyCreatedAccounts'	=>Accounts::recentlyCreated(),
			'disabledAccounts'			=>Accounts::disabled(),
		//	'accountsWithErrors'		=>Accounts::withErrors()
		]);		
	}
	
	public function response() {
		
		// new response setters shortcuts
		Response::set([
			'status'	=>202,
			'metas'		=>['description'	=>'An awesome page'],
			'headers'	=>['X-Knock-Knock'	=>'Who\'s there ?']
		]);

		// push an image to preload using HTTP/2
	/*	Response::push([
			'https://avatars0.githubusercontent.com/u/36459871'=>'image'
		]);*/
		
		// simply import the view
		$this->view('Response');
	}
	
	public function request() {
		
		// this will make sure a CSRF token is present, and is valid (in case of a post)
		Token::enforce();

		// check if something has been posted
		Request::isPost() ? 
			(new Alert([
				'message'	=>'You use ' . Request::server('HTTP_USER_AGENT') . ' and posted the following string',
				'footer'	=>Request::post('test')
			]))->save() : null;

		
		// use the view
		$this->view('Request');		
	}
	
	public function exception() {
		
		// enhanced exceptions with automatic HTTP status code and formatting using the « exception » route declared in the tools bundle
		Throw new Exception('This is a custom exception with proper status code',502);
		
	}

	public function json() {

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

	public function vendorBootstrap() {

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

		$okAlert = new OK;
		$koAlert = new KO;

		$modal = new Modal;
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
			->adopt($okAlert)
			->adopt($koAlert)
			->adopt($modal);

	}

	public function vendorGoogle() {

		echo new Element('h1', ['text'=>'Demo of Vendor/Google']);
		echo new Element('p', ['text'=>'Please note that if you get errors, it is because you should provide a Google API key.']);
		// address geocoder
		$address = 'Arc de triomphe, Paris';
		echo new Element('code', ['text'=>"(Google\Geocoder)->setAddress('{$address}')->getPosition()"]);
		var_dump(
			(new Geocoder)
				->setAddress($address)
				->getPosition()
		);
		
		echo new Element('hr');

		// reverse geocoder
		echo new Element('code', ['text'=>"(Google\Geocoder)->setPosition('48.873','2.292')->getAddress()"]);
		var_dump(
			(new Geocoder)
				->setPosition(48.873,2.292)
				->getAddress()
		);

		echo new Element('hr');

		// google map
		echo new Element('code', ['text'=>"new Google\Map('roadmap',400,12, 48.873, 2.292)"]);
		echo new Element('br');

		$map = new Map('roadmap',400,12, 48.873, 2.292);
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

		$photo = (new Photo(400, 90, 10))
			->position(48.873,2.292);
		echo new Element('a', [
			'href'	=>$photo->url(),
			'target'=>'_blank',
			'text'	=>'Open streetview image',
			'class'	=>'btn btn-outline-primary'

		]);


	}
	

}

?>
