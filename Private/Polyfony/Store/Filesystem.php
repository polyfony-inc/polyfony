<?php

namespace Polyfony\Store;

class Filesystem implements StoreInterface {

	private static function path(string $variable) :array {

		// compute a hash for that file
		$hash = md5($variable);
		// get the two first chars of the checksum
		list(
			$first_folder, 
			$second_folder
		) = str_split(substr($hash, 0, 2));
		// assemble the full path with two subfolders
		return array(
			\Polyfony\Config::get('store', 'path') . 
			$first_folder .'/'. 
			$second_folder .'/',
			$hash
		);

	}

	public static function has(string $variable) :bool {
		
		// get the path for that variable
		list($path, $file) = self::path($variable);

		// if it exists
		return file_exists($path . $file);
		
	}
	

	public static function put(
		string $variable, 
		$value, 
		bool $overwrite = false,
		?int $lifetime = null
	) :bool {
	
		// already exists and no overwrite
		if(
			self::has($variable) && 
			!$overwrite
		) {
			// throw an exception
			\Polyfony\Exception("{$variable} already exists in the store.");
		}
		
		// get the path for that variable
		list($path, $file) = self::path($variable);

		// if the path doesn't exist yet
		if(!is_dir($path)) {

			// create the directories
			mkdir($path, 0777, true);

		}

		// store the variable or document
		\Polyfony\Config::get('store', 'compress') ? 
			file_put_contents($path . $file, gzdeflate(msgpack_pack($value))) :
			file_put_contents($path . $file, msgpack_pack($value));

		// return status
		return self::has($variable);
		
	}
	

	public static function get(string $variable) {
		
		// doesn't exist in the store
		if(!self::has($variable)) {
			// throw an exception
			\Polyfony\Exception("{$variable} does not exist in the store.");
		}

		// get the path for that variable
		list($path, $file) = self::path($variable);

		// return it
		return \Polyfony\Config::get('store', 'compress') ? 
			msgpack_unpack(gzinflate(file_get_contents($path . $file))) :
			msgpack_unpack(file_get_contents($path . $file));

	}
	
	
	public static function remove(string $variable) :bool {
		
		// doesn't exist in the store
		if(!self::has($variable)) {
			// return false
			return false;	
		}

		// get the path for that variable
		list($path, $file) = self::path($variable);

		// return it
		unlink($path . $file);
		// return opposite of presence of the object
		return(!self::has($variable));
		
	}


}

?>
