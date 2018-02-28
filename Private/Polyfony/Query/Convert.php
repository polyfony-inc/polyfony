<?php

namespace Polyfony\Query;

class Convert {

	// convert a value comming from the database, to its original type
	public static function valueFromDatabase($column_name, $raw_value, $get_it_raw=false) {

		// if we want the raw result ok, but exclude arrays that can never be gotten raw
		if($get_it_raw === true && substr($column_name,-6,6) != '_array') {
			// return as is
			$value = $raw_value;
		}
		// if the column_name contains an array
		elseif(substr($column_name,-6,6) == '_array') {
			// decode the array
			$value = json_decode($raw_value,true);
		}
		// if the column_name contains a size value
		elseif(substr($column_name,-5,5) == '_size') {
			// convert to human size
			$value = \Polyfony\Format::size($raw_value);
		}
		// if the column_name contains a datetime
		elseif(substr($column_name,-9,9) == '_datetime') {
			// if the value is set
			$value = date('d/m/Y H:i', $raw_value);
		}
		// if the column_name contains a date
		elseif(
			substr($column_name,-5,5) == '_date' || 
			substr($column_name,-3,3) == '_at' || 
			substr($column_name,-3,3) == '_on'
		) {
			// if the value is set
			$value = date('d/m/Y', $raw_value);
		}
		// not a magic column_name
		else {
			// return as is
			$value = $raw_value;
		}
		// return the converted (or not) value
		return $value;

	}

	// convert a value from its current type to store it in the database
	public static function valueForDatabase(string $column, $value) {
		// if we find a serialization keyword
		if(strpos($column,'_array') !== false) {
			// encode the content as JSON, being it array, null, false, whatever
			$value = json_encode($value);
		}
		// if we are dealing with a date and the value is not empty or null
		elseif(
			(
				substr($column,-9,9) == '_datetime' || 
				substr($column,-5,5) == '_date' || 
				substr($column,-3,3) == '_at' || 
				substr($column,-3,3) == '_on'
			) && $value != '') {
			// if the date has a time with it
			if(
				substr_count($value, '/') == 2 && 
				substr_count($value, ':') == 1 && 
				substr_count($value, ' ') == 1
			) {
				// explode the date's elements
				list($date, $time) 			= explode(' ', $value);
				// explode the date's elements
				list($day, $month, $year) 	= explode('/', $date);
				// explode the time element
				list($hour, $minute) 		= explode(':', $time);
				// create a timestamp early in the morning
				$value = mktime($hour, $minute, 1, $month, $day, $year);
			}
			// if the date is alone (no time besides it)
			elseif(substr_count($value, '/') == 2) {
				// explode the date's elements
				list($day, $month, $year) = explode('/', $value);
				// create a timestamp early in the morning
				$value = mktime(0, 0, 1, $month, $day, $year);
			}
			// date format in unknown, and does not look like a timestamp
			elseif(!is_numeric($value)) {
				// we can't allow such weird data get into the _date column
				Throw new Exception('Query->secure() : Wrong data type for magic date field '.$column);
			}
		}
		// return the value (transformed or not)
		return $value;
	}

	// get a column placeholder to build queries with
	public static function columnToPlaceholder(
		string $quote_symbol, 
		string $column, 
		$allow_wildcard = false
	) :array {
        // apply the secure regex for the column name
        $column = preg_replace(($allow_wildcard ? '/[^a-zA-Z0-9_\.\*]/' : '/[^a-zA-Z0-9_\.]/'), '', $column);    
        // cleanup the placeholder
        $placeholder = str_replace(['.', '*'], '_', strtolower($column)); 
        // return cleaned column
        return([$quote_symbol . $column . $quote_symbol, $placeholder]);
	}


}


?>