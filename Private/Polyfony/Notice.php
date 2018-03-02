<?php

 //  ___                           _          _ 
 // |   \ ___ _ __ _ _ ___ __ __ _| |_ ___ __| |
 // | |) / -_) '_ \ '_/ -_) _/ _` |  _/ -_) _` |
 // |___/\___| .__/_| \___\__\__,_|\__\___\__,_|
 //          |_|                                

namespace Polyfony;

class Notice {
	
	private $message;
	private $title;
	
	const TYPE = 'info';
	
	public function __construct($message, $title=null) {
		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Notice is deprecated, use Bootstrap\Alert instead', 
			E_USER_DEPRECATED
		);
		// pass the message
		$this->message = Format::htmlSafe($message);
		// pass the title (if any)
		$this->title = $title ? Format::htmlSafe($title) : '';
	}
	
	public function getMessage($safe=true) {
		// return the raw message for ajax or plaintext purpose, or an html safe version
		return($safe ? Format::htmlSafe($this->message) : $this->message);
	}
	
	public function getTitle($safe=true) {
		// return the raw title for ajax or plaintext purpose, or an html safe version
		return($safe ? '<strong>' . Format::htmlSafe($this->title) . '</strong> ' : $this->title);
	}

	public function getHtml() {
		// format the notice using a very common boostrap type format
		return "<div class=\"alert alert-__TYPE__\" role=\"alert\">{$this->getTitle()}{$this->getMessage()}</div>";
	}

	public function __toString() {
		// type specific
		return(str_replace('__TYPE__',self::TYPE,$this->getHtml()));
	}
	
}

?>
