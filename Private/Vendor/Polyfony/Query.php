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
use \PDO;

class Query {
	
	// internal attributes
	protected	$Query;
	protected	$Values;
	protected	$Prepared;
	protected	$Success;
	protected	$Result;
	protected	$Array;
	protected	$Action;
	protected	$Hash;
	protected	$Lag;
	
	// attributes to build a query
	protected	$Table;
	protected	$Selects;
	protected	$Joins;
	protected	$Operator;
	protected	$Conditions;
	protected	$Updates;
	protected	$Inserts;
	protected	$Groups;
	protected	$Order;
	protected	$Limit;

	// instanciate a new query
	public function __construct() {

		// set the query as being empty for now
		$this->Query		= null;
		// set an array to store all values to pass to the prepared query
		$this->Values		= array();
		// set the PDO prepare object
		$this->Prepared		= null;
		// set the status of the query
		$this->Success		= false;
		// set the result as being empty for no
		$this->Result		= null;
		// set the array of results as being empty too
		$this->Array		= array();
		// set the main action (INSERT, UPDATE, DELETE, SELECT or QUERY in case of passthru)
		$this->Action		= null;
		// set the unique hash of the query for caching purpose
		$this->Hash			= null;
		// set the maximum age allowed from the cache (in hour), 6h would use caches 6h old at maximum, 0 uses cache since table was not updated
		$this->Lag			= null;
		// initialize attributes
		$this->Table		= null;
		$this->Operator		= 'AND';
		$this->Selects		= array();
		$this->Joins		= array();
		$this->Conditions	= array();
		$this->Updates		= array();
		$this->Inserts		= array();
		$this->Order		= array();
		$this->Limit		= array();
		
	}

	// on garbage collection	
	public function __destruct() {

	}
	
	// passrthu a query with values if needed
	public function query($query, $values=null, $table=null) {
		// if no principal action is set yet
		if(!$this->Action) {
			// set the main action
			$this->Action = 'QUERY';
		}
		// an action has already been set, this is impossible
		else {
			// those actions being incompatible we throw an exception
			Throw new Exception("Query->select() : An incompatible action already exists : {$this->Action}");
		}
		// set the table
		$this->Table = $table;
		// set the query
		$this->Query = $query;
		// set the array of values
		$this->Values = $values;
		// detetect the action
		$action = substr($query, 0, 6);
		// if action can alter a table (INSERT, UPDATE, DELETE)
		if(in_array($action, array('INSERT', 'UPDATE', 'DELETE', 'SELECT'))) {
			// in case of INSERT
			if($action == 'INSERT') {
				// explode after INTO
				list(,$table) = explode('INTO ', $this->Query);
				// isolate the table name
				list($this->Table) = explode(' ', $table);
			}
			// in case of UPDATE
			elseif($action == 'UPDATE') {
				// explode after UPDATE
				list(,$table) = explode('UPDATE ', $this->Query);
				// isolate the table name
				list($this->Table) = explode(' ', $table);
			}
			// in case of DELETE or SELECT
			elseif($action == 'DELETE' || $action == 'SELECT') {
				// explode after FROM 
				list(,$table) = explode('FROM ', $this->Query);
				// isolate the table name
				list($this->Table) = explode(' ', $table);
			}
			// clean the table name from any quotes
			$this->Table = trim($this->Table, '\'/"`');
		}
		// return self to the next method
		return($this);
	}
	
	// first main method
	public function select($array=null) {
		// if no principal action is set yet
		if(!$this->Action) {
			// set the main action
			$this->Action = 'SELECT';
		}
		// an action has already been set, this is impossible
		else {
			// those actions being incompatible we throw an exception
			Throw new Exception("Query->select() : An incompatible action already exists : {$this->Action}");
		}
		// if the argument passed is an array of columns
		if(is_array($array)) {
			// for each column
			foreach($array as $function_or_index => $column) {
				// secure the column name
				list($column, $placeholder) = $this->secure($column);
				// if the key is numeric
				if(is_numeric($function_or_index)) {
					// just select the column
					$this->Selects[] = $column;
				}
				// the key is a SQL function
				else {
					// select the column using a function
					$this->Selects[] = "{$function_or_index}({$placeholder}) AS {$function_or_index}_{$placeholder}";
				}	
			}	
		}
		// return self to the next method
		return($this);
	}
	
	// alias of update
	public function set($columns_and_values) {
		// if provided conditions are an array
		if(is_array($columns_and_values)) {
			// for each provided strict condition
			foreach($columns_and_values as $column => $value) {
				// if the column name not numeric
				if(!is_numeric($column)) {
					// secure the column name
					list($column, $placeholder) = $this->secure($column);
					// save the condition
					$this->Updates[] = "{$column} = :{$placeholder}";
					// save the value (converted if necessary)
					$this->Values[":{$placeholder}"] = $this->convert($column,$value);
				}
				// column name in a number
				else {
					// throw an exception
					Throw new Exception('Query->update() : Column name cannot be a number');
				}
			}
		}
		// return self to the next method
		return($this);
	}
	
	// second main method
	public function update($table) {
		// if no principal action is set yet
		if(!$this->Action) {
			// set the main action
			$this->Action = 'UPDATE';
		}
		// an action has already been set, this is impossible
		else {
			// those actions being incompatible we throw an exception
			Throw new Exception("Query->update() : An incompatible action already exists : {$this->Action}");
		}
		// if the table is a string and is not empty
		if(is_string($table) && !empty($table)) {
			// set the destination table
			list($this->Table) = $this->secure($table);
		}
		// return self to the next method
		return($this);
	}
	
	// insert data
	public function insert($columns_and_values) {
		// if no principal action is set yet
		if(!$this->Action) {
			// set the main action
			$this->Action = 'INSERT';
		}
		// an action has already been set, this is impossible
		else {
			// those actions being incompatible we throw an exception
			Throw new Exception("Query->insert() : An incompatible action already exists : {$this->Action}");
		}
		// check if we have an array
		if(is_array($columns_and_values)) {
			// for each column and value
			foreach($columns_and_values as $column => $value) {
				// if the column is not numeric
				if(!is_numeric($column)) {
					// secure the column name
					list($column) = $this->secure($column);
					// push the column
					$this->Inserts[] = $column;
					// check for automatic conversion and push in place
					$this->Values[] = $this->convert($column, $value);
				}
			}
		}
		// return self to the next method
		return($this);
	}
	
	// delete data from a table
	public function delete() {
		// if no principal action is set yet
		if(!$this->Action) {
			// set the main action
			$this->Action = 'DELETE';
		}
		// an action has already been set, this is impossible
		else {
			// those actions being incompatible we throw an exception
			Throw new Exception("Query->delete() : An incompatible action already exists : {$this->Action}");
		}
		// return self to the next method
		return($this);
	}
	
	// select the table
	public function from($table) {
		// if the table is in string format
		if(is_string($table)) {
			// set the table
			list($this->Table) = $this->secure($table);	
		}
		// wrong type
		else {
			// those actions being incompatible we throw an exception
			Throw new Exception("Query->from() : Wrong parameter type, string expected");
		}
		// return self to the next method
		return($this);
	}
	
	// select another table to join on (implicit INNER JOIN)
	public function join($table, $match, $against) {
		// if table_and_id is an array
		if(!is_string($table) || !$table || !is_string($match) || !$match || !is_string($against) || !$against) {
			// those actions being incompatible we throw an exception
			Throw new Exception("Query->join() : Wrong parameter");
		}
		// secure parameters
		list($table) 	= $this->secure($table);
		list($match) 	= $this->secure($match);
		list($against) 	= $this->secure($against);
		// push the join condition
		$this->Joins[] = "JOIN {$table} ON {$match} = {$against}";
		// return self to the next method
		return($this);
	}

	// select another table to join on (LEFT JOIN)
	public function leftJoin($table, $match, $against) {
		// if table_and_id is an array
		if(!is_string($table) || !$table || !is_string($match) || !$match || !is_string($against) || !$against) {
			// those actions being incompatible we throw an exception
			Throw new Exception("Query->join() : Wrong parameter");
		}
		// secure parameters
		list($table) 	= $this->secure($table);
		list($match) 	= $this->secure($match);
		list($against) 	= $this->secure($against);
		// push the join condition
		$this->Joins[] = "LEFT JOIN {$table} ON {$match} = {$against}";
		// return self to the next method
		return($this);
	}

	// select another table to join on (RIGHT JOIN)
	public function rightJoin($table, $match, $against) {
		// if table_and_id is an array
		if(!is_string($table) || !$table || !is_string($match) || !$match || !is_string($against) || !$against) {
			// those actions being incompatible we throw an exception
			Throw new Exception("Query->join() : Wrong parameter");
		}
		// secure parameters
		list($table) 	= $this->secure($table);
		list($match) 	= $this->secure($match);
		list($against) 	= $this->secure($against);
		// push the join condition
		$this->Joins[] = "RIGHT JOIN {$table} ON {$match} = {$against}";
		// return self to the next method
		return($this);
	}
	
	// add into for inserts
	public function into($table) {
		// if $table is set
		if(is_string($table) && $table) {
			// set the table
			list($this->Table) = $this->secure($table);
		}
		// return self to the next method
		return($this);
	}
	
	public function addAnd() {
		// set the AND
		$this->Operator = 'AND';
		// return self to the next method
		return($this);
	}
	
	public function addOr() {
		// set the OR
		$this->Operator = 'OR';		
		// return self to the next method
		return($this);
	}
	
	// add a condition
	public function where($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// if the column name not numeric
				if(!is_numeric($column)) {
					// secure the column name
					list($column, $placeholder) = $this->secure($column);
					// save the condition
					$this->Conditions[] = "{$this->Operator} ( {$column} = :{$placeholder} )";
					// save the value
					$this->Values[":{$placeholder}"] = $value;
				}
				// column name in a number
				else {
					// throw an exception
					Throw new Exception('Query->where() : Column name cannot be a number');
				}
			}
		}
		// return self to the next method
		return($this);
	}

	// add a condition
	public function whereNot($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// if the column name not numeric
				if(!is_numeric($column)) {
					// secure the column name
					list($column, $placeholder) = $this->secure($column);
					// save the condition
					$this->Conditions[] = "{$this->Operator} ( {$column} <> :{$placeholder} )";
					// save the value
					$this->Values[":{$placeholder}"] = $value;
				}
				// column name in a number
				else {
					// throw an exception
					Throw new Exception('Query->whereNot() : Column name cannot be a number');
				}
			}
		}
		// return self to the next method
		return($this);
	}
	
	// shortcuts
	public function whereStartsWith($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// if the column name not numeric
				if(!is_numeric($column)) {
					// secure the column name
					list($column, $placeholder) = $this->secure($column);
					// save the condition
					$this->Conditions[] = "{$this->Operator} ( {$column} LIKE :{$placeholder} )";
					// save the value
					$this->Values[":{$placeholder}"] = "{$value}%";
				}
				// column name in a number
				else {
					// throw an exception
					Throw new Exception('Query->whereStartsWith() : Column name cannot be a number');
				}
			}
		}
		// return self to the next method
		return($this);
	}
	public function whereEndsWith($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// if the column name not numeric
				if(!is_numeric($column)) {
					// secure the column name
					list($column, $placeholder) = $this->secure($column);
					// save the condition
					$this->Conditions[] = "{$this->Operator} ( {$column} LIKE :{$placeholder} )";
					// save the value
					$this->Values[":{$placeholder}"] = "%$value";
				}
				// column name in a number
				else {
					// throw an exception
					Throw new Exception('Query->whereEndsWith() : Column name cannot be a number');
				}
			}
		}
		// return self to the next method
		return($this);
	}
	public function whereContains($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// if the column name not numeric
				if(!is_numeric($column)) {
					// secure the column name
					list($column, $placeholder) = $this->secure($column);
					// save the condition
					$this->Conditions[] = "{$this->Operator} ( {$column} LIKE :{$placeholder} )";
					// save the value
					$this->Values[":{$placeholder}"] = "%{$value}%";
				}
				// column name in a number
				else {
					// throw an exception
					Throw new Exception('Query->whereContains() : Column name cannot be a number');
				}
			}
		}
		// return self to the next method
		return($this);
	}
	public function whereMatch($conditions) {	
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// if the column name not numeric
				if(!is_numeric($column)) {
					// secure the column name
					list($column, $placeholder) = $this->secure($column);
					// save the condition
					$this->Conditions[] = "{$this->Operator} ( {$column} MATCH :{$placeholder} )";
					// save the value
					$this->Values[":{$placeholder}"] = $value;
				}
				// column name in a number
				else {
					// throw an exception
					Throw new Exception('Query->whereMatch() : Column name cannot be a number');
				}
			}
		}
		// return self to the next method
		return($this);
	}
	public function whereHigherThan($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// if the column name not numeric
				if(!is_numeric($column)) {
					// secure the column name
					list($column, $placeholder) = $this->secure($column);
					// save the condition
					$this->Conditions[] = "{$this->Operator} ( {$column} > :{$placeholder} )";
					// save the value
					$this->Values[":{$placeholder}"] = $value;
				}
				// column name in a number
				else {
					// throw an exception
					Throw new Exception('Query->whereHigherThan() : Column name cannot be a number');
				}
			}
		}
		// return self to the next method
		return($this);
	}
	public function whereLowerThan($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// if the column name not numeric
				if(!is_numeric($column)) {
					// secure the column name
					list($column, $placeholder) = $this->secure($column);
					// save the condition
					$this->Conditions[] = "{$this->Operator} ( {$column} < :{$placeholder} )";
					// save the value
					$this->Values[":{$placeholder}"] = $value;
				}
				// column name in a number
				else {
					// throw an exception
					Throw new Exception('Query->whereLowerThan() : Column name cannot be a number');
				}
			}
		}
		// return self to the next method
		return($this);
	}
	public function whereBetween($column, $lower, $higher) {
		// if column is set
		if($column and !is_numeric($column)) {
			// if lower or higher is numeric
			if(is_numeric($lower) && is_numeric($higher)) {
				// secure the column name
				list($column, $placeholder) = $this->secure($column);
				// save the condition
				$this->Conditions[] = "{$this->Operator} ( {$column} BETWEEN = :min_{$placeholder} AND :max_{$placeholder} )";
				// add the min value
				$this->Values[":min_{$placeholder}"] = $lower;
				// add the max value
				$this->Values[":max_{$placeholder}"] = $higher;
			}
			// min and max are not numeric
			else {
				// throw an exception
			Throw new Exception('Query->whereBetween() : Min or max values must be numbers');	
			}
		}
		// column is incorrect
		else {
			// throw an exception
			Throw new Exception('Query->whereBetween() : Column name is invalid');
		}
		// return self to the next method
		return($this);
	}
	public function whereNull($column) {
		// if column is set
		if($column && !is_numeric($column)) {
			// secure the column name
			list($column, $placeholder) = $this->secure($column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} IS NULL OR {$column} = :empty_{$placeholder} OR {$column} = 0 )";
			// add the empty value
			$this->Values[":empty_{$placeholder}"] = '';
		}
		// column is incorrect
		else {
			// throw an exception
			Throw new Exception('Query->whereNull() : Column name is invalid');
		}
		// return self to the next method
		return($this);
	}
	public function whereNotNull($column) {
		// if column is set
		if($column && !is_numeric($column)) {
			// secure the column name
			list($column) = $this->secure($column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( length({$column}) > 0  )";
		}
		// column is incorrect
		else {
			// throw an exception
			Throw new Exception('Query->whereNotNull() : Column name is invalid');
		}
		// return self to the next method
		return($this);
	}
	public function whereTrue($column) {
		// if column is set
		if($column && !is_numeric($column)) {
			// secure the column name
			list($column) = $this->secure($column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} = 1 OR {$column} = :{$column} )";
			// save the value
			$this->Values[":{$column}"] = 'true';
		}
		// column is incorrect
		else {
			// throw an exception
			Throw new Exception('Query->whereTrue() : Column name is invalid');
		}
		// return self to the next method
		return($this);
	}
	public function whereFalse($column) {
		// if column is set
		if($column && !is_numeric($column)) {
			// secure the column name
			list($column, $placeholder) = $this->secure($column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} IS NULL OR {$column} = 0 OR {$column} = :{$placeholder} OR {$column} = :empty_{$placeholder} )";
			// save the value
			$this->Values[":{$placeholder}"] = 'false';
			// save the value
			$this->Values[":empty_{$placeholder}"] = '';
		}
		// column is incorrect
		else {
			// throw an exception
			Throw new Exception('Query->whereTrue() : Column name is invalid');
		}
		// return self to the next method
		return($this);
	}
	// add an order clause
	public function orderBy($columns_and_direction) {
		// if the parameter is an array
		if(is_array($columns_and_direction)) {
			// for each given parameter
			foreach($columns_and_direction as $column => $direction) {
				// if the column is numeric
				if(is_numeric($column)) {
					// skip it as a wrong parameter has been provided
					continue;	
				}
				// if the direction is not valid
				if($direction != 'ASC' && $direction != 'DESC') {
					// skip it as a wrong parameter has been provided	
					continue;
				}
				// secure the column name
				list($column) = $this->secure($column);
				// push it
				$this->Order[] = "{$column} $direction";
			}
		}
		// return self to the next method
		return($this);
	}
	
	// add a group clause
	public function groupBy($columns) {
		// if the parameter is an array
		if(is_array($columns)) {
			// for each given parameter
			foreach($columns as $column) {
				// secure the column name
				list($column) = $this->secure($column);
				// push it
				$this->Groups[] = $column;
			}
		}
		// return self to the next method
		return($this);
	}
	
	// add a limit clause
	public function limitTo($from,$until) {
		// if both parameters are numric
		if(is_numeric($from) && is_numeric($until)) {
			// build the limit to 
			$this->Limit = array($from, $until);
		}
		// return self to the next method
		return($this);
	}

	// execute the query
	public function execute() {
		// if the action is missing
		if(!$this->Action) {
			// thow an exception
			Throw new Exception('Query->execute() : Missing action');	
		}
		// if action anything but query
		if($this->Action != 'QUERY') {
			// set the first keyword
			$this->Query = $this->Action;
		}
		// if action is insert
		if($this->Action == 'INSERT') {
			// if the table is missing
			if(!$this->Table) {
				// throw an exception
				Throw new Exception('Query->execute() : Missing INTO');
			}
			// if missing values
			if(!$this->Values || !count($this->Values)) {
				// throw an exception
				Throw new Exception('Query->execute() : Missing VALUES');	
			}
			// set destination and columns
			$this->Query .= " INTO $this->Table ( " . implode(', ', $this->Inserts) . " )";
			// set the placeholders
			$this->Query .= " VALUES ( :".trim(implode(', :', $this->Inserts),', ')." )";
		}
		// if action is select
		if($this->Action == 'SELECT') {
			// if the table is missing
			if(!$this->Table) {
				// throw an exception
				Throw new Exception('Query->execute() : Missing FROM');	
			}
			// if columns are set for selection
			if(count($this->Selects)) {
				// assemble all the columns
				$this->Query .= ' ' . implode(', ', $this->Selects).' ';
			}
			// no specific column set for selection
			else {
				// select everything
				$this->Query .= ' * ';
			}
			
		}
		// if the action is delete
		if($this->Action == 'DELETE') {
			// if the table is missing
			if(!$this->Table) {
				// throw an exception
				Throw new Exception('Query->execute() : Missing table to delete from');	
			}
			// set the query
			$this->Query = 'DELETE ';
		}
		// if the action has a from table
		if($this->Action == 'SELECT' || $this->Action == 'DELETE') {
			// add source table
			$this->Query .= "FROM $this->Table";
		}
		// if action is an update
		if($this->Action == 'UPDATE') {
			// if the table is missing
			if(!$this->Table) {
				// throw an exception
				Throw new Exception('Query->execute() : No table to update');	
			}
			// if there is nothing to update
			if(!count($this->Updates)) {
				// throw an exception
				Throw new Exception('Query->execute() : No columns to update');	
			}
			// assemble the updates
			$this->Updates = implode(', ', $this->Updates);
			// prepare the update query
			$this->Query = "UPDATE $this->Table SET $this->Updates";
		}
		// if the select has joined tables
		if($this->Action == 'SELECT' && count($this->Joins)) {
			// assemble the joinds
			$this->Joins = implode(' ', $this->Joins);
			// assemble the query
			$this->Query .= " $this->Joins";
		}
		// if the action needs conditions
		if($this->Action == 'SELECT' || $this->Action == 'UPDATE' || $this->Action == 'DELETE') {
			// if conditions are provided
			if(count($this->Conditions)) {
				// assemble the conditions
				$this->Conditions = trim(implode(' ', $this->Conditions), 'AND /OR ');
				// assemble the query
				$this->Query .= " WHERE $this->Conditions";
			}
		}
		// if groupings options are set
		if($this->Action == 'SELECT' && count($this->Groups)) {
			// assemble groups
			$this->Groups = implode(' , ',$this->Groups);
			// assemble query
			$this->Query .= " GROUP BY $this->Groups";
		}
		// if ordering options are set
		if($this->Action == 'SELECT' && count($this->Order)) {
			// assemble orders
			$this->Order = implode(', ',$this->Order);
			// add ordering to the query
			$this->Query .= " ORDER BY $this->Order";
		}
		// if limit options are set
		if($this->Action == 'SELECT' && count($this->Limit)) {
			// assemble the limit options to the query
			$this->Query .= " LIMIT {$this->Limit[0]},{$this->Limit[1]}";
		}
		// if cache is enabled and query is a SELECT or a passtrhu starting with SELECT
		if(false && ( $this->Action == 'SELECT' || ($this->Action == 'QUERY' && substr($this->Query,0,6) == 'SELECT'))) {
			// check if it exists in cache
			$cached = $this->isInCache();
			// if cache provided actual result
			if($cached !== null) {
				// return the cached data
				return($cached);	
			}
		}
		// prepare the statement
		$this->Prepared = \Polyfony\Database::handle()->prepare($this->Query);
		// if prepare failed
		if(!$this->Prepared) {
			// prepare informations to be thrown
			$exception_infos = implode(":",\Polyfony\Database::handle()->ErrorInfo()).":$this->Query";
			// throw an exception
			Throw new Exception("Query->execute() : Failed to prepare query [{$exception_infos}]");
		}
		// execute the statement
		$this->Success = $this->Prepared->execute($this->Values);
		// if execution failed
		if($this->Success === false) {
			// prepare informations to be thrown
			$exception_infos = implode(":",\Polyfony\Database::handle()->ErrorInfo()).":$this->Query";
			// throw an exception
			Throw new Exception("Query->execute() : Failed to execute query [{$exception_infos}]");
		}
		// fetch all results
		$this->Result = $this->Prepared->fetchAll(
			// fetch as an object
			PDO::FETCH_CLASS,
			// of this specific class
			'\Polyfony\Record',
			// and pass it some arguments
			array(trim($this->Table,"'\"`"))
		);
		// if action was a pathtru and starts with UPDATE, INSERT or DELETE and Table was set and it succeeded
		if($this->Action == 'QUERY' && in_array(substr($this->Query,0,6),array('INSERT','UPDATE','DELETE')) && $this->Table && $this->Success) {
			// we must notify the cache that this table has changed to prevent giving outdated cached data later on
			$this->updateOutdated();
		}
		// if action was DELETE or UPDATE or INSERT and succeeded, it altered a table state
		if(($this->Action == 'UPDATE' || $this->Action == 'DELETE' || $this->Action == 'INSERT') && $this->Success) {
			// we must notify the cache of the new modification date for this table
			$this->updateOutdated();
		}
		// if action succeeded and has some kind of useful result (SELECT or SELECT via a QUERY) and has a table set
		if(($this->Action == 'SELECT' || ($this->Action == 'QUERY' && substr($this->Query,0,6) == 'SELECT')) && $this->Table && $this->Success) {
			// place result in cache
			$this->putInCache();
		}
		// if action was UPDATE or DELETE or one of those via QUERY
		if(in_array($this->Action,array('UPDATE','DELETE')) || ($this->Action == 'QUERY' && in_array(substr($this->Query,0,6),array('UPDATE','DELETE')) && $this->Table)) {
			// return the number of affected rows
			return($this->Prepared->rowCount());
		}
		// if the query was an insert and it succeeded
		if($this->Action == 'INSERT' && $this->Success) {
			// instanciate a new query
			$this->Result = new Query();
			// get the newly inserted element from its id
			return($this->Result
				->select()
				->from($this->Table)
				->where(array('id'=>\Polyfony\Database::handle()->lastInsertId()))
				->execute()
			);
		}
		// return the results
		return($this->Result);
	}
	
	// convert the value depending on the column name
	public static function convert($column,$value) {
		// if we find a file keyword
		if(strpos($column,'_file') !== false) {
			// store that file and replace it by a json value in the database
			// array(path=>null,size=>null,mime=>null)
		}
		// if we find a serialization keyword
		elseif(strpos($column,'_array') !== false) {
			// encode the content as JSON
			$value = json_encode($value);
		}
		// if we are dealing with a date
		elseif(strpos($column,'_date') !== false) {
			// if the date is formated with two slashs
			if(substr_count($value,'/') == 2) {
				// set the separator
				$separator = '/';
			}
			// if the date is formated with two dots
			elseif(substr_count($value,'.') == 2) {
				// set the separator
				$separator = '.';
			}
			// if the date is formated with two -
			elseif(substr_count($value,'-') == 2) {
				// set the separator
				$separator = '-';
			}
			// separator in unknown, we assume it is already a timestamp
			else {
				// clean it just to make sure
				$value = preg_replace('/\D/','',$value);
			}
			// if we know the separator
			if(isset($separator)) {
				// explode the date's elements
				list($day,$month,$year) = explode($separator,$value);
				// create a timestamp
				$value = mktime(0,0,1,$month,$day,$year);
			}
		}
		// return the value
		return($value);
	}
	
	private function putInCache() {

	}

	private function updateOutdated() {
		
	}

	// secure a column name and get a placeholder for that column
    private function secure($column) {
        // apply the secure regex for the column name
        $column = preg_replace('/[^a-zA-Z0-9_\.]/', '', $column);    
        // apply the alias regex for the placeholder
        $placeholder = str_replace('.', '_', strtolower($column)); 
        // return cleaned column
        return(array($column, $placeholder));
    }
	
}


?>
