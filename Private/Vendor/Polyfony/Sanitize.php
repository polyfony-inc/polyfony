<?php
/**
 * PHP Version 5
 * Sanitization helper, when validation would cause too much overweight
 * just sanitize your string with this class before accepting them
 * this class should be merged with Format
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Sanitize {


	// will clean the value of anything but 0-9 and minus preserve sign return integer
	public static function integer($value) {

		if(strrpos($value, '.')) {
			$value = str_replace(',', '' , $value);
		}
		elseif(strrpos($value, ',')) {
			$value = str_replace('.', '' , $value);
		} 
		return(intval(preg_replace('/[^0-9.\-]/', '', str_replace(',', '.' , $value))));

	}
	
	// will clean the value of anything but 0-9\.- preserve sign return float
	public static function float($value) {

		if(strrpos($value, '.')) {
			$value = str_replace(',', '' , $value);
		}
		elseif(strrpos($value, ',')) {
			$value = str_replace('.', '' , $value);
		} 
		return(floatval(preg_replace('/[^0-9.\-]/', '', str_replace(',', '.' , $value))));

	}
	
	// will only allow numbers and + fields are dash separated 
	public static function phone($value) {

		$value = str_replace(array(' ', '.', 'ext', 'extension', 'x', '/', '.','(',')', '-', $value);
		$value = str_replace(array('---','--'), '-', $value);
		$value = substr($value, 0, 1) != '-' ? $value : substr($value, 1);
		return (preg_replace('/[^0-9+\-]/', '', $value));	

	}


}

?>


?>

