<?php

namespace Polyfony\Profiler\HTML;
use Polyfony\Element as Element;
use Polyfony\Format as Format;

class Cache {

	private static $statistics = [];

	public static function getBody(\Bootstrap\Dropdown $cache_dropdown) :\Bootstrap\Dropdown {

		$cache_dropdown->addHeader(['text'=>'Hits']);
		$cache_dropdown->addItem([
			'html'=>
				'<strong>Count</strong> ' . 
				(new Element('span',[
					'class'=>'badge badge-success',
					'text'=>self::$statistics['hits_count']
				]))
		]);
		$cache_dropdown->addItem([
			'html'=>
				'<strong>Time</strong> ' . 
				(new Element('span',[
					'class'=>'badge badge-light',
					'text'=>round(self::$statistics['cache_out_time']*1000, 1). ' ms'
				]))
		]);
		$cache_dropdown->addDivider();
		$cache_dropdown->addHeader(['text'=>'Misses']);
		$cache_dropdown->addItem([
			'html'=>
				'<strong>Count</strong> ' . 
				(new Element('span',[
					'class'=>'badge badge-warning',
					'text'=>self::$statistics['misses_count']
				]))
		]);
		$cache_dropdown->addItem([
			'html'=>
				'<strong>Time</strong> ' . 
				(new Element('span',[
					'class'=>'badge badge-light',
					'text'=>round(self::$statistics['cache_in_time']*1000, 1). ' ms'
				]))
		]);
		$cache_dropdown->addDivider();
		$cache_dropdown->addHeader(['text'=>'Caches']);
		$cache_dropdown->addItem([
			'html'=>
				'<strong>Request</strong> ' . 
				(\Polyfony\Request::isCacheAllowed() ? 
					new Element('span', ['class'=>'badge badge-success','text'=>'Allowed']) : 
					new Element('span', ['class'=>'badge badge-warning','text'=>'Disallowed']))
		]);
		$cache_dropdown->addItem([
			'html'=>
				'<strong>Response</strong> ' . 
				(\Polyfony\Config::get('response','cache') ? 
					new Element('span', ['class'=>'badge badge-success','text'=>'Allowed']) : 
					new Element('span', ['class'=>'badge badge-warning','text'=>'Disallowed']))
		]);
		

		return $cache_dropdown;

	}

	public static function getComponent() :\Bootstrap\Dropdown {

		self::$statistics = \Polyfony\Cache::getStatistics();

		$cache_dropdown = new \Bootstrap\Dropdown();
		$cache_dropdown
			->setTrigger([
				'html'	=>' Cache ' . 
					(new Element('span',[
						'class'=>'badge badge-light',
						'html'=> self::$statistics['hits_count'] . ' hits' . 
						'<span class="text-secondary" style="font-weight:lighter;"> in <strong>'.
						round(self::$statistics['cache_out_time']*1000, 1)
						.' ms</strong></span>'
					])),
				'class'	=>'btn btn-info' . (\Polyfony\Config::get('profiler','use_small_buttons') ? ' btn-sm' : ''),
				'style'	=>'margin-left:10px'
			], 'fa fa-fighter-jet');

		$cache_dropdown = self::getBody($cache_dropdown);
		
		return $cache_dropdown;

	}

}

?>
