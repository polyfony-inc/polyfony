<?php
/**
 * PHP Version 5
 * Mail generation and sending class
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;
 
class Mail {

	private $from;
	private $recipients;
	private $attachments;
	private $template;
	private $body;
	private $subject;
	private $variables;
	private $format;
	private $smtp;
	private $mailer;

	public function __construct() {
		// initialize
		$this->format 		= Config::get('mail', 'format');
		$this->body 		= '';
		$this->subject 		= '';
		$this->mailer 		= null;
		$this->variables 	= array();
		$this->attachments 	= array();
		$this->recipients 	= array(
			'to'	=> array(),
			'cc'	=> array(),
			'bcc'	=> array()
		);
		$this->from 		= array(
			'name'	=> Config::get('mail', 'from_name'),
			'mail'	=> Config::get('mail', 'from_mail')
		);
		$this->smtp 		= array(
			'host'	=> Config::get('mail', 'smtp_host'),
			'user'	=> Config::get('mail', 'smtp_user')
			'pass'	=> Config::get('mail', 'smtp_pass')
		)
		
		// retrieve from the database if id exists
	}

	public function format($format) {
		// html or text
	}

	public function to($mail, $name=null) {

	}

	public function cc($mail, $name=null) {

	}

	public function bcc($mail, $name=null) {

	}

	public function file($file_path, $file_name=null) {

	}

	public function template($template_path) {

	}

	public function set($variable, $value=null) {

	}

	public function body($body, $replace=false) {

	}

	public function subject($subject, $replace=false) {

	}

	public function send($save=true) {
		
		// instanciate a new phpmail object
		$this->mailer = new PHPMailer();

		// configure the mailer from hard config
		$this->mailer->CharSet 		= Config::get('mail', 'default_charset');

		// configure the mailer from instance config
		$this->mailer->From 		= $this->from['mail'];
		$this->mailer->FromName 	= $this->from['name'];
		$this->mailer->Subject 		= $this->subject;

		// set the format of the mail
		$this->mailer->isHTML($this->format == 'html' ? true : false);

		// set the recipients
		foreach($this->recipients['to'] as $mail => $name) {
			// add to the mailer
			$this->mailer->addAddress($mail, $name);
		}
		// set the recipients
		foreach($this->recipients['cc'] as $mail => $name) {
			// add to the mailer
			$this->mailer->addCC($mail, $name);
		}
		// set the recipients
		foreach($this->recipients['bcc'] as $mail => $name) {
			// add to the mailer
			$this->mailer->addBCC($mail, $name);
		}

		// if a template exists, use it
		if($this->template) {
			// if the template exists
			if(Filesystem::exists($this->template)) {
				// replace the body with the template
				$this->body = file_get_contents($this->template);
				// for each variables available
				foreach($this->variables as $key => $value) {
					// clean the value from html entities if using html mail format
					$value = $this->format == 'html' ? Format::htmlSafe($value) : $value;
					// replace in the body
					$this->body = str_replace("__{$key}__", $value, $this->body);
				}
			}
			// the specified template is missing
			else {
				// throw an exception
				Throw new Exception('Mail->send() the template file is missing');
			}
		}

		// set the body in the mailer
		$this->mailer->Body = $this->body;

		// if a smtp host is set, use it
		if($this->smtp['host']) {
			// change the mailer
			$this->mailer->Mailer 	= 'smtp';
			$this->mailer->SMTPAuth = $this->smtp['user'] && $this->smtp['pass'] ? true : false;
			$this->mailer->Host 	= $this->smtp['host'];
			$this->mailer->Username = $this->smtp['user'];
			$this->mailer->Password = $this->smtp['pass'];

		}

		// if we want to save the email to the database
		if($save) {
			// insert pretty much as is
			Database::query()
				->insert(array(

				))
				->into('Mails')
				->execute();
		}

		// if the actual sending succeeded

	}


}

?>
