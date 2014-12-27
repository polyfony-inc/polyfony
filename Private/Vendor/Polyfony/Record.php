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
	public function __construct($table,$conditions=null) {
		
		// init the list of altered columns
		$_altered = array();
		
		// having a table means that we are not the result of a join query
		$this->_table = $table ? $table : null;
		
		/*
		$this->_id = isset($this->id) 
		*/
		
		
	}
	
	public function get($column, $raw=false) {

		// return the columns or null if it does not exist		
		return(isset($this->{$column}) ? $this->convert($column, $raw) : null);

	}
	
	public function set($column, $value) {
		
		// convert the value depending on the column name
		$this->{$column} = Query::convert($column, $value);
		// update the altered list
		
	}
	
	// magic
	public function __toArray($raw=false,$altered=false) {
		$array = array();
		foreach(get_object_vars($this) as $key => $something){
			
			$array[$key] = $raw ? $this->get($key,true) : $this->get($key,false);
		}
		return($array);
	}
	
	// magic
	public function __toString() {
		
	}
	
	private function alter($column) {
		// push
		$this->_altered[] = $column
		// deduplicate
		$this->_altered = array_unique($this->_altered);
	}
	
	private function convert($column, $raw=false) {
		return($this->{$column});
	}
	
	// update or create
	public function save() {
		
		// if an id exist, update, else insert, and return result
		return($this->_id ?
			Database::query()->update($this->_table)->set($this->__toArray(false,true))->where(array('id'=>$this->_id))->execute() :
			Database::query()->insert()->into($this->_table)->set($this->__toArray(false,true))->execute()
		);
		
	}
	
	// delete
	public function delete() {
	}
	
}


?>