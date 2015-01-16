<?php
/**
 * PHP Version 5
 * Sanitization helper, when validation would cause too much overweight
 * just sanitize your string with this class before accepting them
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Sanitize {
	
	// will clean the value of anything but 0-9
	// preserve sign
	// return integer
	public static function integer($value) {

		$dotPos = strrpos($value, '.');

		$commaPos = strrpos($value, ',');

		$sep =	(($dotPos > $commaPos) && $dotPos) ? $dotPos : 
				((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
		
		if (!$sep) {
			return intval(preg_replace("/[^0-9\-]/", "", $value));
		}

		return intval(preg_replace("/[^0-9\-]/", "", substr($value, 0, $sep)));
	}
	
	// will clean the value of anything but 0-9
	// wil preserve decimal part
	// will preserve sign
	// return float
	public static function float($value) {

		$dotPos = strrpos($value, '.');

		$commaPos = strrpos($value, ',');

		$sep =	(($dotPos > $commaPos) && $dotPos) ? $dotPos : 
				((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
		
			if (!$sep) {
				return floatval(preg_replace("/[^0-9\-]/", "", $value));
			}

		return floatval(
					preg_replace("/[^0-9\-]/", "", substr($value, 0, $sep)) . '.' .
					preg_replace("/[^0-9]/", "", substr($value, $sep+1, strlen($value)))
		);
	}
	
	// will clean from special characters
	public static function text() {
		
	}
	
	// will only allow numbers, + and ()
	public static function phone() {
		
	}


}

?>


?>

