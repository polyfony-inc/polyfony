<?php

namespace Polyfony;

class Form {

	private static function builder(
		string $nature, 
		array $pre_defined_attributes=[], 
		array $user_defined_attributes=[]
	) :Element {
		return new Element($nature, array_merge(
			$pre_defined_attributes,
			$user_defined_attributes
		));
	}

	private static function addCheckedAttributeIfTrue(array $attributes, bool $is_checked) :array {
		if($is_checked) {
			$attributes['checked'] = 'checked';
		}
		return $attributes;
	}

	public static function input(string $name, $value='', array $attributes=[]) :Element {
		return self::builder('input', [
			'type'	=>'input',
			'name'	=>$name,
			'value'	=>$value
		], $attributes);
	}
	
	public static function textarea(string $name, $value='', array $attributes=[]) :Element {
		return self::builder('textarea', [
			'type'=>'textarea',
			'name'=>$name,
			'text'=>$value
		], $attributes);
	}
	
	public static function checkbox(string $name, bool $checked=false, array $attributes=[]) :Element {
		return self::builder('input', [
			'type'	=>'checkbox',
			'name'	=>$name
		], self::addCheckedAttributeIfTrue($attributes, $checked));
	}
	
	public static function radio(string $name, $value, bool $checked=false, array $attributes=[]) :Element {
		return self::builder('input', [
			'type'	=>'radio',
			'name'	=>$name,
			'value'	=>$value
		], self::addCheckedAttributeIfTrue($attributes, $checked));
	}

	private static function isThisOptionSelected($option_key, $selected_value=null) :bool {
		return (
				is_array($selected_value) && 
				in_array($option_key, $selected_value)
			) || 
			(
				$selected_value !== null && 
				$selected_value == $option_key
			);
	}

	private static function buildSelectName(string $name, array $attributes=[]) :string {
		if( isset($attributes['multiple']) && substr($name, -2, 2) != '[]' ) {
			$name .= '[]';
		}

		return $name;
	}

	private static function buildSelectOption($option_key, $option_name, $selected_value=null) :Element {

		// define option attributes
		$option_attributes = array(
			'value'	=> $option_key,
			'text'	=> Locales::get($option_name)
		);
		// if the current key has to be selected
		if(self::isThisOptionSelected($option_key, $selected_value)) {
			// selected
			$option_attributes['selected'] = 'selected';
		}
		// put the option in the select directly
		return new Element('option', $option_attributes);

	}

	public static function select(
		string $name, array $select_options=[], $selected_value=null, array $attributes=[]
	) :Element {
		// format the initial and empty select element
		$select = self::builder('select', ['name'=>self::buildSelectName($name, $attributes)], $attributes);
		// for each options available
		foreach($select_options as $option_key => $option_name) {
			// if the value is an array (we enter an optgroup)
			if(is_array($option_name)) {
				// create an optgroup
				$optgroup = new Element('optgroup', array('label'=> Locales::get($option_key)));
				// iterate on the group
				foreach($option_name as $sub_option_key => $sub_option_name) {
					// build option
					$optgroup->adopt(self::buildSelectOption($sub_option_key, $sub_option_name, $selected_value));
				}
				// put the optgroup in the select
				$select->adopt($optgroup);
			}
			// the value is normal, it is a simple list choice
			else {
				// build option
				$select->adopt(self::buildSelectOption($option_key, $option_name, $selected_value));
			}

		}
		// return the whole select element
		return $select;
	}
	
	
}

?>
