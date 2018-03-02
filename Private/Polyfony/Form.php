<?php

namespace Polyfony;

class Form {

	public static function input(string $name, $value='', array $attributes=[]) :Element {
		// format the form element
		return(new Element(
			'input', 
			array_merge(
				array(
					'type'	=>'text', 
					'name'	=>$name, 
					'value'	=>$value
				), 
				$attributes
			)
		));
	}
	
	public static function textarea(string $name, $value='', array $attributes=[]) :Element {
		// format the form element
		return(new Element(
			'textarea', 
			array_merge(
				array(
					'name'	=>$name, 
					'text'	=>$value
				),
				$attributes
			)
		));
	}
	
	public static function checkbox(string $name, bool $checked=false, array $attributes=[]) :Element {
		// if the checkbox is checked
		if($checked) {
			// add to the attributes
			$attributes['checked'] = 'checked';
		}
		// format the form element
		return(new Element(
			'input', 
			array_merge(
				array(
					'type'	=>'checkbox',
					'name'	=>$name
				), 
				$attributes
			)
		));
	}
	
	public static function radio(string $name, $value, bool $checked=false, array $attributes=[]) :Element {
		// if the checkbox is checked
		if($checked) {
			// add to the attributes
			$attributes['checked'] = 'checked';
		}
		// format the form element
		return(new Element(
			'input', 
			array_merge(
				array(
					'name'	=>$name, 
					'type'	=>'radio',
					'value'	=>Format::htmlSafe($value)
				), 
				$attributes
			)
		));
	}

	public static function select(string $name, array $options=[], $value=null, array $attributes=[]) :Element {
		// if multiple and braquets are not trailing yet, add them
		$name .= isset($attributes['multiple']) && substr($name,0,2) != '[]' ? '[]': '';
		// format the select element
		$select = new Element('select', array_merge(array('name' => $name), $attributes));
		// for each options available
		foreach($options as $key_or_group_name => $value_or_group) {
			// if the value is an array (we enter an optgroup)
			if(is_array($value_or_group)) {
				// create an optgroup
				$optgroup = new Element('optgroup', array('label'=> Locales::get($key_or_group_name)));
				// iterate on the group
				foreach($value_or_group as $option_key => $option_value) {
					// define option attributes
					$option_attributes = array(
						'value'	=> $option_key,
						'text'	=> Locales::get($option_value)
					);
					// if the current key has to be select
					if(
						(is_array($value) && in_array($option_key, $value)) || 
						($value !== null && $value == $option_key)
					) {
						// selected
						$option_attributes['selected'] = 'selected';
					}
					// create a new option
					$option = new Element('option', $option_attributes);
					// adopt in the optgroup
					$optgroup->adopt($option);
				}
				// put the optgroup in the select
				$select->adopt($optgroup);
			}
			// the value is normal, it a simple choice
			else {
				// define option attributes
				$option_attributes = array(
					'value'	=> $key_or_group_name,
					'text'	=> Locales::get($value_or_group)
				);
				// if the current key has to be select
				if(
					(is_array($value) && in_array($key_or_group_name, $value)) || 
					($value !== null && $value == $key_or_group_name)
				) {
					// selected
					$option_attributes['selected'] = 'selected';
				}
				// create a new option
				$option = new Element('option', $option_attributes);
				// put the option in the select directly
				$select->adopt($option);
			}

		}
		// return the whole select element
		return($select);
	}
	
	
}

?>
