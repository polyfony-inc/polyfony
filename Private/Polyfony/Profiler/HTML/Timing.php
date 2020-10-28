<?php

namespace Polyfony\Profiler\HTML;
use Polyfony\Element as Element;
use Polyfony\Config as Config;
use Polyfony\Profiler\HTML as Profiler;

class Timing {

	private static function applySpecificNaming($element) {

		if($element['user'] == 'database') {
			$element['name'] = ucfirst(
				strtolower(
					$element['informations']['Query']->getExecutedAction()
				)
			);
		}
		return $element;
	}

	private static function getBody($data) {

		$timing_body = [];
		// for each stacked element
		foreach($data['stack'] as $elem) {
			// a color class for that type of element
			$class 					= Profiler::USERS_CLASSES[$elem['user']];
			// width depends on the duration, cannot reach 100% but 95% (to allow for non overflowing elements on the right)
			$width 					= round($elem['duration'] * 95 / $data['time'],1);
			// height depends on the memory consuption, the thickness/height is on a natural logarythmic scale
			$height 				= log($elem['memory']);
			// absolute start relative to the start of the script
			$relative_start 		= $elem['start'] - $data['start_time'];
			// relative start, in percent
			$relative_start_percent = round($relative_start * 95 / $data['time'],1);
			// human durable duration
			$readable_duration 		= round($elem['duration']*1000, 1) ? round($elem['duration'] * 1000, 1) . ' ms' : '';
			// human readable memory consumption
			$readable_memory 		= $elem['memory'] ? \Polyfony\Format::size($elem['memory']) : '';
			// trick for database queris
			$elem = self::applySpecificNaming($elem);
			// the actual bar/stack element
			$timing_body[] = new Element('div', [
				'style'	=>[
					'min-height'	=>'6px',
					'height'		=>"{$height}px",
					'min-width'		=>'6px',
					'width'			=>"{$width}%",
					'margin-left'	=>"{$relative_start_percent}%",
					'margin-bottom'	=>'1px',
					'border-radius'	=>'4px'
				],
				'class'	=>"bg-{$class}"
			]);
			// then label/details for that bar/stack element
			$timing_body[] = new Element('div', [
				'style'	=>[
					'font-size'		=>'11px',
					'margin-left'	=>"{$relative_start_percent}%",
					'margin-bottom'	=>'1px'
				],
				'class'	=>"text-{$class}",
				'html'	=> 
					(new Element('strong', ['text'=>$elem['name']])) . ' ' .
					(new Element('i', ['text'=>"{$readable_duration} {$readable_memory}"]))
			]);
		}

		return $timing_body;

	}

	private static function getTriggerClass(
		float $execution_time_in_seconds
	) :string {

		if(
			$execution_time_in_seconds > 
			Config::get('profiler','danger_threshold', 0.5)
		) {
			$class = 'danger';
		}
		elseif(
			$execution_time_in_seconds > 
			Config::get('profiler','warning_threshold', 0.25)
		) {
			$class = 'warning';
		}
		elseif(
			$execution_time_in_seconds > 
			Config::get('profiler','success_threshold', 0.1)
		) {
			$class = 'success';
		}
		else {
			$class = 'primary';
		}

		// we may want to add the small button class too
		return $class . (\Polyfony\Config::get('profiler','use_small_buttons') ? ' btn-sm' : '');

	}

	private static function getTrigger(
		array $data
	) :array {

		$execution_time_in_seconds = round($data['time'], 3);

		return [
			'html'	=>' Execution '.
			(new Element('span',[
				'class'	=>'badge text-dark bg-light',
				'html'	=> $execution_time_in_seconds . ' sec' . 
				'<span class="text-secondary" style="font-weight:lighter;"> and <strong>'.
				\Polyfony\Format::size(memory_get_peak_usage())
				.'</strong></span>'
			])),
			'class'	=>'btn btn-'.self::getTriggerClass($execution_time_in_seconds)
		];

	}

	private static function getLegend() :array {

		// the general legend for the waterfall graphics
		$legend=[];
		$legend[] = new Element('span',[
			'class'=>'badge text-light badge-'.Profiler::USERS_CLASSES['framework'],
			'text'=>'Framework'
		]);
		$legend[] = new Element('span',[
			'class'=>'badge text-light badge-'.Profiler::USERS_CLASSES['controller'],	
			'text'=>'Controllers'
		]);
		$legend[] = new Element('span',[
			'class'=>'badge text-light badge-'.Profiler::USERS_CLASSES['view'],	
			'text'=>'Views'
		]);
		$legend[] = new Element('span',[
			'class'=>'badge text-light badge-'.Profiler::USERS_CLASSES['database'],	
			'text'=>'Database'
		]);
		$legend[] = new Element('span',[
			'class'=>'badge text-light badge-'.Profiler::USERS_CLASSES['email'],	
			'text'=>'Emails'
		]);
		$legend[] = new Element('span',[
			'class'=>'badge text-light badge-'.Profiler::USERS_CLASSES['log'],	
			'text'=>'User defined'
		]);
		return $legend;

	}

	public static function getComponent(array $data) :\Bootstrap\Modal {

		$timing_modal = new \Bootstrap\Modal('xxl');
		
		$timing_body = self::getBody($data);

		

		$timing_modal
			->setTrigger(
				self::getTrigger($data), 
				'fa fa-stopwatch'
			)
			->setTitle([
				'html'=>' &nbsp;Execution stack'
			], 'fa fa-stopwatch')
			->setBody([
				'style'=>'background:url(data:image/gif;base64,R0lGODlhGwABAIAAAPHx8fHx8SH5BAEKAAEALAAAAAAbAAEAAAIFjI+pCQUAOw==)',
				'html'=>implode(' ',$timing_body)
			])
			->setFooter([
				'html'=>implode(' ',self::getLegend())
			]);

		return $timing_modal;


	}

}

?>
