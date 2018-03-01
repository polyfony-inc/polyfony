<?php

namespace Polyfony\Profiler\HTML;

class Queries {

	private static function getQueries($stack) {

		$queries_count = 0;
		$queries = [];

		foreach($stack as $element) {

			if($element['user'] == 'database') {

				++$queries_count;

				$readable_duration 		= round($element['duration']*1000, 1) ? round($element['duration'] * 1000, 1) . ' ms' : '';
				$readable_memory 		= $element['memory'] ? \Polyfony\Format::size($element['memory']) : '';

				$execution_time = new \Polyfony\Element('span', [
					'text'	=>' '.$readable_duration,
					'style'	=>'clear:right;margin-top:7px',
					'class'	=>'text-secondary float-right'
				]);
				$execution_time->adopt(new \Polyfony\Element('span',['class'=>'fa fa-stopwatch']), true);
				$execution_memory = new \Polyfony\Element('span', [
					'text'	=>' '.$readable_memory,
					'class'	=>'text-dark float-right'
				]);
				$execution_memory->adopt(new \Polyfony\Element('span',		['class'=>'fa fa-microchip']), true);
				$card 					= new \Polyfony\Element('div', 		['style'=>'border-bottom:solid 1px #efefef;padding-bottom:12px;margin-bottom:12px;overflow:hidden;']);
				$title_container 		= new \Polyfony\Element('code', 	['class'=>'text-dark','style'=>'padding-right:20px;']);
				$title_prefix			= new \Polyfony\Element('strong', 	['text'=>"Query #{$queries_count} ",'class'=>'text-danger']);
				$title 					= new \Polyfony\Element('span', 	['text'=>$element['informations']['Query']->getQuery()]);
				$parameters_container 	= new \Polyfony\Element('div', 		['style'=>'padding-top:0px']);
				$parameters 			= new \Polyfony\Element('code',		['class'=>'text-success','text'=>json_encode($element['informations']['Query']->getValues(),JSON_PRETTY_PRINT)]);

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

		return $queries;

	}

	public static function getComponent(array $data) :\Bootstrap\Modal {

		$queries = self::getQueries($data['stack']);

		$queries_modal = new \Bootstrap\Modal('large');
		$queries_modal
			->setTrigger([
				'html'	=>' Queries <span class="badge badge-light">'.count($queries).'</span>',
				'class'	=>'btn btn-danger',
				'style'	=>'margin-left:10px'
			], 'fa fa-database')
			->setTitle(	['html'=>' &nbsp;Queries'], 'fa fa-database')
			->setBody(	['html'=>implode(' ', $queries)]);

		return $queries_modal;

	}

}

?>
