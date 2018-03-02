<?php

namespace Polyfony;
 
class Element {

	// declare auto closing tags
	private static $_auto = array('input', 'img', 'hr', 'br', 'meta', 'link');

	// internal of the DOM element
	private $type;
	private $attributes;
	private $content;

	// main constructor
	public function __construct(string $type='div', array $options=null) {
		// initialize variables
		$this->content 		= '';
		$this->type 		= $type;
		$this->attributes 	= array();
		// set the options if any
		!$options ?: $this->set($options);
	}

	// set the text in the tag
	public function setText(string $text=null, bool $append=true) :self {
		// append by default or replace text
		$this->content = $append ? $this->content . Format::htmlSafe($text) : Format::htmlSafe($text);
		// return self
		return($this);
	}

	// set the html in the tag
	public function setHtml(string $html=null, bool $append=true) :self {
		// append by default or replace html
		$this->content = $append ? $this->content . $html : $html;
		// return self
		return($this);
	}

	// set the style for the tag
	public function setStyle(array $css_properties=[], bool $append=true) {
		// init the style attribute is it didn't exist already
		isset($this->attributes['style']) ?: $this->attributes['style'] = '';
		// for each of the css properties
		foreach($css_properties as $css_property => $css_value) {
			// add them to the already existing ones
			$this->attributes['style'] .= $css_property.':'.$css_value.';';
		}
		// return self
		return($this);
	}

	// set the value of a tag
	public function setValue($value=null) :self {
		// append by default or replace html
		$this->attributes['value'] = Format::htmlSafe($value);
		// return self
		return($this);
	}

	// set everything, mostly attribute but also content
	public function set($attribute, $value=null) :self {
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
		// specific case of style
		elseif($attribute == 'style' && is_array($value)) {
			// use the setter
			$this->setStyle($value);
		}
		// specific case of value
		elseif($attribute == 'value') {
			// use the setter
			$this->setValue($value);
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
		// adopt, or adopt before
		$this->content = $before ? $child . $this->content : $this->content . $child;
		// return self
		return($this);
	}

	// convert our object to an actual html tag
	public function __toString() :string {
		// first tag
		$html_element = '<' . $this->type;
		// for each attribute
		foreach($this->attributes as $attribute => $value) {
			// append it
			$html_element .= ' '.$attribute . '="' . $value . '"';
		}
		// close the tag
		$html_element .= in_array($this->type, self::$_auto) ? ' />' : ">{$this->content}</{$this->type}>";
		// return the html element
		return($html_element);
	}


}

?>
