<?php
 
namespace Polyfony;

class Entity extends Entity\Form {

	// put the record into the trash
	public function trash() {
		// and return the object for chaining
		return $this->set([
			'trashed_on'=>time(),
			'trashed_by'=>Security::isAuthenticated() ? 
				Security::getAccount()->get('id') : 
				null
		]);
	}

	// remove the object from the trash
	public function untrash() {
		// and return the object for chaining
		return $this->set([
			'trashed_on'=>null,
			'trashed_by'=>null
		]);
	}

}


?>
