<?php 

namespace Models;

use \Polyfony\{
	Locales, 
	Element
};

class AccountsRoles extends \Polyfony\Security\AccountsRoles {
	
	const IS_ALMIGHTY = [
		0=>'No',
		1=>'Yes'
	];

	const VALIDATORS = [
		'is_almighty'=>self::IS_ALMIGHTY
	];

	const FILTERS = [
		'name'	=>['length32','trim','ucfirst','capslock30'],
		'color'	=>['length8','strtolower','trim']
	];

	public function getBadge() :Element {

		return new Element('span', [
			'class'	=>'badge badge-pill badge-light',
			'text'	=>Locales::get(
				$this->get('name', true)
			),
			'style'=>[
				'background-color'=>$this->get('color', true)
			]
		]);

	}

	public function getDot() :Element {

		return new Element('span', [
			'html'	=>'&nbsp',
			'style'	=>[
				'float'			=>'left',
				'margin-top'	=>'2px',
				'display'		=>'inline-block',
				'margin-right'	=>'4px',
				'height'		=>'8px',
				'width'			=>'8px',
				'border-radius'	=>'4px',
				'background-color'=>$this->get('color', true)
			]
		]);

	}

}

?>
