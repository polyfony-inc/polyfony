<?php

namespace Polyfony\Profiler;

// generate an HTML Profiler that will be fixed to the bottom of a page
class HTML {

	// bootstrap classes for these users
	const USERS_CLASSES = [
		'framework'	=>'secondary', 
		'controller'=>'primary', 
		'view'		=>'success', 
		'database'	=>'danger', 
		'email'		=>'warning', 
		'user'		=>'info',
		'log'		=>'info'
	];

	public static function getContainer() :\Polyfony\Element {

		return new \Polyfony\Element('div', [
			'id'	=>'pfProfiler',
			'class'	=>[
				'd-print-none'
			],
			'style'	=>[
				'position'			=>'fixed',
				'z-index'			=>1400,
				'bottom'			=>0,
				'left'				=>0,
				'padding-left'		=>'10px',
				'padding-bottom'	=>'10px'
			]
		]);

	}

	public static function getProfiler() :\Polyfony\Element {

		// get profiler raw data
		$data = \Polyfony\Profiler::getData();
		
		// assemble the profiler
		return (self::getContainer())
			->adopt(HTML\Timing::getComponent	($data))
			->adopt(HTML\Memory::getComponent	($data))
			->adopt(HTML\Routing::getComponent	($data))
			->adopt(HTML\Queries::getComponent	($data))
			->adopt(HTML\Emails::getComponent	($data))
			->adopt(HTML\Logs::getComponent		($data))
			->adopt(HTML\Security::getComponent	($data));

	}

	public function __toString() {

		// conversion to pure html string
		return (string) self::getProfiler();

	}


}

?>
