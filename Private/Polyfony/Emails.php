<?php

namespace Polyfony;

class Emails extends Record {

	public $creation_date		= null;
	public $sending_date		= null;
	public $body 				= '';
	public $subject 			= '';
	public $format 				= ''; // html of text
	public $charset 			= ''; // utf8, etc.
	public $from_name 			= '';
	public $from_email 			= '';
	public $files_array 		= '[]';
	public $recipients_array 	= '{"to":[],"cc":[],"bcc":[],"reply_to":[]}';

	public function autoConfigure() :self {

		// fill voids with static configuration
		return $this
			->set([
				'from_email' => $this->get('from_email') ?: 
				Config::get('email', 'from_email'),
				'from_name' => $this->get('from_name') ?: 
				Config::get('email', 'from_name'),
				'charset' => $this->get('charset') ?: 
				Config::get('email', 'default_charset'),
				'format' => $this->get('format') ?: 
				Config::get('email', 'format'),
				'creation_date' => $this->get('creation_date', true) ?: 
				time(),
				'smtp' => [
					'host' => Config::get('email', 'smtp_host'),
					'user' => Config::get('email', 'smtp_user'),
					'pass' => Config::get('email', 'smtp_pass')
				]
			]);
			//->removeUnusedPlaceholders();

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
				if(
					$column == 'body' && 
					is_array($value)
				) {
					$this->setVariables($value);
				}
				elseif($column == 'template') {
					$this->setTemplate($value);
				}
				elseif($column == 'smtp') {
					$this->setSMTP($value);
				}
				elseif($column == 'to') {
					$this->setRecipient('to', $value);
				}
				elseif($column == 'reply_to') {
					$this->setRecipient('reply_to', $value);
				}
				elseif($column == 'cc') {
					$this->setRecipient('cc', $value);
				}
				elseif($column == 'bcc') {
					$this->setRecipient('bcc', $value);
				}
				else {
					// otherwise set it normally
					parent::set([$column=>$value]);
				}
			}
		}
		else {
			parent::set($columns, $ignored_value);
		}
		// and still, return ourselves for chaining
		return $this;

	}

	private function setTemplate(string $template_path) :self {
		// if the template file exists
		if(file_exists($template_path)) {
			// set the path
			return $this->set([
				'body'=>file_get_contents($template_path)
			]);
		}
		// template file does not exist
		else {
			// throw an exeption
			Throw new Exception(
				'Emails->setTemplate() the template file does not exist', 
				404
			);
		}

	}

	private function setVariables(
		array $placeholders_and_values
	) :self {

		// for each values
		foreach(
			$placeholders_and_values as 
			$placeholder => $value
		) {
			// replace the placeholder with it's actual value
			$this->set([
				'body'=> str_replace(
					[
						'__'.$placeholder.'__', // this allows for some backward compatibility
						'{{'.$placeholder.'}}'
					], 
					$value, 
					$this->get('body', true)
				)
			]);
		}

		// allow for chaining
		return $this;

	}

	// private function removeUnusedPlaceholders() :self {

	// 	// replace unused placeholder with nothing
	// 	return $this->get('body') ? $this->set([
	// 		'body'=> preg_replace(
	// 			'/{{([A-Za-z0-9_])+}}/gi',
	// 			'',
	// 			$this->get('body', true)
	// 		)
	// 	]);

	// }

	public function getError() {
		// return the textual representation for the last error
		return isset($this->_['mailer']) ? $this->_['mailer']->ErrorInfo : '';
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

		// use normal saving of record class
		return parent::save();

	}

	public function send(?bool $save = false) :self {

		// autoconfigure
		$this->autoConfigure();

		// place a marker
		$this->_['id_marker'] = Profiler::setMarker(null, 'email', ['Email'=>$this]);

		// configure the php mailer object
		$this->configurePHPMailer();

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
		Profiler::releaseMarker($this->_['id_marker']);

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

		// set the recipients
		foreach(
			$this->getRecipients('to') as $email => $name
		) {
			// add to the mailer as actual recipients or as header (hidden)
			Config::isProd() ? 
				$this->_['mailer']->addAddress($email, $name) : 
				$this->_['mailer']->addCustomHeader('X-To', $email . ' ' . $name);
		}
		// set the carbon copy recipients
		foreach(
			$this->getRecipients('cc') as $email => $name
		) {
			// add to the mailer as actual recipients or as header (hidden)
			Config::isProd() ? 
				$this->_['mailer']->addCC($email, $name) : 
				$this->_['mailer']->addCustomHeader('X-Cc', $email . ' ' . $name);
		}
		// set the hidden recipients
		foreach(
			$this->getRecipients('bcc') as $email => $name
		) {
			// add to the mailer as actual recipients or as header (hidden)
			Config::isProd() ? 
				$this->_['mailer']->addBCC($email, $name) : 
				$this->_['mailer']->addCustomHeader('X-Bcc', $email . ' ' . $name);
		}
		// set the reply-to recipients
		foreach(
			$this->getRecipients('reply_to') as $email => $name
		) {
			// add to the mailer as actual recipients or as header (hidden)
			Config::isProd() ? 
				$this->_['mailer']->addReplyTo($email, $name) : 
				$this->_['mailer']->addCustomHeader('X-Reply-To', $email . ' ' . $name);
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
