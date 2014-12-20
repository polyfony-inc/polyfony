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

	// human size
	public static function size($integer) {
		
		// declare units
		$unit = array('b','Ko','Mo','Go','To','Po');
		// make human readable
		return(round($size/pow(1024,($i=floor(log($size,1024)))),$precision).' '.$unit[$i]);
		
	}
	
	// relative date
	public static function date($string) {
		
	}
	
	// phone number
	public static function phone($string) {
		
	}
	
	// classic slug
	public static function slug($string) {
		
		// equivalents of accentuated caracters
		$with = str_split("àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ _'");
		$without = str_split("aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY---");
		// replace accents and lowercase the string
		$string = strtolower(str_replace($with, $without, $string));
		// replace all but 0-9 a-z remove doubles and trim the edges
		return(trim(str_replace('--','-',preg_replace('#[^a-z0-9\-]#','-',$string)),'-'));
		
	}
	
	// file or folder name that is safe for the filesystem
	public static function fsSafe($string) {
		
		// remove any symbol that does not belong in a file name of folder name
		return(str_replace(array('..','/','\\',':','@','$','?','*','<','>','&','(',')','{','}',',','%','`','\'','#'),'-',$string));
		
	}
	
	// screen safe
	public static function htmlSafe($string) {
		
		// just remove html entities
		return(htmlentities($string));
		
	}
	
	// create a link
	public static function link($string,$url='#',$attributes=array()) {
		
		// build the actual link
		return("<a {self::attributes(array_merge(array('href'=>$url),$attributes))}>{$string}</a>");
		
	}
	
	// truncate to a certain length
	public static function truncate($string,$length=16) {
	
		// if string is longer than authorized truncate, else do nothing
		return(strlen($string) > $length ? trim(mb_substr(strip_tags($string),0,$length-2,'UTF-8')).'…' : $string);
		
	}
	
	public static function attributes($attributes=null) {
		
		// set empty string
		$string = '';
		
		// array is provided
		if(is_array($attributes)) {
			
			// for each attribute
			foreach($attributes as $key => $value) {
				
				// associate them
				$string .= strlen($value) > 0 ? $key.'="'.strip_tags($value).'" ' : $key.' ';
				
			}

		}
		
		// return the formatted string
		return(trim($string));
		
	}
	
	
}

?>