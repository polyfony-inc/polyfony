<?php

namespace Polyfony\Profiler\HTML;
use Polyfony\Element as Element;
use Polyfony\Format as Format;

class Locales {

	private static $statistics = [];

	public static function getBody(\Bootstrap\Dropdown $locales_dropdown) :\Bootstrap\Dropdown {

		$locales_dropdown->addHeader(['text'=>'Languages']);
		$locales_dropdown->addItem([
			'html'=>
				'<strong>Current</strong> ' . 
				(new Element('span',[
					'class'=>'badge badge-primary',
					'text'=>\Polyfony\Locales::getLanguage()
				]))
		]);
		$locales_dropdown->addItem([
			'html'=>
				'<strong>Available</strong> ' . 
				(new Element('span',[
					'class'=>'badge badge-secondary',
					'text'=>implode(', ', \Polyfony\Config::get('locales','available'))
				]))
		]);

		return $locales_dropdown;

	}

	public static function getComponent() :\Bootstrap\Dropdown {

		self::$statistics = \Polyfony\Locales::getStatistics();

		$locales_dropdown = new \Bootstrap\Dropdown();
		$locales_dropdown
			->setTrigger([
				'html'	=>' Locales ' . 
					(new Element('span',[
						'class'=>'badge badge-light',
						'html'=> self::$statistics['locales_count'] . ' ' . 
						'<span class="text-secondary" style="font-weight:lighter;"> in <strong>'.
						round(self::$statistics['load_time']*1000, 1)
						.' ms</strong></span>'
					])),
				'class'	=>'btn btn-success' . (\Polyfony\Config::get('profiler','use_small_buttons') ? ' btn-sm' : ''),
				'style'	=>'margin-left:10px'
			], 'fa fa-globe-europe');

		$locales_dropdown = self::getBody($locales_dropdown);
		
		return $locales_dropdown;

	}

}

?>
