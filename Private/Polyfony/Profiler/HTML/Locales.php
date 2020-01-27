<?php

namespace Polyfony\Profiler\HTML;
use Polyfony\Element as Element;
use Polyfony\Format as Format;

class Locales {

	private static $statistics = [];

	public static function createItem(
		string $title, 
		string $class,
		$value
	) :array {

		return [
			'html'=>
				'<strong>'.$title.'</strong> ' . 
				(new Element('span',[
					'class'=>'badge badge-'.$class,
					'text'=>$value
				]))
		];

	}

	public static function getBody(
		\Bootstrap\Dropdown $locales_dropdown
	) :\Bootstrap\Dropdown {

		return $locales_dropdown
			->addHeader(['text'=>'Languages'])
			->addItem(self::createItem(
				'Current',
				'primary',
				\Polyfony\Locales::getLanguage()
			))
			->addItem(self::createItem(
				'Available',
				'secondary',
				implode(
					', ', 
					\Polyfony\Config::get('locales','available')
				)
			));

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
				'class'	=>'btn btn-locales' . (\Polyfony\Config::get('profiler','use_small_buttons') ? ' btn-sm' : ''),
				'style'	=>'margin-left:10px'
			], 'fa fa-globe-europe');

		$locales_dropdown = self::getBody($locales_dropdown);
		
		return $locales_dropdown;

	}

}

?>
