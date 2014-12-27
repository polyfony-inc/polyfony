<?php
/**
 * PHP Version 5
 * String format helpers
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Form {
	

	public static function input($name, $value=null, $attributes=array()) {
		
		// form the input form
		return('<input '. Format::attributes(array_merge(array('type'=>'text', 'name'=>$name, 'value'=>$value, $attributes))) . ' />' );
		
	}
	
	public static function password($name, $value=null, $attributes=array()) {
		
		// form the password input form
		return('<input '. Format::attributes(array_merge(array('type'=>'password', 'name'=>$name, 'value'=>$value, $attributes))) . ' />' );
		
	}
	
	public static function hidden($name, $value=null, $attributes=array()) {
		
		// form the hidden input form
		return('<input '. Format::attributes(array_merge(array('type'=>'hidden', 'name'=>$name, 'value'=>$value, $attributes))) . ' />' );
		
	}
	
	public static function submit($value=null, $attributes=array()) {
		
		// form the submit form
		return('<input '. Format::attributes(array_merge(array('type'=>'submit', 'value'=>$value, $attributes))) . ' />' );
		
	}
	
	public static function select($name, $options=array(), $value=null, $attributes=array()) {
		
	}
	
	public static function checkbox($name, $checked=false, $attributes=array()) {
		
	}
	
	public static function radio($name, $checked=false, $attributes=array()) {
		
	}
	
}

?>
