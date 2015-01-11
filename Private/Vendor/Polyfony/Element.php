<?php
/**
 * PHP Version 5
 * Class to generate Elements
 * Loosely based on Mootools Element class
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;
 
class Element {

	private $type;
	private $attributes;
	private $content;

	public function __construct($type='div', $options=array()) {
		$this->type 		= $type;
		$this->attributes 	= $options;
		$this->content 		= '';
	}

	public function setText($text) {
		$this->content = Format::htmlSafe($text);
	}

	public function appendText($text) {
		$this->content .= Format::htmlSafe($text);
	}

	public function setHtml($html) {
		$this->content = $html;
	}

	public function appendHtml($html) {
		$this->content .= $html;
	}

	public function set($attribute, $value) {
		$this->attributes[$attribute] = $value;
	}

	public function get($attribute) {
		return(isset($this->attributes[$attribute] ? $this->attributes[$attribute] : null));
	}

	public function adopt(Element $child) {
		$this->content .= $child;
	}

	public function adoptBefore(Element $child) {
		$this->content = $child . $this->content;
	}

	public function __toString() {
		// magic goes here
	}


}

?>
