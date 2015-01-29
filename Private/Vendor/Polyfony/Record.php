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
			'id'		=> isset($this->id) ? $this->id : null,
			// table of the record
			'table'		=> $table ?: null,
			// list of altered columns since the retrieval from the database
			'altered'	=> array()
		);
		// if conditions are provided
		if($conditions !== null) {
			// if conditions is not an array
			if(!is_array($conditions)) {
				// we assume it is the id of the record
				$conditions = array('id'=>$conditions);	
			}
			// grab that object from the database
			$record = Database::query()
				->select()
				->first()
				->from($this->_['table'])
				->where($conditions)
				->execute();
			// if the record is found
			if($record) {
				// clone the found record
				$this->replicate($record);
				// return self
				return($this);	
			}
			else {
				// return false
				Throw new Exception('Record->__construct() No matching record in the database', 404);
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
		// replicate the id if it is available
		$this->_['id'] = isset($this->id) ? $this->id : $this->_['id'];
	}
	
	public function get($column, $raw=false) {
		// return the columns or null if it does not exist		
		return(isset($this->{$column}) ? $this->convert($column, $raw) : null);
	}
	
	private function field($column) {
		return("{$this->_['table']}[$column]");
	}

	public function input($column, $options=array()) {
		return(Form::input(
			$this->field($column), 
			$this->get($column), 
			$options
		));
	}
	
	public function password($column, $options=array()) {
		return(Form::password(
			$this->field($column), 
			$this->get($column), 
			$options
		));
	}
	
	public function textarea($column, $options=array()) {
		return(Form::textarea(
			$this->field($column), 
			$this->get($column), 
			$options
		));
	}
	
	public function select($column, $list=array(), $options=array()) {
		return(Form::select(
			$this->field($column), 
			$list, 
			$this->get($column), 
			$options
		));
	}
	
	public function checkbox($column, $options=array()) {
		return(Form::checkbox(
			$this->field($column), 
			$this->get($column), 
			$options
		));
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
		// what to iterate on
		$attributes = $altered ? $this->_['altered'] : array_keys(get_object_vars($this));
		// for each attribute of this object
		foreach($attributes as $attribute){
			// if the attribute is internal
			if($attribute == '_') {
				// skip it
				continue;
			}
			// normal attribute
			else {
				// convert or not
				$array[$attribute] = $raw ? $this->get($attribute,true) : $this->get($attribute,false);
			}
		}
		return($array);
	}
	
	// magic
	public function __toString() {
		// a string to sybolize this record
		return($this->_['id'] ? $this->_['id'] : 0);
	}
	
	private function alter($column) {
		// push
		$this->_['altered'][] = $column;
		// deduplicate
		$this->_['altered'] = array_unique($this->_['altered']);
	}
	
	private function convert($column, $raw=false) {
		
		// if we want the raw result ok, but exclude arrays that can never be gotten raw
		if($raw && strpos($column,'_array') === false) {
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
				->set($this->__toArray(true, true))
				->where(array('id'=>$this->_['id']))
				->execute();
			// if update went well
			return $updated ? true : false;
		}
		// this is a new record
		else {
			// try to insert it
			$inserted = Database::query()
				->insert($this->__toArray(true, true))
				->into($this->_['table'])
				->execute();
			// if insertion succeeded clone ourselves and return true
			if($inserted) {
				// replicate
				$this->replicate($inserted);
				// return success
				return true;
			}
			// didnt insert
			else {
				// failure feedback
				return false;
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
		return $deleted ? true : false;
	}
	
}


?>
