<?php

namespace Polyfony\Query;

class Base {

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

		// initialize attributes
		$this->Object		= 'Entity';
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

}

?>
