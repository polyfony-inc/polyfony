<?php

namespace Polyfony\Profiler\HTML;

class Security {

	public static function getBody(\Bootstrap\Dropdown $security_dropdown) : \Bootstrap\Dropdown {

		$user = new \Polyfony\Element('code', ['text'=>\Polyfony\Security::get('login')]);
		$security_dropdown->addItem([
			'html'=>"<strong>User</strong> {$user}"
		]);
		$level = new \Polyfony\Element('span', ['class'=>'badge badge-primary','text'=>\Polyfony\Security::get('id_level')]);
		$security_dropdown->addItem([
			'html'=>"<strong>Level</strong> {$level}"
		]);
		$modules = [];
		foreach((array) \Polyfony\Security::get('modules_array') as $module) {
			$modules[] = new \Polyfony\Element('span', ['class'=>'badge badge-primary','text'=>$module]);
		}
		$modules = implode(' ', $modules);
		$security_dropdown->addItem([
			'html'=>"<strong>Modules</strong> {$modules}"
		]);

		$security_dropdown->addDivider();

		return $security_dropdown;

	}

	public static function getComponent() : \Bootstrap\Dropdown {

		// SECURITY
		$security_dropdown = new \Bootstrap\Dropdown();
		$security_dropdown
			->setTrigger([
				'text'	=>' ' . (\Polyfony\Security::get('login') ? \Polyfony\Security::get('login') : 'n/a'),
				'class'	=>'btn btn-light' . (\Polyfony\Config::get('profiler','use_small_buttons') ? ' btn-sm' : ''),
				'style'	=>'margin-left:10px'
			], 'fa fa-user-circle');
		

		$security_dropdown = self::getBody($security_dropdown);

		$loggedin = new \Polyfony\Element('span', ['text'=>\Polyfony\Security::get('last_login_date'),'class'=>'badge badge-secondary']);
		$security_dropdown->addItem([
			'html'=>"<strong>Logged</strong> {$loggedin}"
		]);
		$expiration = new \Polyfony\Element('span', ['text'=>\Polyfony\Security::get('session_expiration_date'),'class'=>'badge badge-secondary']);
		$security_dropdown->addItem([
			'html'=>"<strong>Expires</strong> {$expiration}"
		]);

		return $security_dropdown;

	}

}

?>
