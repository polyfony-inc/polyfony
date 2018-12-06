<?php

namespace Polyfony\Response;
use Polyfony\Config as Config;

class CSV {

	const default_new_line		= "\n";
	const default_delimiter 	= "\t";
	const default_encloser 		= '"';
	const destination_charset 	= 'UTF-16LE';

	// the $content is not type constrained, so that old code 
	// with preformatted csv will still work
	public static function buildAndGetDocument($content) :array {
		// convert the array to a basic CSV string
		$content = is_array($content) ? self::convertArrayToString($content) : $content;
		// convert the content to the proper charset (if needs be)
		$content = mb_convert_encoding($content, self::destination_charset);
		// prefix it with the BOM characters
		$content = self::getBomCharacters() . $content;
		// return the formatted document
		return [
			$content,
			self::destination_charset
		];
	}

	public static function convertArrayToString(array $content) :string {
		// get the text delimiter (once)
		$delimiter 	= self::getDelimiter();
		// get the encloder (once)
		$encloser 	= self::getEncloser();
		// declare a temporary array
		$csv 		= [];
		// for each entry of the array
		foreach($content as $line) {
			// store cells temporarily
			$cells = [];
			// for each cell
			foreach((is_object($line) ? array_values($line->__toArray()) : $line) as $cell) {
				// remove conflicting symbols, enclose with proper character and spool the cell
				$cells[] = 
					self::getEncloser() . 
					str_replace(self::getCharactersToRemove(), ' ', $cell) . 
					self::getEncloser();
			}
			// implode the array
			$csv[] = implode($delimiter, $cells);
		}
		// make a string out of it
		return implode(self::default_new_line, $csv);
	}

	public static function getBomCharacters() :string {
		return chr(255) . chr(254);
	}

	public static function getCharactersToRemove() :array {
		// list of characters to remove from the cells to prevent corruption
		return [
			self::getDelimiter(),
			self::default_new_line,
			self::default_encloser
		];
	}

	public static function getDelimiter() :string {

		// get the configured delimited, or the default one
		return strlen(Config::get('response', 'csv_delimiter')) > 0 ? 
			Config::get('response', 'csv_delimiter') : self::default_delimiter;

	}

	public static function getEncloser() :string {

		// get the configured delimited, or the default one
		return strlen(Config::get('response', 'csv_encloser')) > 0 ? 
			Config::get('response', 'csv_encloser') : self::default_encloser;

	}



}

?>
