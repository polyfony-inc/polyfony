<?php

namespace Polyfony\Store;

class Database implements StoreInterface {

	public static function has($variable) {
		// if no variable is provieded
		if(!$variable) {
			// throw an exception by security to prevent accidental database operations
			\Polyfony\Exception("Variable name is missing");
		}
		// return the presence of that key
		return((boolean) 
			\Polyfony\Database::query()
			->select(array('key'))
			->from('Store')
			->where(array('key' => $variable))
			->execute()
		);
	}

	public static function put($variable, $value=null, $overwrite=false) {
		// do we have that variable
		$existing_variable = self::has($variable);
		// variable already exist in the store and we can override it
		if($existing_variable && $overwrite) {
			return((boolean)
				\Polyfony\Database::query()
				->update('Store')
				->set(array('content'=>serialize($value)))
				->where(array('key'=>$variable))
				->execute()
			);
		}
		// variable already exists but we cannot override it
		elseif($existing_variable) {
			// throw an exception
			Throw new \Polyfony\Exception("{$variable} already exists in the store.");
		}
		// variable does not exist, we store it
		else {
			return((boolean)
				\Polyfony\Database::query()
				->insert(array(
					'key'		=>$variable,
					'content'	=>serialize($value)
				))
				->into('Store')
				->execute()
			);
		}

	}

	public static function get($variable) {
		// doesn't exist in the store
		if(!self::has($variable)) {
			// return false
			return(false);	
		}
		// retrieve from the database
		list($record) = \Polyfony\Database::query()
			->select()
			->from('Store')
			->where(array('key'=>$variable))
			->execute();
		// decode and return the content
		return(unserialize($record->get('content', true)));
	}

	public static function remove($variable) {
		// doesn't exist in the store
		if(!self::has($variable)) {
			// return false
			return(false);	
		}
		// remove the entry
		\Polyfony\Database::query()
			->delete()
			->from('Store')
			->where(array('key'=>$variable))
			->execute();
		// return opposite of presence of the object
		return(!self::has($variable));
	}

}

?>
