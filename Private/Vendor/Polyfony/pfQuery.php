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
 
class pfQuery {
	
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
		$this->Query		= null;
		$this->Values		= array();
		$this->Prepared		= null;
		$this->Success		= false;
		$this->Result		= null;
		$this->Array		= array();
		$this->Action		= null;
		$this->Hash			= null;
		$this->Lag			= null;
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
	
	// convert the value depending on the column name
	protected static function set($column,$value) {
		
	}
	
	// convert the value depending on the column name
	protected static function get($column,$value) {
		
	}
	
}


?>