<?php 

namespace Models;

class AccountsLogins extends \Polyfony\Entity {
	
	const HAS_FAILED = [
		0=>'No',
		1=>'Yes'
	];

	const HAS_SUCCEEDED = [
		0=>'No',
		1=>'Yes'
	];

	const VALIDATORS = [
		'has_failed'		=>self::HAS_FAILED,
		'has_succeeded'		=>self::HAS_SUCCEEDED,
		'originating_from'	=>FILTER_VALIDATE_IP
	];

}

?>
