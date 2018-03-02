<?php
 
namespace Polyfony;
use \PDO;

class Query {
	
	// internal attributes
	protected	$Query;
	protected	$Values;
	protected	$Prepared;
	protected	$Success;
	protected	$Result;
	protected	$Action;
	protected	$Hash;
	protected	$Quote;
	
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
	public function __construct($quote = '') {

		// set the query as being empty for now
		$this->Query		= null;
		// set an array to store all values to pass to the prepared query
		$this->Values		= [];
		// set the PDO prepare object
		$this->Prepared		= null;
		// set the status of the query
		$this->Success		= false;
		// set the option to return all results, not only the first
		$this->First 		= false;
		// set the result as being empty for no
		$this->Result		= null;
		// set the main action (INSERT, UPDATE, DELETE, SELECT or QUERY in case of passthru)
		$this->Action		= null;
		// set the unique hash of the query for caching purpose
		$this->Hash			= null;
		// set the quote symbol (if any to use for columns)
		$this->Quote 		= $quote;

		// initialize attributes
		$this->Object		= 'Record';
		$this->Table		= null;
		$this->Operator		= 'AND';
		$this->Selects		= [];
		$this->Joins		= [];
		$this->Conditions	= [];
		$this->Updates		= [];
		$this->Inserts		= [];
		$this->Groups		= [];
		$this->Order		= [];
		$this->Limit		= [];
		
	}
	
	// debugging methods (for the Profiler)
	public function getQuery() {
		return $this->Query;
	}
	// debugging methods (for the Profiler)
	public function getValues() {
		return $this->Values;
	}

	// passrthu a query with values if needed
	public function query($query, $values=null, $table=null) {
		// set the main action
		$this->action('QUERY');
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
		return $this;
	}
	
	// first main method
	public function select($array=null) {
		// set the main action
		$this->action('SELECT');
		// if the argument passed is an array of columns
		if(is_array($array)) {
			// for each column
			foreach($array as $function_or_index_or_column => $column) {
				// secure the column name
				list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column, true);
				// if the key is function_or_index_or_column
				if(is_numeric($function_or_index_or_column)) {
					// just select the column
					$this->Selects[] = $column;
				}
				// the key contains a dot, we are trying to create a alias
				elseif(stripos($function_or_index_or_column, '.') !== false) {
					// secure the column
					list($column) = Query\Convert::columnToPlaceholder($this->Quote ,$function_or_index_or_column);
					// select the column and create an alias
					$this->Selects[] = "{$column} AS {$placeholder}";
				}
				// the key is a SQL function
				else {
					// secure the function
					list($function) = Query\Convert::columnToPlaceholder($this->Quote ,$function_or_index_or_column);
					// select the column using a function
					$this->Selects[] = "{$function}({$column}) AS {$function}_{$placeholder}";
				}

			}	
		}
		// return self to the next method
		return $this;
	}
	
	// alias of update
	public function set($columns_and_values) {
		// if provided conditions are an array
		if(is_array($columns_and_values)) {
			// for each provided strict condition
			foreach($columns_and_values as $column => $value) {
				// secure the column name
				list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// save the condition
				$this->Updates[] = "{$column} = :{$placeholder}";
				// save the value (converted if necessary)
				$this->Values[":{$placeholder}"] = Query\Convert::valueForDatabase($column,$value);
			}
		}
		// return self to the next method
		return $this;
	}
	
	// second main method
	public function update($table) {
		// set the main action
		$this->action('UPDATE');
		// set the destination table
		list($this->Table) = Query\Convert::columnToPlaceholder($this->Quote ,$table);
		// return self to the next method
		return $this;
	}
	
	// insert data
	public function insert($columns_and_values) {
		// set the main action
		$this->action('INSERT');
		// check if we have an array
		if(is_array($columns_and_values)) {
			// for each column and value
			foreach($columns_and_values as $column => $value) {
				// secure the column name
				list($column) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// push the column
				$this->Inserts[] = $column;
				// check for automatic conversion and push in place
				$this->Values[] = Query\Convert::valueForDatabase($column, $value);
			}
		}
		// return self to the next method
		return $this;
	}
	
	// delete data from a table
	public function delete() {
		// set the main action
		$this->action('DELETE');
		// return self to the next method
		return $this;
	}
	
	// select the table
	public function from($table) {
		// set the table
		list($this->Table) = Query\Convert::columnToPlaceholder($this->Quote ,$table);	
		// return self to the next method
		return $this;
	}
	
	// set the type of object that we want to be instanciated
	public function object($class) {
		// set the main action
		$this->Object = $class;
		// return self to the next method
		return $this;
	}

	// select another table to join on (implicit INNER JOIN)
	public function join($table, $match, $against) {
		// secure parameters
		list($table) 	= Query\Convert::columnToPlaceholder($this->Quote ,$table);
		list($match) 	= Query\Convert::columnToPlaceholder($this->Quote ,$match);
		list($against) 	= Query\Convert::columnToPlaceholder($this->Quote ,$against);
		// push the join condition
		$this->Joins[] = "JOIN {$table} ON {$match} = {$against}";
		// return self to the next method
		return $this;
	}

	// select another table to join on (LEFT JOIN)
	public function leftJoin($table, $match, $against) {
		// secure parameters
		list($table) 	= Query\Convert::columnToPlaceholder($this->Quote ,$table);
		list($match) 	= Query\Convert::columnToPlaceholder($this->Quote ,$match);
		list($against) 	= Query\Convert::columnToPlaceholder($this->Quote ,$against);
		// push the join condition
		$this->Joins[] = "LEFT JOIN {$table} ON {$match} = {$against}";
		// return self to the next method
		return $this;
	}

	// select another table to join on (RIGHT JOIN)
	public function rightJoin($table, $match, $against) {
		// secure parameters
		list($table) 	= Query\Convert::columnToPlaceholder($this->Quote ,$table);
		list($match) 	= Query\Convert::columnToPlaceholder($this->Quote ,$match);
		list($against) 	= Query\Convert::columnToPlaceholder($this->Quote ,$against);
		// push the join condition
		$this->Joins[] = "RIGHT JOIN {$table} ON {$match} = {$against}";
		// return self to the next method
		return $this;
	}
	
	// add into for inserts
	public function into($table) {
		// set the table
		list($this->Table) = Query\Convert::columnToPlaceholder($this->Quote ,$table);
		// return self to the next method
		return $this;
	}
	
	public function addAnd() {
		// set the AND
		$this->Operator = 'AND';
		// return self to the next method
		return $this;
	}
	
	public function addOr() {
		// set the OR
		$this->Operator = 'OR';		
		// return self to the next method
		return $this;
	}
	
	// add a condition
	public function where($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// secure the column name
				list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// save the condition
				$this->Conditions[] = "{$this->Operator} ( {$column} = :{$placeholder} )";
				// save the value
				$this->Values[":{$placeholder}"] = $value;
			}
		}
		// return self to the next method
		return $this;
	}

	// add a condition
	public function whereNot($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// secure the column name
				list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// save the condition
				$this->Conditions[] = "{$this->Operator} ( {$column} <> :{$placeholder} )";
				// save the value
				$this->Values[":{$placeholder}"] = $value;
			}
		}
		// return self to the next method
		return $this;
	}
	
	// shortcuts
	public function whereStartsWith($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// secure the column name
				list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// save the condition
				$this->Conditions[] = "{$this->Operator} ( {$column} LIKE :{$placeholder} )";
				// save the value
				$this->Values[":{$placeholder}"] = "{$value}%";
			}
		}
		// return self to the next method
		return $this;
	}
	public function whereEndsWith($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// secure the column name
				list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// save the condition
				$this->Conditions[] = "{$this->Operator} ( {$column} LIKE :{$placeholder} )";
				// save the value
				$this->Values[":{$placeholder}"] = "%$value";
			}
		}
		// return self to the next method
		return $this;
	}
	public function whereContains($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// secure the column name
				list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// save the condition
				$this->Conditions[] = "{$this->Operator} ( {$column} LIKE :{$placeholder} )";
				// save the value
				$this->Values[":{$placeholder}"] = "%{$value}%";
			}
		}
		// return self to the next method
		return $this;
	}
	public function whereMatch($conditions) {	
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// secure the column name
				list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// save the condition
				$this->Conditions[] = "{$this->Operator} ( {$column} MATCH :{$placeholder} )";
				// save the value
				$this->Values[":{$placeholder}"] = $value;
			}
		}
		// return self to the next method
		return $this;
	}
	public function whereHigherThan($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// secure the column name
				list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// save the condition
				$this->Conditions[] = "{$this->Operator} ( {$column} > :{$placeholder} )";
				// save the value
				$this->Values[":{$placeholder}"] = $value;
			}
		}
		// return self to the next method
		return $this;
	}
	public function whereLowerThan($conditions) {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each provided strict condition
			foreach($conditions as $column => $value) {
				// secure the column name
				list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// save the condition
				$this->Conditions[] = "{$this->Operator} ( {$column} < :{$placeholder} )";
				// save the value
				$this->Values[":{$placeholder}"] = $value;
			}
		}
		// return self to the next method
		return $this;
	}
	public function whereBetween($column, $lower, $higher) {
		// if lower or higher is numeric
		if(is_numeric($lower) && is_numeric($higher)) {
			// secure the column name
			list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} BETWEEN :min_{$placeholder} AND :max_{$placeholder} )";
			// add the min value
			$this->Values[":min_{$placeholder}"] = $lower;
			// add the max value
			$this->Values[":max_{$placeholder}"] = $higher;
		}
		// min and max are not numeric
		else { Throw new Exception('Query->whereBetween() : Min or max values must be numbers'); }
		// return self to the next method
		return $this;
	}
	public function whereEmpty($column) {
		// secure the column name
		list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
		// save the condition
		$this->Conditions[] = "{$this->Operator} ( {$column} == :empty_{$placeholder} OR {$column} IS NULL )";
		// add the empty value
		$this->Values[":empty_{$placeholder}"] = '';
		// return self to the next method
		return $this;
	}
	public function whereNotEmpty($column) {
		// secure the column name
		list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
		// save the condition
		$this->Conditions[] = "{$this->Operator} ( {$column} <> :empty_{$placeholder} AND {$column} IS NOT NULL )";
		// add the empty value
		$this->Values[":empty_{$placeholder}"] = '';
		// return self to the next method
		return $this;
	}
	public function whereNull($column) {
		// secure the column name
		list($column, $placeholder) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
		// save the condition
		$this->Conditions[] = "{$this->Operator} ( {$column} IS NULL )";
		// add the empty value
		$this->Values[":empty_{$placeholder}"] = '';
		// return self to the next method
		return $this;
	}
	public function whereNotNull($column) {
		// secure the column name
		list($column) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
		// save the condition
		$this->Conditions[] = "{$this->Operator} ( {$column} IS NOT NULL )";
		// return self to the next method
		return $this;
	}
	// add an order clause
	public function orderBy($columns_and_direction) {
		// if the parameter is an array
		if(is_array($columns_and_direction)) {
			// for each given parameter
			foreach($columns_and_direction as $column => $direction) {
				// if the direction is not valid force ASC
				$direction == 'ASC' || $direction == 'DESC' ?: $direction = 'ASC';
				// secure the column name
				list($column) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// push it
				$this->Order[] = "{$column} $direction";
			}
		}
		// return self to the next method
		return $this;
	}
	
	// add a group clause
	public function groupBy($columns) {
		// if the parameter is an array
		if(is_array($columns)) {
			// for each given parameter
			foreach($columns as $column) {
				// secure the column name
				list($column) = Query\Convert::columnToPlaceholder($this->Quote ,$column);
				// push it
				$this->Groups[] = $column;
			}
		}
		// return self to the next method
		return $this;
	}
	
	// add a limit clause
	public function limitTo($from,$until) {
		// if both parameters are numric
		if(is_numeric($from) && is_numeric($until)) {
			// build the limit to 
			$this->Limit = array($from, $until);
		}
		// return self to the next method
		return $this;
	}

	// return only the first result
	public function first() {
		// return only the first record
		$this->First = true;
		// artificially restrict the number of results to one
		$this->limitTo(0,1);
		// return self query
		return $this;
	}

	// execute the query
	public function execute() {
		
		// build the query
		$this->buildQuery();

		// prepare the statement
		$this->Prepared = \Polyfony\Database::handle()->prepare($this->Query);
		
		// marker start (after preparation, otherwise we would not be able to read the query, it wouldn't exist yet!)
		$id_marker = Profiler::setMarker(null, 'database', ['Query'=>$this]);

		// if prepare failed
		if(!$this->Prepared) {
			// throw an exception
			$this->throwExceptionOn('prepare');
		}
		
		// execute the statement
		$this->Success = $this->Prepared->execute($this->Values);
		
		// if execution failed
		if($this->Success === false) {
			// throw an exception
			$this->throwExceptionOn('execute');
		}

		// if a forced type of object has been defined
		$this->Object = $this->Object && $this->Object != 'Record' ? 
			'\Models\\'.$this->Object : 
			'\Models\\'.$this->Table;
		
		// fetch all results as objects
		$this->Result = $this->Prepared->fetchAll(PDO::FETCH_CLASS, $this->Object);

		// format the result
		$this->formatSelectResult();
		$this->formatUpdateOrDeleteResult();
		$this->formatInsertResult();

		// marker end, release after all manipulation has been done
		Profiler::releaseMarker($id_marker);

		// return whatever result we got
		return $this->Result;
	}

	public function getExecutedAction() {
		// return the first 6 letters of the query
		return $this->Action ? $this->Action : substr(trim($this->Query),0,6);
	}

	private function formatUpdateOrDeleteResult() {
		// actions of type UPDATE or DELETE
		if(in_array($this->getExecutedAction(),['UPDATE','DELETE'])) {
			// return the number of affected rows
			$this->Result = $this->Prepared->rowCount();
		}
	}

	private function formatInsertResult() :void {
		// actions of type INSERT
		if($this->Action == 'INSERT') {
			$this->Result = $this->Success ? 
				new $this->Object(\Polyfony\Database::handle()->lastInsertId()) : false;
		}
	}

	private function formatSelectResult() :void {
		// actions of type SELECT & first element
		if($this->Action == 'SELECT' && $this->First) {
			$this->Result = isset($this->Result[0]) ? 
				$this->Result[0] : false;
		}
	}

	private function buildQuery() :void {

		// if the action is missing
		if(!$this->Action) { Throw new Exception('Query->buildQuery() : Missing action'); }
		// if action anything but query
		if($this->Action != 'QUERY') {
			// set the first keyword
			$this->Query = $this->Action;
		}
		// if action is insert
		if($this->Action == 'INSERT') {
			// if the table is missing
			if(!$this->Table) { Throw new Exception('Query->buildQuery() : Missing INTO'); }
			// if missing values
			if(!$this->Values || !count($this->Values)) { Throw new Exception('Query->execute() : Missing VALUES');}
			// set destination and columns
			$this->Query .= " INTO $this->Table ( " . implode(', ', $this->Inserts) . " )";
			// set the placeholders
			$this->Query .= " VALUES ( :".trim(implode(', :', $this->Inserts),', ')." )";
		}
		// if action is select
		if($this->Action == 'SELECT') {
			// if the table is missing
			if(!$this->Table) { Throw new Exception('Query->buildQuery() : Missing FROM'); }
			// if columns are set for selection
			$this->Query .= count($this->Selects) ? ' ' . implode(', ', $this->Selects) . ' ' : ' * ';
		}
		// if the action is delete
		if($this->Action == 'DELETE') {
			// if the table is missing
			if(!$this->Table) { Throw new Exception('Query->buildQuery() : Missing table to delete from'); }
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
			if(!$this->Table) { Throw new Exception('Query->buildQuery() : No table to update'); }
			// if there is nothing to update
			if(!count($this->Updates)) { Throw new Exception('Query->buildQuery() : No columns to update'); }
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

	}

    // set the action internally
    private function action($action_name) {
    	// if no principal action is set yet or is the same
		if(!$this->Action || $action_name == $this->Action) {
			// set the main action
			$this->Action = $action_name;
		}
		// an action has already been set, this is impossible
		else { Throw new Exception("Query->action({$action_name}) : An incompatible action already exists : {$this->Action}"); }
    }

    private function throwExceptionOn(string $occured_on) {
    	// prepare informations to be thrown
		$exception_infos = implode(":",\Polyfony\Database::handle()->ErrorInfo()).":$this->Query";
		// throw an exception
		Throw new Exception("Query->{$occured_on}() failed because : {$exception_infos}");
    }
	
}


?>
