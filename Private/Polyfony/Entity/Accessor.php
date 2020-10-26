<?php

namespace Polyfony\Entity;
use Polyfony\Exception as Exception;
use Polyfony\Query\Convert as Convert;
use Polyfony\Query as Query;
use Polyfony\Database as Database;
use Polyfony\Format as Format;
use Polyfony\Locales as Locales;

class Accessor extends Aware {

	// increment a column's value, up to an optionnal point
	public function increment(
		string $column_name, 
		?int $maximum_value = null
	) :self {

		return $this->set([
			$column_name => 
				// if the incremented value doesn't exceed the maximum
				(int) $this->get($column_name, true) + 1 <= $maximum_value || 
				// of if we don't have a maximum_value defined
				is_null($maximum_value) ? 
					// increment it
					(int) $this->get($column_name, true) + 1 : 
					// keep as is
					$this->get($column_name, true)
		]);

	}

	// decrement a column's value, down to an implicit (0) point
	public function decrement(
		string $column_name, 
		?int $minimum_value = 0
	) :self {

		return $this->set([
			$column_name => 
				// if the incremented value doesn't exceed the maximum
				(int) $this->get($column_name, true) - 1 >= $minimum_value ? 
					// increment it
					(int) $this->get($column_name, true) - 1 : 
					// keep as is
					$this->get($column_name, true)
		]);

	}

	// add an element to the end of a magic array column
	public function push(
		string $column_name, 
		$array_or_value
	) :self {

		// remove the _array extension (if any) and add it back
		$column_name = str_replace('_array', '', $column_name) . '_array';

		// get the column's value
		$column_s_array = (array) $this->get($column_name);

		// push the new value(s) in it
		array_push(
			$column_s_array, 
			$array_or_value
		);

		// push the array or value to the end of the column's array
		return $this->set([$column_name => $column_s_array]);

	}

	// TruncatedGet
	// truncated version of get
	public function tget(
		string $column, 
		?int $length = 16,
		?bool $raw = false
	) :string {
		// truncate the string if it's longer than allowed
		$string = mb_strlen($this->get($column, true)) > $length ? 
			Format::truncate(
				$this->get($column, true), 
				$length
			) : 
			$this->get($column, true);
		// return it raw, or protected against html entites
		return $raw ? 
			$string : 
			Format::htmlSafe($string);
	}

	
	// LocalizedGet
	// localized version of get
	public function lget(
		string $column
	) :string {
		// then re-protect it against html
		return Format::htmlSafe(
			// translate it
			Locales::get(
				// get it without html entites protection
				$this->get($column, true)
			)
		);
	}

	// OnlySet
	// limited version of set (certain column only will be set from the array)  
	public function oset(
		array $associative_array_of_value_to_set,
		array $columns_allowed_to_be_set
	) :self {

		// for each of the column that we are allowed to set
		foreach(
			$columns_allowed_to_be_set as 
			$column
		) {
			// if it exists, set it
			!array_key_exists(
				$column, 
				$associative_array_of_value_to_set
			) ?: $this->set([
				$column => $associative_array_of_value_to_set[$column]
			]);
		}
		// and allow chaining
		return $this;

	}


}

?>
