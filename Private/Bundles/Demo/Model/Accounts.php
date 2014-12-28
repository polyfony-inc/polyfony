<?php

// this is a model class
// it's a repository for stored SQL queries
// there should not be any functionnal code or conditions here


namespace Bundles\Demo\Model;

class Accounts {

	public static function all() {
		return(\Polyfony\Database::query()
			->select()
			->from('Accounts')
			->execute()
		);
	}
	
	public static function recentlyCreated($maximum=5) {
		return(\Polyfony\Database::query()
			->select()
			->from('Accounts')
			->orderBy(array('creation_date'=>'DESC'))
			->limitTo(0,$maximum)
			->execute()
		);
	}
	
	public static function disabled() {
		return(\Polyfony\Database::query()
			->select()
			->from('Accounts')
			->whereNull('is_enabled')
			->execute()
		);
	}
	
	public static function forcedRecently($what_recent_means=3600) {
		return(\Polyfony\Database::query()
			->select()
			->from('Accounts')
			->whereNotNull('last_failure_date')
			->addAnd()
			->whereHigherThan('last_failure_date',time()-$what_recent_means)
			->execute()
		);
	}
	
}

?>
