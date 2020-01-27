<?php

namespace Polyfony\Profiler;
use Polyfony\Element as Element;

// generate an HTML Profiler that will be fixed to the bottom of a page
class HTML {

	// bootstrap classes for these users
	const USERS_CLASSES = [
		'framework'	=>'framework', 
		'controller'=>'controller', 
		'view'		=>'view', 
		'database'	=>'database', 
		'email'		=>'email', 
		'user'		=>'logs',
		'log'		=>'logs'
	];

	const STYLES = '
#pfProfiler .btn-framework, 
#pfProfiler .badge-framework,
#pfProfiler .bg-framework {
	background-color: #000000;
	color: white;
}
#pfProfiler .btn-controller,
#pfProfiler .badge-controller,
#pfProfiler .bg-controller {
	color: white;
	background-color: #e27e00;
}
#pfProfiler .text-controller {
	color: #e27e00;
}
#pfProfiler .btn-view,
#pfProfiler .badge-view, 
#pfProfiler .bg-view {
	background-color: #888888;
	color: white;
}
#pfProfiler .text-view {
	color: #888888;
}
#pfProfiler .btn-database,

#pfProfiler .label-database {
	color: white;
	background-color: #5a4a6c;
}
#pfProfiler .badge-database,
#pfProfiler .bg-database {
	color: white;
	background-color: #6937a2;
}
#pfProfiler .text-database {
	color: #6937a2;
}
#pfProfiler .btn-email,
#pfProfiler .badge-email,
#pfProfiler .label-email,
#pfProfiler .bg-email {
	color: white;
	background-color: #407b7a;
}
#pfProfiler .text-email {
	color: #407b7a;
}
#pfProfiler .btn-user,
#pfProfiler .badge-user {}

#pfProfiler .btn-locales {
	color: white;
	background-color: #40587b;
}
#pfProfiler .btn-logs {
	background-color: #885765;
	color: white;
}
#pfProfiler .bg-logs,
#pfProfiler .badge-logs {
	color: white;
	background-color: #a02649;
}
#pfProfiler .text-logs {
	color: #a02649;
}
#pfProfiler .btn-cache {
	background-color: #3b7a94;
	color: white;
}
#pfProfiler .btn-security {
	background-color: #888757;
	color: white;
}
';

	public static function getContainer() :Element {

		return new \Polyfony\Element('div', [
			'id'	=>'pfProfiler',
			'class'	=>[
				'd-print-none'
			],
			'style'	=>[
				'position'			=>'fixed',
				'width'				=>'100%',
				'z-index'			=>1400,
				'bottom'			=>0,
				'left'				=>0,
				'padding'			=>'10px',
				'background'		=>'rgba(0,0,0,0.2)',
				'backdrop-filter'	=>'blur(3px)'
			]
		]);

	}

	public static function getStyles() :Element {

		return new Element('style',[
			'text'=>self::STYLES,
			'type'=>'text/css'
		]);

	}

	public static function getProfiler() :Element {

		// get profiler raw data
		$data = \Polyfony\Profiler::getData();
		
		// assemble the profiler
		return (self::getContainer())
			->adopt(self::getStyles())
			->adopt(HTML\Timing::getComponent	($data))
			->adopt(HTML\Routing::getComponent	($data))
			->adopt(HTML\Queries::getComponent	($data))
			->adopt(HTML\Emails::getComponent	($data))
			->adopt(HTML\Cache::getComponent	($data))
			->adopt(HTML\Locales::getComponent	($data))
			->adopt(HTML\Logs::getComponent		($data))
			->adopt(HTML\Security::getComponent	($data));

	}

	public function __toString() {

		// conversion to pure html string
		return (string) self::getProfiler();

	}


}

?>
