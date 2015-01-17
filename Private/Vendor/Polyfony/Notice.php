<?php
/**
 * PHP Version 5
 * Provide an object to easily store a notice for the enduser
 * This notice can be rendered nicely as a string or you can get
 * the title and message separately to format them your way
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
	
	const TYPE = 'info';
	
	public function __construct($message, $title=null) {
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
