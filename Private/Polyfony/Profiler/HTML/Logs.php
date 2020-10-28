<?php

namespace Polyfony\Profiler\HTML;

use \Polyfony\Element as Element;
use \Polyfony\Logger as Logger;

class Logs {

	

	private static function getLogs($stack) {

		$logs_count = 0;
		$logs = [];

		foreach($stack as $element) {

			if($element['user'] == 'log') {

				++$logs_count;
				
				$title_container = new Element('code', [
					'class'=>'text-dark',
					'style'=>'padding-right:20px;'
				]);
				$title_prefix = new Element('strong', [
					'text'	=>ucfirst($element['informations']['level'])." #{$logs_count} ",
					'class'	=>'text-'.Logger::CLASSES[$element['informations']['id_level']]
				]);
				$title 					= new Element('span', ['text'=>$element['name']]);
				$parameters_container 	= new Element('div', ['style'=>'padding-top:0px']);
				$parameters 			= $element['informations']['context'] ? new Element('code', [
					'class'	=>'text-success',
					'html'	=>self::getFormattedContext($element)
				]) : new Element('span');

				$logs[$logs_count] = (new Element('div',  ['style'=>[
						'border-bottom'	=>'solid 1px #efefef',
						'padding-bottom'=>'12px',
						'margin-bottom'	=>'12px',
						'overflow'		=>'hidden',
					]]))
					->adopt($title_container
						->adopt($title_prefix)
						->adopt($title))
					->adopt($parameters_container
						->adopt($parameters));
			}
		}

		return $logs;

	}

	public static function getComponent(array $data) :\Bootstrap\Modal {

		$logs = self::getLogs($data['stack']);

		return (new \Bootstrap\Modal('large'))
			->setTrigger([
				'html'	=>' Logs <span class="badge text-dark bg-light">'.count($logs).'</span>',
				'class'	=>'btn btn-logs' . (\Polyfony\Config::get('profiler','use_small_buttons') ? ' btn-sm' : ''),
				'style'	=>'margin-left:10px'
			], 'fas fa-exclamation-circle')
			->setTitle(	['html'=>' &nbsp;Logs'], 'fas fa-exclamation-circle')
			->setBody(	['html'=>implode(' ', $logs)]);

	}

	private static function getFormattedContext($element) : ?string {

		return $element['informations']['context'] ? 
			nl2br(str_replace(" ",'&nbsp;',print_r(
				$element['informations']['context'],
				true
			))) : null;

	}

}

?>
