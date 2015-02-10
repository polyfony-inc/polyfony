<?php
/**
 * PHP Version 5
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Model {

	// manualy override the table name so that it doesn't reflect the class name
	private static $_table = null;
	

	// find a set of results
	public static function find($parameters) {

		
	}
	
	// find the first result matching specific parameters
	public static function findFirst(parameters) {

		
	}
	
	// change the data source (table)
	public static function setSource($table_name) {
		
	}
	
	// get the source for this model
	public static function getSource() {
		
		// class name, or overriden table name
		return self::$_table ?: get_called_class();
		
	}
	
}


?>
