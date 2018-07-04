<?php

namespace Polyfony\Profiler\HTML;

class Routing {

	public static function getHeader(\Bootstrap\Dropdown $routing_dropdown) :\Bootstrap\Dropdown {

		$routing_dropdown->addItem([
			'html'=>'<strong>Status</strong> '.(\Polyfony\Response::getStatus() >= 200 && \Polyfony\Response::getStatus() < 300 ? 
			new \Polyfony\Element('span',['class'=>'badge badge-success','text'=>\Polyfony\Response::getStatus()]) : 
			new \Polyfony\Element('span',['class'=>'badge badge-warning','text'=>\Polyfony\Response::getStatus()])
			)
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>SSL/TLS</strong> '.(\Polyfony\Request::isSecure() ? 
			new \Polyfony\Element('span',['class'=>'badge badge-success','text'=>'Yes']) :
			new \Polyfony\Element('span',['class'=>'badge badge-warning','text'=>'No']) 
			)
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>Method</strong> '.			(new \Polyfony\Element('span',['class'=>'badge badge-primary','text'=>strtoupper(\Polyfony\Request::getMethod())]))
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>Environment</strong> '.	(new \Polyfony\Element('span',['class'=>'badge badge-primary','text'=>\Polyfony\Config::isProd() ? 'Prod' : 'Dev']))
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>PHP Version</strong> '.	(new \Polyfony\Element('code',['class'=>'','text'=>phpversion()]))
		]);

		return $routing_dropdown;

	}

	public static function getBody(\Bootstrap\Dropdown $routing_dropdown) :\Bootstrap\Dropdown {

		$routing_dropdown->addDivider();
		foreach(\Polyfony\Request::getUrlParameters() as $parameter => $value) {
			$routing_dropdown->addItem([
				'html'=>new \Polyfony\Element('code',[
					'html'=>
						(new \Polyfony\Element('strong', 	['text'=>$parameter.':'])) . 
						(new \Polyfony\Element('span', 		['text'=>$value]))
				])
			]);
		}
		$routing_dropdown->addDivider();
		foreach(\Polyfony\Profiler::IMPORTANT_PHP_INI as $parameter) {
			$routing_dropdown->addItem([
				'html'=>new \Polyfony\Element('code',[
					'html'=>
						(new \Polyfony\Element('strong', 	['text'=>$parameter.':'])) . 
						(new \Polyfony\Element('span', 		['text'=>ini_get($parameter)]))
				])
			]);
		}

		return $routing_dropdown;

	}

	public static function getFooter(\Bootstrap\Dropdown $routing_dropdown, \Polyfony\Route $route) :\Bootstrap\Dropdown {

		$routing_dropdown->addDivider();
		$routing_dropdown->addItem([
			'html'=>'<strong>Route</strong> '.		(new \Polyfony\Element('code',['text'=>$route->name]))
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>Bundle</strong> '.		(new \Polyfony\Element('code',['text'=>$route->bundle]))
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>Controller</strong> '.	(new \Polyfony\Element('code',['text'=>$route->controller]))
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>Action</strong> '.		(new \Polyfony\Element('code',['text'=>$route->action]))
		]);

		return $routing_dropdown;

	}

	public static function getComponent() :\Bootstrap\Dropdown {

		// ROUTING
		$route = \Polyfony\Router::getCurrentRoute();
		$full_route = " {$route->bundle}/{$route->controller}@<strong>{$route->action}</strong>";
		$routing_dropdown = new \Bootstrap\Dropdown();
		$routing_dropdown
			->setTrigger([
				'html'	=>$full_route,
				'class'	=>'btn btn-light',
				'style'	=>'margin-left:10px'
			], 'fa fa-code-branch');

		$routing_dropdown = self::getHeader($routing_dropdown);
		$routing_dropdown = self::getBody($routing_dropdown);
		$routing_dropdown = self::getFooter($routing_dropdown, $route);
		
		return $routing_dropdown;

	}

}

?>
