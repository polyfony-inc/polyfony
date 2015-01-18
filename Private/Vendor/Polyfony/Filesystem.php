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
	
	// this will restrict a path to the data storage folder
	public static function chroot($path='/') {

		// if the path already has the proper (ch)root
		if(strpos($path, Config::get('polyfony', 'data_path')) === 0) {
			// remove the root, remove double dots
			$path = str_replace(array(Config::get('polyfony', 'data_path'), '..'), '', $path);
			// re-add the root
			return(Config::get('polyfony', 'data_path') . trim($path, '/') . '/');
		}
		// the path doesn't start with the (ch)root
		else {
			// remove all double dots and add the root path
			return(Config::get('polyfony', 'data_path') . trim(str_replace('..','',$path), '/') . '/');
		}

	}

	public static function isDirectory($path, $chroot = false) {

		// if chroot is enabled, restrict the path to the data storage path
		$path = $chroot ? self::chroot($path) : $path;
		// check if the path is a directory
		return(is_dir($path));

	}

	public static function isFile($path, $chroot = false) {

		// if chroot is enabled, restrict the path to the data storage path
		$path = $chroot ? self::chroot($path) : $path;
		// check if the path is a file
		return(is_file($path));

	}

	public static function isSymbolic($path, $chroot = false) {

		// if chroot is enabled, restrict the path to the data storage path
		$path = $chroot ? self::chroot($path) : $path;
		// check if it is symbolic
		return(is_link($path));

	}
	
	public static function isNormalName($string) {
		// check if the name starts with a dot
		return((substr($string,0,1) != '.' ) ? true : false);
	}

	public static function exists($path, $chroot = false) {

		// if chroot is enabled, restrict the path to the data storage path
		$path = $chroot ? self::chroot($path) : $path;
		// check for the existence
		return(file_exists($path));

	}
	
	public static function ls($path, $filters=null, $chroot = false) {

		// if chroot is enabled, restrict the path to the data storage path
		$path = $chroot ? self::chroot($path) : $path;
		// prepare the results
		$filtered = array();
		// clean the path and add a trailing slash
		$path = trim($path, '/') . '/';
		// scan the folder
		$files_and_folders = self::exists($path) && self::isDirectory($path) ? scandir($path) : array();
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

	public static function symlink($existing_file, $link, $chroot = false) {

		// if chroot is enabled, restrict the path to the data storage path
		$existing_file = $chroot ? self::chroot($existing_file) : $existing_file;
		// if chroot is enabled, restrict the path to the data storage path
		$link = $chroot ? self::chroot($link) : $link;
		// return false if the target does not exist
		return(self::exists($existing_file) ? symlink($existing_file, $link) : false);

	}

	public static function mkdir($path, $mask = 0777, $chroot = false) {

		// if chroot is enabled, restrict the path to the data storage path
		$path = $chroot ? self::chroot($path) : $path;
		// if the file already exists
		if(self::exists($path) && self::isDirectory($path)) {
			// we also return true
			return(true);
		}
		// return the creation status
		return(mkdir($path, $mask, true));

	}
	
	public static function remove($path, $chroot = false) {

		// if chroot is enabled, restrict the path to the data storage path
		$path = $chroot ? self::chroot($path) : $path;
		// if path exists and is a file
		if(self::exists($path) && self::isFile($path)) {
			// remove the file and return the result of that action
			return(unlink($path));
		}
		// invalid path
		else {
			// return false
			return(false);
		}

	}

	public static function get($path, $chroot = false) {

		// if chroot is enabled, restrict the path to the data storage path
		$path = $chroot ? self::chroot($path) : $path;
		// if the file exists
		if(self::exists($path) && self::isFile($path)) {
			// remove the file and return the result of that action
			return(file_get_contents($path));
		}

	}

	public static function chmod($path, $mask, $chroot = false) {

	}

	public static function chown($path, $user, $group, $chroot = false) {

	}

	public static function touch($path, $timestamp=null, $chroot = false) {

	}

	public static function copy($source_path, $destination_path, $chroot = false) {

	}

	public static function info($path) {

		// if path exists and is a file
		if(self::exists($path) && self::isFile($path)) {
			// remove the file and return the result of that action
			return(array(
				'size'			=>'',
				'creation'		=>'',
				'modification'	=>'',
				'type'			=>'',
				'extension'		=>''
			));
		}
		// file does not exist
		else {
			// return false
			return(false);
		}

	}
	
	public static function type($path) {
		
		// get a new fileinfo object
		$info = new \finfo(FILEINFO_MIME);
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
