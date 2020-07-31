<?php


namespace Polyfony\Entity;

class Validator {

	// use defined validators to deduce the associated input types
	const VALIDATORS_TO_ATTRIBUTES = [
		FILTER_VALIDATE_EMAIL	=>['type'=>'email'],
		FILTER_VALIDATE_FLOAT	=>['type'=>'number'],
		FILTER_VALIDATE_INT		=>['type'=>'number'],
		FILTER_VALIDATE_URL		=>['type'=>'url']
	];

	public static function isThisValueAcceptable(
		string $table_name, 
		string $class_name, 
		string $column, 
		$value=null
	) :void {
		// get the allowed null columns
		$allowed_nulls = \Polyfony\Database::describe($table_name);

		if(!\Polyfony\Database::doesColumnExist($column, $table_name)) {
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

	private static function doesNullableConstraintFail(
		$value=null, 
		string $column, 
		array $allowed_nulls
	) :bool {

		return 
			$allowed_nulls[$column] === false && 
			(
				is_null($value) || 
				(
					!is_array($value) &&  
					strlen($value) === 0
				)
			);

	}

	public static function getValidatorForColumn(
		string $column, 
		string $class_name
	) { // beware, you CANNOT type the return value of this function
		// get the validator if any
		return isset($class_name::VALIDATORS[$column]) ? $class_name::VALIDATORS[$column] : null;
	}

	private static function doesUserDefinedValidatorFail(
		$value=null, 
		string $column, 
		string $class_name
	) :bool {
		// get the validator
		$validator = self::getValidatorForColumn($column, $class_name);
		// deduce the passing/failing status
		return 
			// if the value is not null (nullability is checked elsewhere)
			!is_null($value) && 
			// if a validator exists
			!is_null($validator) && 
			// and the value is not empty (nullability is checked elsewhere)
			strlen($value) !== 0 && 
			(
				// if the validator is an int, then it's a FILTER_VALIDATE
				(
					is_int($validator) && 
					!filter_var($value, $validator)
				) || 
				// if the validator is a string, then it's a regex, check if the values matches
				(
					is_string($validator) && 
					!preg_match($validator, $value)
				) || 
				// or if the validator is an array, check if the value is a key of that array
				(
					is_array($validator) && 
					!in_array($value, array_keys($validator))
				)
			);
	}
}

?>
