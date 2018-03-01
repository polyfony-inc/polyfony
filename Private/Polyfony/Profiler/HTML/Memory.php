<?php

namespace Polyfony\Profiler\HTML;

class Memory {

	public static function getComponent(array $data) :\Bootstrap\Dropdown {

		// MEMORY
		$memory_dropdown = new \Bootstrap\Dropdown();
		$memory_dropdown
			->setTrigger([
				'html'	=>' Memory '.
				(new \Polyfony\Element('span',[
					'class'=>'badge badge-light',
					'text'=>\Polyfony\Format::size($data['memory'])
				])),
				'class'	=>'btn btn-light',
				'style'	=>'margin-left:10px'
			], 'fa fa-microchip');

		$peak = new \Polyfony\Element('span',['text'=>\Polyfony\Format::size(memory_get_peak_usage()),'class'=>'badge badge-secondary']);
		$memory_dropdown->addItem([
			'html'=>"<strong>Peak</strong> {$peak}"
		]);
		$total = new \Polyfony\Element('span',['text'=>\Polyfony\Format::size(memory_get_usage()),'class'=>'badge badge-secondary']);
		$memory_dropdown->addItem([
			'html'=>"<strong>Current</strong> {$total}"
		]);

		return $memory_dropdown;

	}

}

?>
