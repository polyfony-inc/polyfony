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
/*
	// set the text in the tag
	public function setText($text, $append=true) {
		// append by default or replace text
		$this->content = $append ? $this->content . Format::htmlSafe($text) : Format::htmlSafe($text);
		// return self
		return($this);
	}

	// set the html in the tag
	public function setHtml($html, $append=true) {
		// append by default or replace html
		$this->content = $append ? $this->content . $html : $html;
		// return self
		return($this);
	}
*/

	// set the text in the tag
	public function setText($text, $mode=0) {

		switch(strtolower($mode)){

			// insert text before previous content
			case 1:
			case 'i':
			case 'b':
				$this->content = Format::htmlSafe($text) . $this->content;
				break;

			// replace or set text
			case 2:
			case 's':
				$this->content = Format::htmlSafe($text);
				break;

			// append by default
			case 0:
			case 'a':
			default:
				$this->content = $this->content . Format::htmlSafe($text);
		}

		// return self
		return($this);
	}

	// set the html in the tag
	public function setHtml($html, $mode=0) {

		switch(strtolower($mode)){

			// insert html before previous content
			case 1:
			case 'i':
			case 'b':
				$this->content = $html . $this->content;
				break;

			// replace or set html
			case 2:
			case 's':
				$this->content = $html;
				break;

			// append by default
			case 0:
			case 'a':
			default:
				$this->content = $this->content . $html;
		}
		// return self
		return($this);
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
			// return self
			return($this);
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
		// return self
		return($this);
	}

	// adopt an element of the same kind
	public function adopt($child, $before=false) {

		if ($child instanceof Element) {
			// adopt, or adopt before
			$this->content = $before ? $child . $this->content : $this->content . $child;
			// return self
			return($this);

		} else {
			// those actions being incompatible we throw an exception
			Throw new Exception("Polyfony Element : cant adopt a non Polyfony\Elelement  : (".gettype($child).") {$child}","997");
		}
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
