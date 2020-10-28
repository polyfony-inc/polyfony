<?php

namespace Polyfony\Profiler\HTML;
use Polyfony\Element as Element;
use Polyfony\Format as Format;
use Bootstrap\Dropdown as Dropdown;

class Cache {

	private static $statistics = [];

	private static function getBodyHits(
		Dropdown $cache_dropdown
	) :Dropdown {
		
		return $cache_dropdown
			->addHeader(['text'=>'Hits'])
			->addItem(Locales::createItem(
				'Count',
				'success',
				self::$statistics['hits_count']
			))
			->addItem(Locales::createItem(
				'Time',
				'light',
				round(
					self::$statistics['cache_out_time']*1000, 
					1
				). ' ms'
			))
			->addDivider();
	} 

	private static function getBodyMisses(
		Dropdown $cache_dropdown
	) :Dropdown {

		return $cache_dropdown
			->addHeader(['text'=>'Misses'])
			->addItem(Locales::createItem(
				'Count',
				'warning',
				self::$statistics['misses_count']
			))
			->addItem(Locales::createItem(
				'Time',
				'light',
				round(
					self::$statistics['cache_in_time']*1000, 
					1
				). ' ms'
			))
			->addDivider();

	}

	private static function getBodyMisc(
		Dropdown $cache_dropdown
	) :Dropdown {

		return $cache_dropdown
			->addHeader(['text'=>'Settings'])
			->addItem([
				'html'=>
					'<strong>Request</strong> ' . 
					(\Polyfony\Request::isCacheAllowed() ? 
						new Element('span', [
							'class'=>'badge bg-success text-light',
							'text'=>'Allowed'
						]) : 
						new Element('span', [
							'class'=>'badge text-light bg-warning',
							'text'=>'Disallowed'
						]))
			])
			->addItem([
				'html'=>
					'<strong>Response</strong> ' . 
					(\Polyfony\Config::get('response','cache') ? 
						new Element('span', [
							'class'=>'badge text-light bg-success',
							'text'=>'Allowed'
						]) : 
						new Element('span', [
							'class'=>'badge text-light bg-warning',
							'text'=>'Disallowed'
						]))
			]);

	}

	public static function getBody(
		Dropdown $cache_dropdown
	) :Dropdown {

		$cache_dropdown = self::getBodyHits($cache_dropdown);
		$cache_dropdown = self::getBodyMisses($cache_dropdown);
		$cache_dropdown = self::getBodyMisc($cache_dropdown);

		return $cache_dropdown;
	}

	public static function getComponent() :Dropdown {

		self::$statistics = \Polyfony\Cache::getStatistics();

		return self::getBody((new Dropdown)
			->setTrigger([
				'html'	=>' Cache ' . 
					(new Element('span',[
						'class'=>'badge text-light bg-light',
						'html'=> self::$statistics['hits_count'] . ' hits' . 
						'<span class="text-secondary" style="font-weight:lighter;"> in <strong>'.
						round(self::$statistics['cache_out_time']*1000, 1)
						.' ms</strong></span>'
					])),
				'class'	=>'btn btn-cache' . (\Polyfony\Config::get('profiler','use_small_buttons') ? ' btn-sm' : ''),
				'style'	=>'margin-left:10px'
			], 'fa fa-fighter-jet'));

	}

}

?>
