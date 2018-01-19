<?php
/**
 * PHP Version 5
 * Provide an object to easily store an alert for the enduser
 * This notice can be rendered nicely as a string or you can get
 * the title and message separately to format them your way
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Bootstrap;

class Alert {
	
	const DEFAULT_CLASS 	= 'info';
	const AVAILABLE_CLASSES = [
		'primary',
		'secondary',
		'success',
		'danger',
		'warning',
		'info',
		'light',
		'dark'
	];

	private $message;
	private $title;
	private $footer;
	private $class;

	public function __construct(
		$class   =null,
		$title   =null, 
		$message =null,
		$footer  =null
	) {
		$this->setClass($class);
		$this->setTitle($title);
		$this->setMessage($message);
		$this->setFooter($footer);
	}

	// setters

	public function setClass($class=null) {
		// make sure that class is allowed and supported by bootstrap
		$this->class = in_array($class, self::AVAILABLE_CLASSES) ? 
			$class : self::DEFAULT_CLASS;
	}

	public function setTitle($title=null) {
		$this->title = $title;
	}

	public function setMessage($message=null) {
		$this->message = $message;
	}

	public function setFooter($footer=null) {
		$this->footer = $footer;
	}

	// getter

	public function getClass() {
		return $this->class;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getMessage() {
		return $this->message;
	}

	public function getFooter() {
		return $this->footer;
	}

	// magic goes here

	public function getHtml() {
		
		// create a bootstrap alert
		$alert = new \Polyfony\Element('div',[
			'class'	=>"alert alert-{$this->getClass()}",
			'role'	=>'alert'
		]);

		// add a title
		if($this->title) {
			$alert->adopt(new \Polyfony\Element('h2',[
				'class'	=>'alert-heading',
				'text'	=>$this->getTitle()
			]));
		}

		if($this->message) {
			$alert->adopt(new \Polyfony\Element('p',[
				'text'	=>$this->getMessage()
			]));
		}

		if($this->footer) {
			$alert->adopt(new \Polyfony\Element('hr'));
			$alert->adopt(new \Polyfony\Element('p',[
				'text'	=>$this->getFooter()
			]));
		}

		return $alert->__toString();
	}

	// magic convertion of the object to text
	public function __toString() {
		// convert to html
		return $this->getHtml();
	}
	
}

?>
