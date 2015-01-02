<?php
/**
 * PHP Version 5
 * Helper for filesystem operations
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Filesystem {
	
	public static function isDirectory($path) {
		
		return(is_dir($path));
		
	}
	
	public static function exists($path) {
		
		return(file_exists($path));
		
	}
	
	public static function isNormalName($string) {
		
		// true if not starting with a dot, false otherwise
		return((substr($string,0,1) != '.' ) ? true : false);
		
	}
	
	public static function getType($path) {
		
		// get a new fileinfo object
		$info = new finfo(FILEINFO_MIME);
		// if the fileinfo failed to instanciate
		if(!$info) {
			// return an error
			return(false);	
		}
		// get the mimetype
		$type = $info->file($path);
		// if failed to get a type
		if(!$type) {
			// return an error
			return(false);		
		}
		// we got a mimetype
		else {
			// if it has a ; in it
			if(strstr($type,';')) {
				// only keep the first part
				list($type) = explode(';',$type);
			}
		}
		// return the type
		return($type);
		
	}
	
	public static function getFolders($path, $filter_callback=null) {
		
	}
	
	
	public static function getFiles($path ,$filter_callback=null) {
		
	}
	
}

?>
