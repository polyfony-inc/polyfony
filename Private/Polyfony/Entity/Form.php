<?php

namespace Polyfony\Entity;
use Polyfony\Element as Element;

class Form extends Accessor {

	// if filters or validators exist, we can apply default attributes
	private function deduceAttributes(string $column) :array {

		// attriutes deduced from filters
		$filters_attributes 	= self::deduceAttributesFromFilters($column);
		// attriutes deduced from filters
		$validators_attributes 	= self::deduceAttributesFromValidators($column);
		// attributes deduced from the database
		$database_attributes 	= self::deduceAttributesFromDatabase($column);	
		// attributes deduced from the column's content
		$contents_attributes 	= self::deduceAttributesFromContents($column);	

		// return the deduced attributes
		return 
			$filters_attributes + 
			$validators_attributes + 
			$database_attributes + 
			$contents_attributes;

	}

	private function deduceAttributesFromFilters(string $column) :array {

		// default attributes
		$attributes = [];

		// get filters that could exist for that column
		// and which could provide hints as to the data type
		$filters = Filter::getFiltersForColumn($column, get_class($this));

		// if filters exist for that column
		if($filters) {
			// for each filter that exist
			foreach($filters as $filter) {
				// if specific attributes exist for this filter
				if(isset(Filter::FILTERS_TO_ATTRIBUTES[$filter])) {
					// get its associated form attributes
					$attributes += Filter::FILTERS_TO_ATTRIBUTES[$filter];
				}
			}
		}

		return $attributes;

	}

	private function deduceAttributesFromValidators(string $column) :array {

		// default attributes
		$attributes = [];

		// get validator that could exist for that column
		// and which could provide hints as to the data type to expect
		$validator = Validator::getValidatorForColumn($column, get_class($this));

		// if specific attributes exist for this filter
		if(!is_array($validator) && isset(Validator::VALIDATORS_TO_ATTRIBUTES[$validator])) {
			// get its associated form attributes
			$attributes += Validator::VALIDATORS_TO_ATTRIBUTES[$validator];
		} 

		return $attributes;

	}

	private function deduceAttributesFromDatabase(string $column) :array {

		// default attributes
		$attributes = [];

		// get validators that could exist for that column 
		// and which could provide hints as to the data type
		$allowed_nulls = \Polyfony\Database::describe(self::tableName());

		// if that column cannot be null
		if(!$allowed_nulls[$column]) {
			// add the requried attribute
			$attributes['required'] = 'required';
		}
		

		return $attributes;

	}

	private function deduceAttributesFromContents(string $column) :array {

		// default attributes
		$attributes = [];

		// if the column contents is an array
		if(is_array($this->get($column))) {
			// add the multiple attribute
			$attributes['multiple'] = 'multiple';
		}

		return $attributes;

	}

	private function field(string $column) {
		return("{$this->_['table']}[$column]");
	}

	// shortcut to generate a preconfigured HTML Form element
	public function input(string $column, array $options = []) :Element {
		return \Polyfony\Form::input(
			$this->field($column), 
			$this->get($column), 
			array_merge(self::deduceAttributes($column), $options)
		);
	}
	
	// shortcut to generate a preconfigured HTML Form element
	public function textarea(string $column, array $options = []) :Element {
		return \Polyfony\Form::textarea(
			$this->field($column), 
			$this->get($column), 
			array_merge(self::deduceAttributes($column), $options)
		);
	}
	
	// shortcut to generate a preconfigured HTML Form element
	public function select(
		string $column, 
		array $list = [], 
		array $options = []
	) :Element {
		return \Polyfony\Form::select(
			$this->field($column), 
			$list, 
			$this->get($column), 
			array_merge(self::deduceAttributes($column), $options)
		);
	}
	
	// shortcut to generate a preconfigured HTML Form element
	public function checkbox(
		string $column, 
		array $options = []
	) :Element {
		return \Polyfony\Form::checkbox(
			$this->field($column), 
			$this->get($column), 
			$options
		);
	}

}

?>
