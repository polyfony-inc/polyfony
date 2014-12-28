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
	
	// storing variable that do not reflect the database table structure
	protected $_;
	
	// create a object from scratch, of fetch it in from its table/id
	public function __construct($table, $conditions=null) {
		
		// init the list of altered columns
		$this->_ = array(
			// if of the record
			'id'		=> null,
			// table of the record
			'table'		=> null,
			// list of altered columns since the retrieval from the database
			'altered'	=> array()
		);
		
		// having a table means that we are not the result of a join query
		$this->_['table'] = $table ? $table : null;
		
		// if we have an id available
		$this->_['id'] = isset($this->id) ? $this->id : null;
		
		// if conditions are provided
		if($conditions !== null) {
			// if conditions is not an array
			if(!is_array($conditions)) {
				// we assume it is the id of the record
				$conditions = array('id'=>$conditions);	
			}
			// grab that object from the database
			$records = Database::query()
				->select()
				->from($this->_['table'])
				->where($conditions)
				->execute();
			// if the record is found
			if($records) {
				// clone the found record
				$this->replicate($records[0]);
				// return self
				return($this);	
			}
			else {
				// return false
				return(false);
			}
		}
		// return self
		return($this);
		
	}
	
	private function replicate(Record $clone) {
		// for each attribute
		foreach(get_object_vars($clone) as $attribute => $value) {
			// clone that attribute
			$this->{$attribute} = $value;
		}	
	}
	
	public function get($column, $raw=false) {
		// return the columns or null if it does not exist		
		return(isset($this->{$column}) ? $this->convert($column, $raw) : null);
	}
	
	public function input($column, $options=null) {
		return(Form::input("{$this->_['table']}[$column]",$this->get($column),$options));
	}
	
	public function password($column, $options=null) {
	}
	
	public function textarea($column, $list=array(), $options=null) {
	}
	
	public function select($column, $list=array(), $options=null) {
	}
	
	public function checkbox($column, $options=null) {	
	}
	
	public function radio($column, $options=null) {
	}
	
	public function set($column, $value) {
		// convert the value depending on the column name
		$this->{$column} = Query::convert($column, $value);
		// update the altered list
		$this->alter($column);
		// return self
		return($this);
	}
	
	// magic
	public function __toArray($raw=false,$altered=false) {
		// declare an empty array
		$array = array();
		// for each attribute of this object
		foreach(get_object_vars($this) as $key => $something){
			// if the attribute is internal
			if($key == '_') {
				// skip it
				continue;
			}
			// normal attribute
			else {
				// convert or not
				$array[$key] = $raw ? $this->get($key,true) : $this->get($key,false);
			}
		}
		return($array);
	}
	
	// magic
	public function __toString() {
		// a string to sybolize this record
		return("Polyfony\Record:{$this->_['table']}:{$this->_['id']}");
	}
	
	private function alter($column) {
		// push
		$this->_['altered'][] = $column;
		// deduplicate
		$this->_['altered'] = array_unique($this->_['altered']);
	}
	
	private function convert($column, $raw=false) {
		
		// if we want the raw result
		if($raw) {
			// return as is
			return($this->{$column});
		}
		// otherwise convert it
		// if the column contains an array
		if(strpos($column,'_array') !== false) {
			// decode the array
			return(json_decode($this->{$column},true));
		}
		// if the column contains a size value
		elseif(strpos($column,'_size') !== false) {
			// convert to human size
			return(Format::size($this->{$column}));
		}
		// if the column contains a date
		elseif(strpos($column,'_date') !== false) {
			// if the value is set
			if(!empty($this->{$column})) {
				// create the date using raw unix epoch
				return(date('d/m/y',$this->{$column}));
			}
			// value is empty
			else {
				// return an empty string
				return('');	
			}
		}
		// not a magic column
		else {
			// return as is
			return($this->{$column});
		}
		
	}
	
	// update or create
	public function save() {
		
		// if an id already exists
		if($this->_['id']) {
			// we can update and return the number of affected rows (0 on error, 1 on success)
			$updated = Database::query()
				->update($this->_['table'])
				->set($this->__toArray(false,true))
				->where(array('id'=>$this->_['id']))
				->execute();
			// if update went well
			if($updated) {
				// return true
				return(true);
			}
			// update failed
			else {
				// return false
				return(false);
			}
		}
		// this is a new record
		else {
			// try to insert it
			$inserted = Database::query()
				->insert($this->__toArray(false,true))
				->into($this->_['table'])
				->execute();
			// if insertion succeeded
			if($inserted) {
				// clone ourself
				$this->replicate($inserted[0]);	
				// and return true
				return(true);
			}
			else {
				// return false
				return(false);	
			}
		}
		
	}
	
	// delete
	public function delete() {
		
		// if id or table if missing
		if(!$this->_['table'] || !$this->_['id']) {
			// throw an exception
			Throw new Exception('Record->delete() : cannot delete a record without table and id');
		}
		// try to delete
		$deleted = Database::query()
			->delete()
			->from($this->_['table'])
			->where(array('id'=>$this->_['id']))
			->execute();
		// if it went well
		if($deleted) {
			// return true
			return(true);
		}
		// not deleted
		else {
			// return false
			return(false);
		}
	}
	
}


?>
