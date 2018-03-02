<?php

 //  ___                           _          _ 
 // |   \ ___ _ __ _ _ ___ __ __ _| |_ ___ __| |
 // | |) / -_) '_ \ '_/ -_) _/ _` |  _/ -_) _` |
 // |___/\___| .__/_| \___\__\__,_|\__\___\__,_|
 //          |_|                                

namespace Polyfony;

class Filesystem {
	
	// this will restrict a path to the data storage folder
    public static function chroot(string $path='/', bool $override = false) {

    	// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

        // if chrooting is enabled in the configuration
        if(Config::get('filesystem', 'chroot') && !$override) {
            // if the path already has the proper (ch)root
            if(strpos($path, Config::get('filesystem', 'data_path')) === 0) {
                // remove the root, remove double dots
                $path = str_replace(array(Config::get('filesystem', 'data_path'), '..'), '', $path);
                // re-add the root
                return(Config::get('filesystem', 'data_path') . $path);
            }
            // the path doesn't start with the (ch)root
            else {
                // remove all double dots and add the root path
                return(Config::get('filesystem', 'data_path') . str_replace('..','',$path));
            }
        }
        // return the path (altered or not)
        return($path);

    }

	public static function isDirectory(string $path, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// check if the path is a directory
		return(is_dir($path));

	}

	public static function isFile(string $path, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// check if the path is a file
		return(is_file($path));

	}

	public static function isSymbolic(string $path, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// check if it is symbolic
		return(is_link($path));

	}

	public static function isWritable(string $path, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// check if it is writable
		return(is_writable($path));

	}
	
	public static function isNormalName(string $string) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// check if the name starts with a dot
		return(substr($string,0,1) != '.' );

	}

	public static function exists(string $path, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// check for the existence
		return(file_exists($path));

	}
	
	public static function ls(string $path, bool $override_chroot = false) :array {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// prepare the results
		$filtered = array();
		// clean the path and add a trailing slash
		$path = trim($path, '/') . '/';
		// scan the folder
		$files_and_folders = scandir($path);
		// for each found result
		foreach($files_and_folders as $file_or_folder) {
			// ignore filesystem internals
			if(!self::isNormalName($file_or_folder)) {continue;}
			// build the fullpath
			$full_path = $path . $file_or_folder;
			// add a trailing slash if it is a folder
			$full_path .= self::isDirectory($full_path, $override_chroot) ? '/' : '';
			// add the file or folder if it has passed the filter(s)
			$filtered[$full_path] = $file_or_folder;
		}
		// return the filetered content
		return($filtered);

	}

	public static function symlink(string $existing_file, string $link, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$existing_file = self::chroot($existing_file, $override_chroot);
		$link = self::chroot($link, $override_chroot);
		// return false if the target does not exist
		return(symlink($existing_file, $link));

	}

	public static function mkdir(string $path, string $mask = '0777', bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// if the file already exists
		if(self::exists($path, $override_chroot) && self::isDirectory($path, $override_chroot)) {
			// we also return true
			return(true);
		}
		// return the creation status
		return(mkdir($path, $mask, true));

	}
	
	public static function remove(string $path, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// if path exists and is a file
		return(unlink($path));

	}

	public static function get(string $path, bool $override_chroot = false) :string {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// if the file or folder exists
		return(file_get_contents($path));

	}

	public static function put(string $path, $content=null, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// put the content in a file
		return(file_put_contents($path, $content));

	}

	public static function chmod(string $path, string $mask, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// apply the chmod
		return(chmod($path, $mask));

	}

	public static function chown(string $path, string $user, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// apply the chmod
		return(chown($path, $user));

	}

	public static function touch(string $path, int $timestamp=null, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// touch it
		return(touch($path, $timestamp));

	}

	public static function copy(string $source_path, string $destination_path, bool $override_chroot = false) :bool {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$source_path 		= self::chroot($source_path, $override_chroot);
		$destination_path 	= self::chroot($destination_path, $override_chroot);
		// copy the file
		return(copy($source_path, $destination_path));

	}

	public static function info(string $path, bool $override_chroot = false) :array {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);

		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
		// return informations about the file
		return(array(
			'size'			=> filesize($path),
			'modification'	=> filemtime($path),
			'type'			=> self::type($path, $override_chroot)
		));

	}
	
	public static function type(string $path, bool $override_chroot = false) {
		
		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Filesystem is deprecated, require symfony/filesystem instead', 
			E_USER_DEPRECATED
		);
		
		// if chroot is enabled, restrict the path to the chroot
		$path = self::chroot($path, $override_chroot);
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
