<?php

namespace Polyfony;

class Mail {

	protected $title;
	protected $from;
	protected $recipients;
	protected $files;
	protected $template;
	protected $body;
	protected $subject;
	protected $variables;
	protected $format;
	protected $charset;
	protected $smtp;
	protected $mailer;

	public function __construct() {
		// initialize
		$this->format 		= Config::get('mail', 'format');
		$this->charset 		= Config::get('mail', 'default_charset');
		$this->body 		= '';
		$this->subject 		= '';
		$this->mailer 		= null;
		$this->title 		= null;
		$this->files 		= [];
		$this->variables 	= [];
		$this->recipients 	= [
			'to'	=> [],
			'cc'	=> [],
			'bcc'	=> []
		];
		$this->from = [
			'name'	=> Config::get('mail', 'from_name'),
			'mail'	=> Config::get('mail', 'from_mail')
		];
		$this->smtp = [
			'host'	=> Config::get('mail', 'smtp_host'),
			'user'	=> Config::get('mail', 'smtp_user'),
			'pass'	=> Config::get('mail', 'smtp_pass')
		];
	}

	public function getDebugData() {
		return [
			'recipients'=>$this->recipients,
			'subject'	=>$this->subject,
			'files'		=>$this->files,
			'from'		=>$this->from,
			'format'	=>$this->format,
			'smtp'		=>$this->smtp
		];
	}

	public function format($format) {
		// set the correct format
		$this->format = $format == 'html' ? 'html' : 'text';
		// return self
		return($this);
	}

	public function title($title) {
		// set the title of the mail
		$this->title = $title;
		// return self
		return($this);
	}

	public function charset($charset) {
		// set the charset of the mail
		$this->charset = $charset;
		// return self
		return($this);
	}

	public function smtp($host='', $user='', $pass='') {
		// change the smtp configuration
		$this->smtp = array(
			'host'	=> $host,
			'user'	=> $user,
			'pass'	=> $pass
		);
		// return self
		return($this);
	}

	public function from($mail, $name) {
		// change the sender name and email
		$this->from['mail'] = $mail;
		$this->from['name'] = $name;
		// return self
		return($this);
	}

	private function recipient(string $type, $mail, $name=null) {
		// array of recipients provided
		if(is_array($mail)) {
			// for each recipients
			foreach($mail as $individual_mail) {
				// if we have only an email, set the name to null
				list($individual_mail, $individual_name) = is_array($individual_mail) ? $individual_mail : array($individual_mail, null);
				// recurse
				$this->recipient($type, $individual_mail, $individual_name);
			}
		}
		// single recipient provided
		else {
			// push to the table
			$this->recipients[$type][$mail] = $name;
		}
		// return self
		return($this);
	}

	public function to($mail, $name=null) {
		// return self
		return $this->recipient('to', $mail, $name);
	}

	public function cc($mail, $name=null) {
		// return self
		return $this->recipient('cc', $mail, $name);
	}

	public function bcc($mail, $name=null) {
		// return self
		return $this->recipient('bcc', $mail, $name);
	}

	public function file($file_path, $file_name='') {
		// array of recipients provided
		if(is_array($file_path)) {
			// for each recipients
			foreach($file_path as $individual_path) {
				// if we only have to path or if we also have the name
				list($individual_path, $individual_name) = is_array($individual_path) ? $individual_path : array($individual_path, null);
				// recurse
				$this->file($individual_path, $individual_name);
			}
		}
		// // if the file exists
		elseif(file_exists($file_path)) {
			// push to the table
			$this->files[$file_path] = $file_name;
		}
		// file does not exist
		else {
			// throw an exeption
			Throw new Exception('Mail->file() the attachment file does not exist');
		}
		// return self
		return($this);
	}

	public function template($template_path) {
		// if the template file exists
		if(file_exists($template_path)) {
			// set the path
			$this->template = $template_path;
		}
		// template file does not exist
		else {
			// throw an exeption
			Throw new Exception('Mail->template() the template file does not exist');
		}
		// return self
		return($this);
	}

	public function set($key, $value='', $escape_html = true) {
		// clean the value from html entities if using html mail format and there is no bypass
		$value = $this->format == 'html' && $escape_html === true ? Format::htmlSafe($value) : $value;
		// directly set the asociative key/value
		$this->variables[$key] = $value;
		// return self
		return($this);
	}

	public function body($body, $replace=false) {
		// replace or append
		$this->body = $replace ? $body : $this->body . $body;
		// return self
		return($this);
	}

	public function subject($subject, $replace=false) {
		// replace or append
		$this->subject = $replace ? $subject : $this->subject . $subject;
		// return self
		return($this);
	}

	public function error() {
		// return the textual representation for the last error
		return(isset($this->mailer) ? $this->mailer->ErrorInfo : '');
	}



	public function send($save=false) {

		// marker
		$id_marker = Profiler::setMarker(null, 'email', ['Email'=>$this]);

		// configure the php mailer object
		$this->configurePHPMailer();
		// configure environment specifics parameters/options
		$this->configureEnvironmentSpecifics();

		// default status is false
		$is_sent = false;

		// try sending
		try {
			$is_sent = (bool) $this->mailer->Send();
		}
		// catch any exception
		catch (Exception $exception) {
			// log the error
			Logger::warning($exception->getMessage());
			// return that we failed to send
			$is_sent = false;
		}

		// if we want to save the email to the database
		if($save) {
			// insert pretty much as is
			Database::query()
				->insert(array(
					'is_sent' 			=> $is_sent,
					'creation_date' 	=> time(),
					'sending_date' 		=> $is_sent ? time() : null,
					'title'				=> $this->title,
					'format'			=> $this->format,
					'from_mail'			=> $this->from['mail'],
					'from_name'			=> $this->from['name'],
					'body'				=> $this->body,
					'subject'			=> $this->subject,
					'to_array'			=> $this->recipients['to'],
					'cc_array'			=> $this->recipients['cc'],
					'bcc_array'			=> $this->recipients['bcc'],
					'files_array'		=> $this->files
				))
				->into('Mails')
				->execute();
		}

		// marker
		Profiler::releaseMarker($id_marker);

		// return the sending status
		return $is_sent;

	}

	private function configurePHPMailer() {

		// instanciate a new phpmail object (allowing exception to be thrown)
		$this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

		// configure the mailer from hard config
		$this->mailer->CharSet 		= $this->charset;

		// configure the mailer from instance config
		$this->mailer->From 		= $this->from['mail'];
		$this->mailer->FromName 	= $this->from['name'];
		$this->mailer->Subject 		= $this->subject;

		// change the mailer engine if necessary
		$this->mailer->Mailer 		= $this->smtp['host'] ? 'smtp' : 'mail';
		$this->mailer->SMTPAuth 	= $this->smtp['user'] ? true : false;
		$this->mailer->Host 		= $this->smtp['host'];
		$this->mailer->Username 	= $this->smtp['user'];
		$this->mailer->Password 	= $this->smtp['pass'];

		// set the format of the mail
		$this->mailer->isHTML($this->format == 'html' ? true : false);
		
		// for each attachment
		foreach($this->files as $path => $name) {
			// add to the mailer
			$this->mailer->addAttachment($path, $name);
		}

		// if a template exists, use it
		if($this->template) {
			// replace the body with the template
			$this->body = file_get_contents($this->template);
			// for each variables available
			foreach($this->variables as $key => $value) {
				// replace in the body
				$this->body = str_replace("__{$key}__", $value, $this->body);
			}
		}

		// set the body in the mailer
		$this->mailer->Body = $this->body;
	}

	// this applies options that are only used in development, or only in production  
	private function configureEnvironmentSpecifics() {
		// if we are in production we set the actual recipients
        if(Config::isProd()) {
            // set the main recipients
            foreach($this->recipients['to'] as $mail => $name) {
                // add to the mailer
                $this->mailer->addAddress($mail, $name);
            }
            // set the carbon copy recipients
            foreach($this->recipients['cc'] as $mail => $name) {
                // add to the mailer
                $this->mailer->addCC($mail, $name);
            }
            // set the hidden recipients
            foreach($this->recipients['bcc'] as $mail => $name) {
                // add to the mailer
                $this->mailer->addBCC($mail, $name);
            }
        }
        // if we are in the development enviroment
        else {
            // add the bypass address only
            $this->mailer->addAddress(Config::get('mail', 'bypass_mail'));
        }
	}

}

?>