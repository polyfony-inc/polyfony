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

	public static function isFile($path) {
		return(is_file($path));
	}

	public static function isSymbolic($path) {
		return(is_link($path));
	}
	
	public static function isNormalName($string) {
		return((substr($string,0,1) != '.' ) ? true : false);
	}

	public static function exists($path) {
		return(file_exists($path));
	}
	
	public static function ls($path, $filters=null) {

		// prepare the results
		$filtered = array();

		// clean the path and add a trailing slash
		$path = trim($path, '/') . '/';

		// scan the folder
		$files_and_folders = self::exists && self::isDirectory($path) ? scandir($path) : array();

		// for each found result
		foreach($files_and_folders as $file_or_folder) {

			// apply the filters
			// … some code

			// build the fullpath
			$full_path = $path . $file_or_folder;

			// add the file or folder if it passed the filter
			$filtered[$full_path] = $file_or_folder;

		}

		// return the filetered content
		return($filtered);

	}

	public static function symlink() {

	}

	public static function mkdir() {

	}
	
	public static function remove() {

	}

	public static function chmod() {

	}

	public static function chown() {

	}

	public static function touch() {

	}

	public static function copy() {

	}

	public static function info() {

	}
	
	public static function type($path) {
		
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
	
	
}

?>
