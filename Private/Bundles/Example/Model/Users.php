<?php

namespace Bundles\Example\Model;


// models allow to you to put all queries in a single place and reuse them later on from anywhere
class Users {

	public static function all() {
	
		return(
			\Polyfony\Database::query()
				->select()
				->from('Accounts')
				->execute()
		);
		
	}
	
	public static function loggedIn() {
		
	}
	
	public static function withLevel($id_level=1) {
		
		return(
			\Polyfony\Database::query()
				->select()
				->from('Accounts')
				->where(array('id_level'=>$id_level))
				->execute()
		);
		
	}
	
	
}

?>