<?php
/**
 * PHP Version 5
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Notice {
	
	private $message;
	private $title;
	
	private static $type = 'info';

	public function __construct($message,$title=null) {
		// pass the message
		$this->message = Format::htmlSafe($message);
		// pass the title (if any)
		$this->title = $title ? Format::htmlSafe($title) : '';
		// auto set type
		$this->setType();
	}

	private function setType() {
		//$this->type = 'success';	// bootstrap green		Notice\Success
		//$this->type = 'info';		// bootstrap blue		Notice\Info
		//$this->type = 'warning';	// bootstrap yellow		Notice\Warning
		//$this->type = 'danger';	// bootstrap red		Notice\Danger
	}

	public function getMessage($safe=true) {
		return($safe ? Format::htmlSafe($this->message) : $this->message);
	}
	
	public function getTitle($safe=true) {
		return($safe ? '<strong>' . Format::htmlSafe($this->title) . '</strong> ' : $this->title);
	}

	public function __toString() {
		
		// format the notice 
		return "<div class=\"alert alert-". self::$type."\" role=\"alert\">{$this->getTitle()}{$this->getMessage()}</div>";
		
	}

	
}

?>