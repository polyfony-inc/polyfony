<?php
/**
 * PHP Version 5
 * String format helpers
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Format {

	// file or folder name that is safe for the filesystem
	public static function fsSafe($string) {
		// remove any symbol that does not belong in a file name of folder name
		return(str_replace(array(
				'..','/','\\',':','@','$','?','*','<','>','&','(',')','{','}',',','%','`','\'','#'), 
				'-', 
				$string
			)
		);
	}

	// string that is safe for javascript variable
	public static function jsSafe($string) {
		// escape all single quotes
		return(json_encode($string));
	}
	
	// screen safe
	public static function htmlSafe($string) {
		// just remove html entities
		return(filter_var($string, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
	}

	// human size
	public static function size($integer, $precision=1) {
		// declare units
		$unit = array('b','Ko','Mo','Go','To','Po');
		// make human readable
		return(round($integer/pow(1024, ($i=floor(log($integer, 1024)))), $precision) . ' ' . $unit[$i]);
	}
	
	// relative date
	public static function date($timestamp) {
		// date is in the future
		if($timestamp > time()) {

		}
		// date is in the past
		else {

		}
	}
	
	// phone number
	public static function phone($phone) {
		// remove all spaces from the number
		$phone = str_replace(' ', '', $phone);
		// remove all symbols except + and ()
		$phone = preg_replace('/[^0-9\+()]/', '', $phone);
		// reformat special symbols
		$phone = str_replace('(', ' (', $phone);
		// if the phone is of normal local format
		if(
			strlen($phone) === 10 && strpos($phone, '+') === false && 
			strpos($phone, '(') === false && strpos($phone, ')') === false
		) {
			// format the phone number by blocks of two numbers
			return(
				substr($phone, 0, 2).' '.substr($phone, 2, 2).' '.
				substr($phone, 4, 2).' ' .substr($phone, 6, 2).' '.substr($phone, 8, 2));
		}
		// the phone is in international format
		else {
			// return the phone number, truncate at 31 chars (the world longest possible phone number)
			return(self::truncate($phone, 31));
		}
	}
	
	// classic slug
	public static function slug($string) {
		// accentuated characters
		$with = str_split("àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ _'");
		// equivalent characters without accents
		$without = str_split("aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY---");
		// replace accents and lowercase the string
		$string = strtolower(str_replace($with, $without, $string));
		// replace all but 0-9 a-z remove doubles and trim the edges
		return(trim(str_replace('--', '-', preg_replace('#[^a-z0-9\-]#', '-', $string)), '-'));
	}
	
	// create a link
	public static function link($string, $url='#', $attributes=array()) {
		// build the actual link
		return(new Element('a', array_merge(array('href'=>$url, 'text'=>$string), $attributes)));
	}
	
	// truncate to a certain length
	public static function truncate($string, $length=16) {
		// if string is longer than authorized truncate, else do nothing
		return(
			strlen($string) > $length ? 
			trim(mb_substr(strip_tags($string), 0, $length - 2, Response::getCharset())).'…' : 
			$string
		);
	}
	
	
	public static function wrap($text, $phrase, $wrapper = '<strong class="highlight">\\1</strong>') {
		if(empty($text)) {
			return '';
		}
		if(empty($phrase)) {
			return $text;
		}
		if(is_array($phrase)) {
			foreach ($phrase as $word) {
				$pattern[] = '/('.preg_quote($word, '/').')/i';
				$replacement[] = $wrapper;
			}
		}
		else {
			$pattern = '/('.preg_quote($phrase, '/').')/i';
			$replacement = $wrapper;
		}
		return preg_replace($pattern, $replacement, $text);
	}
	
	// remove all formatting and invisible symbols to get the shortest possible string
	public static function obfuscate($string) {
		// remove all formatting symbols and double spaces	
		return(str_replace('  ', ' ', str_replace(array("\t", "\n", "\r"), '', $string)));
	}	
	
}

?>
