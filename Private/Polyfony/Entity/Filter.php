<?php


namespace Polyfony\Entity;
use Polyfony\Format as Format;

class Filter {

	const FILTERS_TO_METHODS_MAP = [
		'capslock30'=>'toUppercaseSafe30Percent',
		'capslock50'=>'toUppercaseSafe50Percent',
		'capslock70'=>'toUppercaseSafe70Percent',
		'strtoupper'=>'toUpperCase',
		'strtolower'=>'toLowerCase',
		'ucfirst'	=>'toFirstUppercase',
		'ucwords'	=>'toAllFirstUppercase',
		'trim'		=>'toTrimmedEnds',
		'numeric'	=>'toNumeric',
		'integer'	=>'toInteger',
		'email'		=>'toEmail',
		'phone'		=>'toPhone',
		'text'		=>'toText',
		'name'		=>'toName',
		'slug'		=>'toSlug',
		'length4'	=>'length4',
		'length8'	=>'length8',
		'length16'	=>'length16',
		'length32'	=>'length32',
		'length64'	=>'length64',
		'length128'	=>'length128',
		'length256'	=>'length256',
		'length512'	=>'length512',
		'length1024'=>'length1024',
		'length2048'=>'length2048',
		'length4096'=>'length4096'
	];

	const FILTERS_TO_ATTRIBUTES = [
		'email'		=>['type'=>'email'],
		'phone'		=>['type'=>'tel'],
		'integer'	=>['type'=>'number'],
		'numeric'	=>['type'=>'number'],
		'length4'	=>['maxlength'=>'4'],
		'length8'	=>['maxlength'=>'8'],
		'length16'	=>['maxlength'=>'16'],
		'length32'	=>['maxlength'=>'32'],
		'length64'	=>['maxlength'=>'64'],
		'length128'	=>['maxlength'=>'128'],
		'length256'	=>['maxlength'=>'256'],
		'length512'	=>['maxlength'=>'512'],
		'length1024'=>['maxlength'=>'1024'],
		'length2048'=>['maxlength'=>'2048'],
		'length4096'=>['maxlength'=>'4096']
	];

	public static function sanitizeThisValue(
		string $column,  
		string $class_name, 
		$value=null
	) {
		// get the filter
		$filters = self::getFiltersForColumn($column, $class_name);
		// if a filter has been defined for that column
		if($filters) {
			// for each filter, apply them
			foreach($filters as $filter) {
				// get the method name for that filter
				$method = self::getMethodForFilter($filter);
				// apply that specific filter to the value
				$value = self::$method($value);
			}
		}
		// return the potentially sanitized value
		return $value;
	}

	public static function getFiltersForColumn(
		string $column, 
		string $class_name
	) {

		// get the filters if any
		$filters = isset($class_name::FILTERS[$column]) ? $class_name::FILTERS[$column] : null;
		// if filters were found, convert them to an array, or return null
		return $filters ? 
			is_array($filters) ? $filters : [$filters] : 
			null ;

	}

	private static function getMethodForFilter($filter) :string {

		// check if said filter exists
		if(!array_key_exists($filter, self::FILTERS_TO_METHODS_MAP)) {
			// tell the developer that it doesn't exist
			Throw new \Polyfony\Exception(
				'Filter '.$filter.' does not exist in Record/Filter', 500
			);
		}

		// name of the method to call the filter
		return self::FILTERS_TO_METHODS_MAP[$filter];

	}

	private static function toUpperCase($value) {
		return mb_strtoupper($value);
	}

	private static function toLowerCase($value) {
		return mb_strtolower($value);
	}

	private static function toFirstUppercase($value) {
		return ucfirst($value);
	}

	private static function toAllFirstUppercase($value) {
		return ucwords($value);
	}

	private static function toTrimmedEnds($value) {
		return trim($value);
	}

	private static function toNumeric($value) {
		// keep only digit, +/- and dot
		return filter_var(
			str_replace(',','.',$value), 
			FILTER_SANITIZE_NUMBER_FLOAT, 
			FILTER_FLAG_ALLOW_FRACTION
		);
	}

	private static function toInteger($value) {
		return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	}

	private static function toEmail($value) {
		return filter_var($value, FILTER_SANITIZE_EMAIL);
	}

	private static function toPhone($value) {
		// keep only 0-9 + ( )
		return preg_replace('/[^0-9\+\(\)]+/i', '', $value);
	}

	private static function toText($value) {
		// remove dangerous symbols
		return str_replace(
			['<','>','"','&','\\','/', '`'], 
			'', 
			str_replace(
				'\'', 
				'’', 
				$value
			)
		);
	}

	private static function toName($value) {
		return preg_replace('/[^\p{L}’ ]/u','',self::toText($value));
	}

	private static function toSlug($value) {
		return Format::slug($value);
	}

	private static function toUppercaseSafe30Percent(
		$value
	) :string {
		return Format::uppercaseSafe(
			$value, 
			0.3
		);
	}

	private static function toUppercaseSafe50Percent(
		$value
	) :string {
		return Format::uppercaseSafe(
			$value, 
			0.5
		);
	}

	private static function toUppercaseSafe70Percent(
		$value
	) :string {
		return Format::uppercaseSafe(
			$value, 
			0.7
		);
	}

	private static function length($value, int $length) {
		return mb_substr($value, 0, $length);
	}

	private static function length4($value) {
		return self::length($value, 4);
	}

	private static function length8($value) {
		return self::length($value, 8);
	}

	private static function length16($value) {
		return self::length($value, 16);
	}

	private static function length32($value) {
		return self::length($value, 32);
	}

	private static function length64($value) {
		return self::length($value, 64);
	}

	private static function length128($value) {
		return self::length($value, 128);
	}

	private static function length256($value) {
		return self::length($value, 256);
	}

	private static function length512($value) {
		return self::length($value, 512);
	}

	private static function length1024($value) {
		return self::length($value, 1024);
	}

	private static function length2048($value) {
		return self::length($value, 2048);
	}

	private static function length4096($value) {
		return self::length($value, 4096);
	}

}

?>
