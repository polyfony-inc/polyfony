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

	// declare auto closing tags
	private static $_auto = array('input','img','hr','br','meta','link');

	// internal of the DOM element
	private $type;
	private $attributes;
	private $content;

	// main constructor
	public function __construct($type='div', $options=null) {
		// initialize variables
		$this->content 		= '';
		$this->type 		= $type;
		$this->attributes 	= array();
		// set the options if any
		!$options ?: $this->set($options);
	}

	// set the text in the tag
	public function setText($text, $append=true) {
		// append by default or replace text
		$this->content = $append ? $this->content . Format::htmlSafe($text) : Format::htmlSafe($text);
	}

	// set the html in the tag
	public function setHtml($html, $append=true) {
		// append by default or replace html
		$this->content = $append ? $this->content . $html : $html;
	}

	// set everything, mostly attribute but also content
	public function set($attribute, $value=null) {
		// array is passed
		if(is_array($attribute)) {
			// for each associative value in the array
			foreach($attribute as $single_attribute => $single_value) {
				// recurse with a single pair or attribute/value
				$this->set($single_attribute, $single_value);
			}
			// stop
			return;
		}
		// specific case of text
		if($attribute == 'text') {
			// use the setter
			$this->setText($value);
		}
		// specific case of html
		elseif($attribute == 'html') {
			// use the setter
			$this->setHtml($value);
		}
		// any normal attribute
		else {
			// if the value is an array we assemble its content or we set directly
			$this->attributes[$attribute] = is_array($value) ? implode(' ', $value) : $value;
		}
	}

	// adopt an element of the same kind
	public function adopt(Element $child, $before=false) {
		// adopt, or adopt before
		$this->content = $before ? $child . $this->content : $this->content . $child;
	}

	// convert our object to an actual html tag
	public function __toString() {
		// first tag
		$html_element = '<' . $this->type;
		// for each attribute
		foreach($this->attributes as $attribute => $value) {
			// append it
			$html_element .= ' '.$attribute . '="' . $value . '" ';
		}
		// close the tag
		$html_element .= in_array($this->type, self::$_auto) ? ' />' : ">{$this->content}</{$this->type}>";
		// return the html element
		return($html_element);
	}


}

?>
