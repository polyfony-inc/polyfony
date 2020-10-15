<?php

namespace Polyfony;

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Emails extends Entity {

	const recipients_types_to_phpmailer_methods = [
		'to'		=>'addAddress',
		'cc'		=>'addCC',
		'bcc'		=>'addBCC',
		'reply_to'	=>'addReplyTo'
	];

	public ?int $creation_date	= null;
	public ?int $sending_date	= null;
	public $body 				= '';
	public ?string $subject 	= '';
	public ?string $format 		= ''; // html of text
	public ?string $charset 	= ''; // utf8, etc.
	public ?string $from_name 	= '';
	public ?string $from_email 	= '';
	public $files_array 		= '[]';
	public $recipients_array 	= '{"to":[],"cc":[],"bcc":[],"reply_to":[]}';

	public function autoConfigure() :self {

		// fill voids with static configuration
		return $this
			->set([
				'from_email' 	=> $this->get('from_email') ?: 
					Config::get('email', 'from_email'),
				'from_name' 	=> $this->get('from_name') ?: 
					Config::get('email', 'from_name'),
				'charset' 		=> $this->get('charset') ?: 
					Config::get('email', 'default_charset'),
				'format' 		=> $this->get('format') ?: 
					Config::get('email', 'format'),
				'creation_date' => $this->get('creation_date', true) ?: 
					time(),
				'smtp' => [
					'host' => Config::get('email', 'smtp_host'),
					'user' => Config::get('email', 'smtp_user'),
					'pass' => Config::get('email', 'smtp_pass')
				]
			]);

	}

	private function render() :self {

		// if the view has already been rendered, or if there is no view
		if(
			isset($this->_['rendered']) || 
			!isset($this->_['view'])
		) {
			return $this;
		}

		// define a marker to benchmark the rendering
		$id_marker = Profiler::setMarker(
			'Emails.render.'.uniqid(), 
			'email',
			['Email'=>$this]
		);

		// render the PHP view 
		$this->renderView();

		// render the CSS (inlining)
		$this->renderCSS();

		// release the marker
		Profiler::releaseMarker($id_marker);

		// memorize
		$this->_['rendered'] = true;

		// allow chaining
		return $this;

	}

	public function renderView() :void {

		// start a new buffer for rending the email's view
		ob_start();
		// extract the variables for that view
		extract(isset($this->_['variables']) ? $this->_['variables']: []);
		// include the actual email's view
		include($this->_['view']);
		// get the rendered view
		$rendered_view = ob_get_contents();
		// terminate the buffer
		ob_end_clean();
		// update the body with the rendered view
		$this->set(['body'=>$rendered_view]);

	}

	public function renderCSS() :void {

		// if the email is html, and we have some css
		if(
			$this->isHTML() && 
			isset($this->_['css'])
		) {
			// inline the css into the email's body
			$this->set([
				'body'=>(new CssToInlineStyles)
					->convert(
						$this->get('body', true),
						$this->_['css']
					)
			]);
		}



	}

	private function setSMTP(array $parameters) :self {
		// change the smtp configuration
		$this->_['smtp'] = $parameters;
		// return self
		return $this;
	}

	private function setRecipient(
		string $type, 
		$email, 
		?string $name=null
	) :self {
		// array of recipients provided
		if(is_array($email)) {
			// for each recipients
			foreach($email as $individual_email) {
				// if we have only an email, set the name to null
				list(
					$individual_email, 
					$individual_name
				) = is_array($individual_email) ? 
					$individual_email : 
					[$individual_email, null];
				// recurse
				$this->setRecipient(
					$type, 
					$individual_email, 
					$individual_name
				);
			}
		}
		// single recipient provided
		else {
			// get the current list of recipients
			$recipients = $this->getRecipients();
			// add one
			$recipients[$type][$email] = $name;
			// push to the table
			$this->set([
				'recipients_array'=>$recipients
			]);
		}
		// return self
		return $this;
	}

	public function getRecipients(
		?string $which_ones = null
	) :array {

		return $which_ones ? 
			$this->get('recipients_array')[$which_ones] : 
			$this->get('recipients_array');

	}

	// override the default Record->set method 
	// to intercept fake columns
	public function set(
		$columns, 
		$ignored_value = null
	) :self {
		
		// if we are not passed an array
		if(is_array($columns)) {
			// for each column that has to be set
			foreach($columns as $column => $value) {
				// if we want to set a variable
				$this->setIncludingFakeColumns($column, $value);
			}
		}
		else {
			// if we want to set a variable
			parent::set($columns, $ignored_value);
		}
		// and still, return ourselves for chaining
		return $this;

	}

	

	// this is not to be called directly
	// it's only used to simplify the ->set() method
	private function setIncludingFakeColumns(
		string $column, 
		$value
	) :void {
		// if the body is an array, we assume it's a list of variables
		if($column == 'body' && is_array($value)) {
			$this->setVariables($value);
		}
		// allow to set the view 
		elseif($column == 'view') {
			$this->setView($value);
		}
		// allow to set multiple css 
		elseif($column == 'css' && is_array($value)) {
			$this->setCSS($value);
		}
		// allow to set a single css 
		elseif($column == 'css' && is_string($value)) {
			$this->setCSS([$value]);
		}
		// allow to change the smtp
		elseif($column == 'smtp') {
			$this->setSMTP($value);
		}
		// recipients settings
		elseif(in_array(
			$column, 
			['to','cc','bcc','reply_to']
		)) {
			$this->setRecipient($column, $value);
		}
		else {
			// otherwise set it normally
			parent::set([$column=>$value]);
		}
	}

	private function setView(
		string $view_name
	) :self {
		// prepare the view
		$view_path = Config::absolutizePath(
			"Private/Bundles/Emails/Views/{$view_name}.php"
		);
		// if the template file exists
		if(!file_exists($view_path)) {
			// throw an exeption
			Throw new Exception(
				'Emails->setView() the view file does not exist', 
				404
			);
		}
		$this->_['view'] = $view_path;
		return $this;
	}

	private function setVariables(
		array $placeholders_and_values
	) :self {

		// place the variable in a hidden variable container
		$this->_['variables'] = $placeholders_and_values;

		// allow for chaining
		return $this;

	}

	private function setCSS(
		array $stylesheets
	) :self {

		// initialize css container
		$this->_['css'] = '';


		foreach($stylesheets as $stylesheet) {
			// prepare the stylesheet
			$stylesheet_path = Config::absolutizePath(
				"Private/Bundles/Emails/Assets/Css/{$stylesheet}.css"
			);
			// if the stylesheet does not exist
			if(!file_exists($stylesheet_path)) {
				// throw an exeption
				Throw new Exception(
					'Emails->setStylesheets() the stylesheet does not exist', 
					404
				);
			}
			// append the css content to existing content
			$this->_['css'] .= ' ' . file_get_contents($stylesheet_path);

		}
		return $this;

	}

	public function getError() {
		// return the textual representation for the last error
		return isset($this->_['mailer']) ? 
			$this->_['mailer']->ErrorInfo : 
			'';
	}

	public function isSent() :bool {

		return $this->get('sending_date') ? true : false;

	}

	private function isHTML() :bool {

		return $this->get('format') == 'html';

	}

	public function save() :bool {

		// autoconfigure, so that no columns are left NULL
		$this->autoConfigure();

		// if no marker exists
		if(!isset($this->_['id_marker'])) {
			// place a marker
			$this->_['id_marker'] = Profiler::setMarker(null, 'email', ['Email'=>$this]);
		}

		// render if need be
		$this->render();

		// use normal saving of record class
		return parent::save();

	}

	public function send(
		?bool $save = false
	) :self {

		// autoconfigure
		$this->autoConfigure();

		// place a marker
		$id_marker = Profiler::setMarker(null, 'email', ['Email'=>$this]);

		// configure the php mailer object
		$this->configurePHPMailer();

		// render if need be
		$this->render();

		// try sending
		try {

			// update the sending date
			$this->set([
				// if it succeeds
				'sending_date' => $this->_['mailer']->Send() ? time() : null
			]);

		}
		// catch any exception
		catch (Exception $exception) {
			// log the error
			Logger::warning($exception->getMessage());
		}

		// release the marker
		Profiler::releaseMarker($id_marker);

		// if we asked to save the object of if we're from the database already
		// aka we have an id/primary key
		if($save || $this->get('id')) {
			// try to save
			if(!$this->save()) {
				// and throw an exception if we can't
				Throw new Exception(
					'Emails->save() failed', 
					500
				);
			}
		}

		// return ourselves
		return $this;

	}

	public function configurePHPMailer() :void {

		// instanciate a new phpmail object (allowing exception to be thrown)
		$this->_['mailer'] = new \PHPMailer\PHPMailer\PHPMailer(true);

		// configure the mailer from object config
		$this->_['mailer']->CharSet 	= $this->get('charset', true);		
		$this->_['mailer']->From 		= $this->get('from_email', true);
		$this->_['mailer']->FromName	= $this->get('from_name', true);
		$this->_['mailer']->Subject 	= $this->get('subject', true);
		$this->_['mailer']->Body 		= $this->get('body', true);

		// change the mailer engine if necessary
		$this->_['mailer']->Mailer 		= $this->_['smtp']['host'] ? 'smtp' : 'mail';
		$this->_['mailer']->SMTPAuth 	= $this->_['smtp']['user'] ? true : false;
		$this->_['mailer']->Host 		= $this->_['smtp']['host'];
		$this->_['mailer']->Username 	= $this->_['smtp']['user'];
		$this->_['mailer']->Password 	= $this->_['smtp']['pass'];

		// set the format of the mail
		$this->_['mailer']->isHTML($this->isHTML());
		
		// configure attachments
		$this->configurePHPMailerAttachments();
		// configure recipients
		$this->configurePHPMailerRecipients();
		
	}	

	private function configurePHPMailerAttachments() :void {

		// for each attachment
		foreach(
			$this->get('files_array') 
			as $path => $name
		) {
			// add to the mailer
			$this->_['mailer']->addAttachment($path, $name);
		}

	}

	// this applies options that are only used in development, or only in production  
	private function configurePHPMailerRecipients() :void {
		// for each category of recipients
		foreach($this->getRecipients() as $category => $recipients) {
			// for each recipients in this category
			foreach($recipients as $email => $name) {
				// deduce the proper add recipient method
				$addRecipientMethod = self::recipients_types_to_phpmailer_methods[$category];
				// add to the mailer as actual recipients or as header (hidden)
				Config::isProd() ? 
					$this
						->_['mailer']
						->$addRecipientMethod(
							$email, 
							$name
					) : 
					$this
						->_['mailer']
						->addCustomHeader(
							'X-'.ucfirst($category), 
							$email . ' ' . $name
					);
			}
		}
		// if we are in the development enviroment
		if(Config::isDev()) {
			// add the bypass address only
			$this->_['mailer']->addAddress(
				Config::get('email', 'bypass_email')
			);
		}
	}

	public function getDebugData() {
		return [
			'recipients'=>$this->get('recipients_array'),
			'files'		=>$this->get('files_array'),
			'from'		=>$this->get('from_email', true),
			'subject'	=>$this->get('subject', true),
			'format'	=>$this->get('format'),
			'smtp'		=>isset($this->_['smtp']) ? 
				$this->_['smtp'] : ['host'=>'','user'=>'','pass'=>'']
		];
	}

}

?>
