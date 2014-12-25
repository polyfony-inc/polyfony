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

class Record {
	
	// if of the record
	protected $_id;
	// table of the record
	protected $_table;
	// list of altered columns
	protected $_altered;
	
	// create a object from scratch, of fetch it in from its table/id
	public function __construct($table=null,$conditions=null) {
		
		
		
	}
	
	public function get($column, $raw=false) {

		// return the columns or null if it does not exist		
		return(isset($this->{$column}) ? $this->convert($column, $raw) : null);

	}
	
	public function set($column, $value) {
		
		// convert the value depending on the column name
		$this->{$column} = Query::convert($column, $value);
		
	}
	
	private function convert($column) {
		return($this->{$column});
	}
	
	// update or create
	public function save() {
	}
	
	// delete
	public function delete() {
	}
	
}


?>