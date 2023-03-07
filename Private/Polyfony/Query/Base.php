<?php

namespace Polyfony\Query;

class Base {

	// internal attributes
	protected	$Query;
	protected	?array $Values;
	protected	$Prepared;
	protected	bool $Success;
	protected	bool $First;
	protected	$Result;
	protected	?string $Action;
	protected	?string $Hash;
	protected	string $Quote;
	
	// attributes to build a query
	protected	string $Object 		= "Entity";
	protected	?string $Table 		= null;
	protected	string $Operator 	= 'AND';
	protected	array $Selects 		= [];
	protected	$Joins 				= []; // array|string
	protected	$Conditions 		= []; // array|string
	protected	$Updates 			= []; // array|string
	protected	$Inserts 			= []; // array|string
	protected	$Groups 			= []; // array|string
	protected	$Order 				= []; // array|string
	protected	$Limit 				= []; // array|string

	// instanciate a new query
	public function __construct(string $quote = '') {

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
		
	}

}

?>
