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
	
	// storing variable that does not reflect the database table structure
	protected $_;
	
	// storing the validators
	const VALIDATORS = [];

	// create a object from scratch, of fetch it in from its table/id
	public function __construct($table=null, $conditions=null) {

		// if this has been inherited in a child class
		if(get_class($this) != 'Polyfony\Record') {

			// use the table parameter as conditions and ignore the conditions parameter
			$conditions = $table ?: null;

			// use the class name as table name
			$table = str_replace('Models\\','',get_class($this));

		}

		// init the list of altered columns
		$this->_ = [
			// if of the record
			'id'		=> isset($this->id) ? $this->id : null,
			// table of the record
			'table'		=> $table ?: null,
			// list of altered columns since the retrieval from the database
			'altered'	=> []
		];
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
				Throw new Exception(get_class($this)."->__construct() No matching record in table {$this->_['table']}", 404);
			}
		}
		// return self
		return($this);
		
	}
	
	private function replicate($clone) {
		// for each attribute
		foreach(get_object_vars($clone) as $attribute => $value) {
			// clone that attribute
			$this->{$attribute} = $value;
		}
		// replicate the id if it is available
		$this->_['id'] = isset($this->id) ? $this->id : $this->_['id'];
	}
	
	public function get($column, $raw = false) {
		// return the columns or null if it does not exist		
		return(isset($this->{$column}) ? $this->convert($column, $raw) : null);
	}
	
	private function field($column) {
		return("{$this->_['table']}[$column]");
	}

	public function input($column, $options = []) {
		return(Form::input(
			$this->field($column), 
			$this->get($column), 
			$options
		));
	}
	
	public function textarea($column, $options = []) {
		return(Form::textarea(
			$this->field($column), 
			$this->get($column), 
			$options
		));
	}
	
	public function select($column, $list = [], $options = []) {
		return(Form::select(
			$this->field($column), 
			$list, 
			$this->get($column), 
			$options
		));
	}
	
	public function checkbox($column, $options = array()) {
		return(Form::checkbox(
			$this->field($column), 
			$this->get($column), 
			$options
		));
	}
	
	public function set($column_or_array, $value = null) {
		// if we want to set a batch of values
		if(is_array($column_or_array)) {
			// for each value to set
			foreach($column_or_array as $column => $value) {
				// set that individual column
				$this->set($column, $value);
			}
		}
		// setting only a single value
		else {
			// validate the attribute
			$this->validateAttribute($column_or_array, $value);
			// convert the value depending on the column name
			$this->{$column_or_array} = Query::convert($column_or_array, $value);
			// update the altered list
			$this->alter($column_or_array);
		}
		// return self
		return($this);
	}
	
	// magic
	public function __toArray($raw = false, $altered = false) {
		// declare an empty array
		$array = [];
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
		return $array;
	}
	
	// magic
	public function __toString() {
		// a string to sybolize this record
		return $this->_['id'] ? $this->_['id'] : 0;
	}

	// magic
	public function __clone() {
		// set all columns as altered
		$this->_['altered'] = array_keys(get_object_vars($this));
		// remove the hidden column
		unset($this->_['altered'][0]);
		// remove the hidden id, so that the object is recognized as absent from the database
		$this->_['id'] = null;
		// remove the id attribute too
		$this->id = null;
	}
	
	private function validateAttribute($column, $value) {
		// get the allowed null values
		$allowed_nulls = Database::describe($this->_['table']);
		// get the class of the object
		$class_name = get_class($this);
		// get the validators
		$validators = $class_name::VALIDATORS;
		// get the validator
		$validator = isset($validators[$column]) ? 
			$validators[$column] : null;
		// check if the column exists
		if(!array_key_exists($column, $allowed_nulls)) {
			// throw a useful exception
			Throw new Exception(get_class($this).'->set('.$column.') : column does not exist');
		}
		// check if the value is null/empty and that it is not allowed to have such a value
		if($allowed_nulls[$column] == false && (is_null($value) || $value === '')) {
			// throw a useful exception
			Throw new Exception(get_class($this).'->set('.$column.') : cannot be null');
		}
		// check if the value is not null and a validator exists
		if(!is_null($value) && $validator && ((is_string($validator) && !preg_match($validator, $value)) || (is_array($validator) && !in_array($value, array_keys($validator)))) )  {
			// throw a useful exception
			Throw new Exception(get_class($this).'->set('.$column.') : does not conform to the REGEX or is not in the allowed array');
		}
	}

	private function alter($column) {
		// push
		$this->_['altered'][] = $column;
		// deduplicate
		$this->_['altered'] = array_unique($this->_['altered']);
	}
	
	private function convert($column, $raw = false) {
		
		// if we want the raw result ok, but exclude arrays that can never be gotten raw
		if($raw === true && strpos($column,'_array') === false) {
			// return as is
			return $this->{$column};
		}
		// otherwise convert it
		// if the column contains an array
		if(strpos($column,'_array') !== false) {
			// decode the array
			return json_decode($this->{$column},true);
		}
		// if the column contains a size value
		elseif(strpos($column,'_size') !== false) {
			// convert to human size
			return Format::size($this->{$column});
		}
		// if the column contains a datetime
		elseif(strpos($column,'_datetime') !== false) {
			// if the value is set
			return !empty($this->{$column}) ? date('d/m/Y H:i', $this->{$column}) : '';
		}
		// if the column contains a date
		elseif(strpos($column,'_date') !== false || substr($column,-3,3) == '_at') {
			// if the value is set
			return !empty($this->{$column}) ? date('d/m/Y', $this->{$column}) : '';
		}
		// not a magic column
		else {
			// return as is
			return $this->{$column};
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
				->where(['id'=>$this->_['id']])
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
			Throw new Exception(get_class($this).'->delete() : cannot delete a record without table and id');
		}
		// try to delete
		$deleted = Database::query()
			->delete()
			->from($this->_['table'])
			->where(['id'=>$this->_['id']])
			->execute();
		// if it went well
		return $deleted ? true : false;
	}


	// returns the name of the class that has extended this one (aka, the Table name)
	private static function tableName() :string {

		// removed the namespace from the class name
		return str_replace('Models\\','',get_called_class());

	}

	// shortcut to insert an element
	public static function create(array $columns_and_values=[]) {

		return Database::query()
			->insert($columns_and_values)
			->into(self::tableName())
			->execute();

	}

	// shortcut that bootstraps a select query
	public static function _select(array $select=[]) :Query {

		// returns a Query object, to execute, or to complement with some more parameters
		return Database::query()
			->select($select)
			->from(self::tableName());

	}

	// shortcut that bootstraps an update query
	public static function _update(array $columns_and_values=[]) :Query {

		// returns a Query object, to execute, or to complement with some more parameters
		return Database::query()
			->update(self::tableName())
			->set($columns_and_values);

	}

	// shortcut that bootstraps a delete query
	public static function _delete() :Query {

		// returns a Query object, to execute, or to complement with some more parameters
		return Database::query()
			->delete()
			->from(self::tableName());

	}


	
}


?>
