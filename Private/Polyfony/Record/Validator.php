<?php


namespace Polyfony\Record;

class Validator {

	public static function isThisValueAcceptable(
		string $table_name, 
		string $class_name, 
		string $column, 
		$value=null
	) :void {

		// get the allowed null columns
		$allowed_nulls = \Polyfony\Database::describe($table_name);
		// check if the column exists
		if(!array_key_exists($column, $allowed_nulls)) {
			// throw a useful exception
			Throw new \Polyfony\Exception(
				"{$class_name}->set({$column}) : column does not exist (If it does, purge the cache!)",
				400
			);
		}
		// check if the value is null/empty and that it is not allowed to have such a value
		if(self::doesNullableConstraintFail($value, $column, $allowed_nulls)) {
			// throw a useful exception
			Throw new \Polyfony\Exception(
				"{$class_name}->set({$column}) : cannot be null (If it should, purge the cache!)",
				400
			);
		}
		// check if a user defined validator exists for that column, and if it fails to pass it
		if(self::doesUserDefinedValidatorFail($value, $column, $class_name))  {
			// throw a useful exception
			Throw new \Polyfony\Exception(
				"{$class_name}->set({$column}) : does not conform to the regex, PHP Filter, or is not in array of allowed values",
				400
			);
		}

	}

	private static function doesNullableConstraintFail($value=null, string $column, array $allowed_nulls) :bool {

		return 
			$allowed_nulls[$column] == false && 
			(
				is_null($value) || 
				$value === ''
			);

	}

	private static function doesUserDefinedValidatorFail($value=null, string $column, string $class_name) :bool {
		// get the validator
		$validator = isset($class_name::VALIDATORS[$column]) ? $class_name::VALIDATORS[$column] : null;
		// deduce the passing/failing status
		return 
			// if the value is not null
			!is_null($value) && 
			// and a validator exists
			!is_null($validator) && 
			(
				// if the validator is an int, then it's a FILTER_VALIDATE
				(is_int($validator) && !filter_var($value, $validator)) || 
				// if the validator is a string, then it's a regex, check if the values matches
				(is_string($validator) && !preg_match($validator, $value)) || 
				// or if the validator is an array, check if the value is a key of that array
				(is_array($validator) && !in_array($value, array_keys($validator)))
			);
	}
}

?>
