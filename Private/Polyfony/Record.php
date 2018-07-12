<?php
 
namespace Polyfony;

class Record extends Record\Form {
	
	// get a truncated field, that is safe to place in a HTML document
	public function tget(string $column, int $length=32) {
		// we get the raw value, truncate it, and escape it afterwards
		return strlen($this->get($column, true)) > $length ? 
			Format::htmlSafe(
				Format::truncate(
					$this->get($column, true),
					$length
				)
			) : 
			$this->get($column);
	}

	// put the record into the trash
	public function trash() {
		// and return the object for chaining
		return $this->set([
			'trashed_on'=>time(),
			'trashed_by'=>Security::get('id')
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
