<?php
 
namespace Polyfony;
use \PDO;
use Polyfony\Query\Convert as Convert;

class Query extends Query\Conditions {
	
	// debugging methods (for the Profiler)
	public function getQuery() {
		return $this->Query;
	}
	// debugging methods (for the Profiler)
	public function getValues() {
		return $this->Values;
	}

	public function detectActionAndTable() {
		// detetect the action
		$action = substr($this->Query, 0, 6);
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
	}

	// passrthu a query with values if needed
	public function query(
		string $query, 
		$values=null, 
		$table=null
	) {
		// set the main action
		$this->action('QUERY');
		// set the table
		$this->Table = $table;
		// set the query
		$this->Query = $query;
		// set the array of values
		$this->Values = $values;
		// // detetect the action
		$this->detectActionAndTable();
		// return self to the next method
		return $this;
	}
	
	// first main method
	public function select(array $array=[]) {
		// set the main action
		$this->action('SELECT');
		// for each column
		foreach($array as $function_or_index_or_column => $column) {
			// secure the column name
			list(
				$column, 
				$placeholder
			) = Convert::columnToPlaceholder($this->Quote ,$column, true);
			// if the key is function_or_index_or_column
			if(is_numeric($function_or_index_or_column)) {
				// just select the column
				$this->Selects[] = $column;
			}
			// the key contains a dot, we are trying to create a alias
			elseif(stripos($function_or_index_or_column, '.') !== false) {
				// secure the column
				list($column) = Convert::columnToPlaceholder($this->Quote ,$function_or_index_or_column, true);
				// select the column and create an alias
				$this->Selects[] = "{$column} AS {$placeholder}";
			}
			// the key is a SQL function
			else {
				// secure the function
				list($function) = Convert::columnToPlaceholder($this->Quote ,$function_or_index_or_column);
				// select the column using a function
				$this->Selects[] = "{$function}({$column}) AS {$function}_{$placeholder}";
			}
		}
		// return self to the next method
		return $this;
	}
	
	// alias of update
	public function set(array $columns_and_values) {
		// for each provided strict condition
		foreach($columns_and_values as $column => $value) {
			// secure the column name
			list($column, $placeholder) = Convert::columnToPlaceholder($this->Quote ,$column, false, 'set');
			// save the condition
			$this->Updates[] = "{$column} = :{$placeholder}";
			// save the value (converted if necessary)
			$this->Values[":{$placeholder}"] = Convert::valueForDatabase($column,$value);
		}
		// return self to the next method
		return $this;
	}
	
	// second main method
	public function update(string $table) {
		// set the main action
		$this->action('UPDATE');
		// set the destination table
		list($this->Table) = Convert::columnToPlaceholder($this->Quote ,$table);
		// return self to the next method
		return $this;
	}
	
	// insert data
	public function insert(array $columns_and_values) {
		// set the main action
		$this->action('INSERT');
		// for each column and value
		foreach($columns_and_values as $column => $value) {
			// secure the column name
			list($column) = Convert::columnToPlaceholder($this->Quote ,$column);
			// push the column
			$this->Inserts[] = $column;
			// check for automatic conversion and push in place
			$this->Values[] = Convert::valueForDatabase($column, $value);
		}
		// return self to the next method
		return $this;
	}
	
	// delete data from a table
	public function delete() :self {
		// set the main action
		$this->action('DELETE');
		// return self to the next method
		return $this;
	}
	
	// select the table
	public function from(string $table) :self {
		// set the table
		list($this->Table) = Convert::columnToPlaceholder($this->Quote ,$table);	
		// return self to the next method
		return $this;
	}
	
	// set the type of object that we want to be instanciated
	public function object(string $class) :self {
		// set the main action
		$this->Object = $class;
		// return self to the next method
		return $this;
	}

	// select another table to join on (implicit INNER JOIN)
	public function join(
		string $table, 
		string $match, 
		string $against
	) :self {
		// secure parameters
		list($table) 	= Convert::columnToPlaceholder($this->Quote ,$table);
		list($match) 	= Convert::columnToPlaceholder($this->Quote ,$match);
		list($against) 	= Convert::columnToPlaceholder($this->Quote ,$against);
		// push the join condition
		$this->Joins[] = "JOIN {$table} ON {$match} = {$against}";
		// return self to the next method
		return $this;
	}

	// select another table to join on (LEFT JOIN)
	public function leftJoin(
		string $table, 
		string $match, 
		string $against
	) :self {
		// secure parameters
		list($table) 	= Convert::columnToPlaceholder($this->Quote ,$table);
		list($match) 	= Convert::columnToPlaceholder($this->Quote ,$match);
		list($against) 	= Convert::columnToPlaceholder($this->Quote ,$against);
		// push the join condition
		$this->Joins[] = "LEFT JOIN {$table} ON {$match} = {$against}";
		// return self to the next method
		return $this;
	}

	// select another table to join on (RIGHT JOIN)
	public function rightJoin(
		string $table, 
		string $match, 
		string $against
	) :self {
		// secure parameters
		list($table) 	= Convert::columnToPlaceholder($this->Quote ,$table);
		list($match) 	= Convert::columnToPlaceholder($this->Quote ,$match);
		list($against) 	= Convert::columnToPlaceholder($this->Quote ,$against);
		// push the join condition
		$this->Joins[] = "RIGHT JOIN {$table} ON {$match} = {$against}";
		// return self to the next method
		return $this;
	}
	
	// add into for inserts
	public function into(string $table) :self {
		// set the table
		list($this->Table) = Convert::columnToPlaceholder($this->Quote ,$table);
		// return self to the next method
		return $this;
	}
	
	public function addAnd() :self {
		// set the AND
		$this->Operator = 'AND';
		// return self to the next method
		return $this;
	}
	
	public function addOr() :self {
		// set the OR
		$this->Operator = 'OR';		
		// return self to the next method
		return $this;
	}
	
	// add an order clause
	public function orderBy(
		array $columns_and_direction
	) :self {
		// for each given parameter
		foreach($columns_and_direction as $column => $direction) {
			// if the direction is not valid force ASC
			$direction == 'ASC' || $direction == 'DESC' ?: $direction = 'ASC';
			// secure the column name
			list($column) = Convert::columnToPlaceholder($this->Quote ,$column);
			// push it
			$this->Order[] = "{$column} $direction";
		}
		// return self to the next method
		return $this;
	}
	
	// add a group clause
	public function groupBy(array $columns) :self {
		// for each given parameter
		foreach($columns as $column) {
			// secure the column name
			list($column) = Convert::columnToPlaceholder($this->Quote ,$column);
			// push it
			$this->Groups[] = $column;
		}
		// return self to the next method
		return $this;
	}
	
	// add a limit clause
	public function limitTo(int $from, int $until) :self {
		// build the limit to 
		$this->Limit = array($from, $until);
		// return self to the next method
		return $this;
	}

	// return only the first result, and does it right now
	public function get() :?Entity {
		// all in one 
		return $this
			->first()
			->execute();

	}

	// return only the first result
	public function first() :self {
		// return only the first Entity
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
		$this->Object = $this->Object && $this->Object != 'Entity' ? 
			'\Models\\'.$this->Object : 
			'\Models\\'.$this->Table;

		// temporary fix, if we don't have an object class at all
		if($this->Object == '\Models\\') {
			$this->Object = '\Polyfony\\Entity';
		}

		// tweak for Microsoft SQL Server and MySQL
		$this->Object = str_replace('"','', $this->Object);
		
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
		// handle passthru select properly
		// actions of type SELECT & first element
		if(
			(
				$this->Action == 'SELECT' || 
				$this->getExecutedAction()
			) && 
			$this->First
		) {
			$this->Result = isset($this->Result[0]) ? 
				$this->Result[0] : null;
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
			if(!$this->Table) { Throw new Exception(
				'Query->buildQuery() : No table to update'
			); }
			// if there is nothing to update
			if(!count($this->Updates)) { Throw new Exception(
				'Query->buildQuery() : No changes have been made, no columns to update'
			); }
			// assemble the updates
			$this->Updates = implode(', ', $this->Updates);
			// prepare the update query
			$this->Query = "UPDATE $this->Table SET $this->Updates";
		}
		// if the select has joined tables
		if($this->Action == 'SELECT' && count($this->Joins)) {
			// assemble the joins
			$this->Joins = implode(' ', $this->Joins);
			// assemble the query
			$this->Query .= " $this->Joins";
		}
		// if the action needs conditions
		if(
			$this->Action == 'SELECT' || 
			$this->Action == 'UPDATE' || 
			$this->Action == 'DELETE'
		) {
			// if conditions are provided
			if(count($this->Conditions)) {
				// assemble the conditions
				$this->Conditions = trim(
					implode(' ', $this->Conditions), 
					'AND /OR '
				);
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
		else { Throw new Exception(
			"Query->action({$action_name}) : An incompatible action already exists : {$this->Action}"
		); }
    }

    private function throwExceptionOn(string $occured_on) {
    	// prepare informations to be thrown
		$exception_infos = implode(
			":",
			\Polyfony\Database::handle()->ErrorInfo()
		).":$this->Query";
		// throw an exception
		Throw new Exception(
			"Query->{$occured_on}() failed because : {$exception_infos}",
			500
		);
    }
	
}


?>
