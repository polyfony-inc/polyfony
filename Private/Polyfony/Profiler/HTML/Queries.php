<?php

namespace Polyfony\Profiler\HTML;

use Polyfony\Element as Element;

class Queries {

	private static function getQueriesAndTheirDuration($stack) {

		$queries_count = 0;
		$queries_duration = 0;
		$queries = [];

		foreach($stack as $element) {

			if($element['user'] == 'database') {

				++$queries_count;

				$queries_duration 		+= $element['duration']*1000;
				$readable_duration 		= round($element['duration']*1000, 1) ? round($element['duration'] * 1000, 1) . ' ms' : '';
				$readable_memory 		= $element['memory'] ? \Polyfony\Format::size($element['memory']) : '';

				$execution_time = new Element('span', [
					'text'	=>' '.$readable_duration,
					'style'	=>'clear:right;margin-top:7px',
					'class'	=>'text-secondary float-right'
				]);
				$execution_time->adopt(new Element('span',['class'=>'fa fa-stopwatch']), true);
				$execution_memory = new Element('span', [
					'text'	=>' '.$readable_memory,
					'class'	=>'text-dark float-right'
				]);
				$execution_memory->adopt(new Element('span',		['class'=>'fa fa-microchip']), true);
				$card 					= new Element('div', 		['style'=>'border-bottom:solid 1px #efefef;padding-bottom:12px;margin-bottom:12px;overflow:hidden;']);
				$title_container 		= new Element('code', 	['class'=>'text-dark','style'=>'padding-right:20px;']);
				$title_prefix			= new Element('strong', 	['text'=>"Query #{$queries_count} ",'class'=>'text-database']);
				$title 					= new Element('span', 	['text'=>$element['informations']['Query']->getQuery()]);
				$parameters_container 	= new Element('div', 		['style'=>'padding-top:0px']);
				$parameters 			= new Element('code',		['class'=>'text-success','html'=>self::getFormattedParameters($element)]);

				$queries[$queries_count] = $card
					->adopt($execution_memory)
					->adopt($execution_time)
					->adopt($title_container
						->adopt($title_prefix)
						->adopt($title))
					->adopt($parameters_container
						->adopt($parameters));
			}
		}

		return [$queries, $queries_duration];

	}

	public static function getComponent(array $data) :\Bootstrap\Modal {

		list($queries, $queries_duration) = self::getQueriesAndTheirDuration($data['stack']);

		$queries_modal = new \Bootstrap\Modal('large');
		$queries_modal
			->setTrigger([
				'html'	=>
					' Queries <span class="badge badge-light">'.count($queries).
					' <span class="text-secondary" style="font-weight:lighter;">in <strong>' . 
					round($queries_duration, 1) .'</strong> ms</span></span>',
				'class'	=>'btn btn-database' . (\Polyfony\Config::get('profiler','use_small_buttons') ? ' btn-sm' : ''),
				'style'	=>'margin-left:10px'
			], 'fa fa-database')
			->setTitle(	['html'=>' &nbsp;Queries'], 'fa fa-database')
			->setBody(	['html'=>implode(' ', $queries)]);

		return $queries_modal;

	}

	public static function getFormattedParameters(array $element) : string {

		return nl2br(
			str_replace(
				' ',
				'&nbsp;',
				htmlentities(
					json_encode(
						$element['informations']['Query']->getValues(),
						JSON_PRETTY_PRINT
					)
				)
			)
		);
	}

}

?>
