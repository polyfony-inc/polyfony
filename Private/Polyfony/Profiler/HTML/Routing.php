<?php

namespace Polyfony\Profiler\HTML;
use Polyfony\Element as Element;

class Routing {

	public static function getHeader(
		\Bootstrap\Dropdown $routing_dropdown
	) :\Bootstrap\Dropdown {

		return $routing_dropdown
			->addItem([
				'html'=>'<strong>SSL/TLS</strong> '.(\Polyfony\Request::isSecure() ? 
				new Element('span',['class'=>'badge badge-success','text'=>'Yes']) :
				new Element('span',['class'=>'badge badge-warning','text'=>'No']) 
				)
			])
			->addItem([
				'html'=>'<strong>Method</strong> '.	(new Element('span',[
					'class'=>'badge badge-primary',
					'text'=>strtoupper(\Polyfony\Request::getMethod())
				]))
			])
			->addItem([
				'html'=>'<strong>Environment</strong> '.(new Element('span',[
					'class'=>'badge badge-primary',
					'text'=>\Polyfony\Config::isProd() ? 'Prod' : 'Dev'
				]))
			])
			->addItem([
				'html'=>'<strong>PHP Version</strong> '. (new Element('code',[
					'class'=>'',
					'text'=>phpversion()
				]))
			]);


	}

	public static function getBody(
		\Bootstrap\Dropdown $routing_dropdown
	) :\Bootstrap\Dropdown {

		$routing_dropdown->addDivider();
		foreach(\Polyfony\Request::getUrlParameters() as $parameter => $value) {
			$routing_dropdown->addItem([
				'html'=>new Element('code',[
					'html'=>
						(new Element('strong', 	['text'=>$parameter.':'])) . 
						(new Element('span', 		['text'=>$value]))
				])
			]);
		}
		$routing_dropdown->addDivider();
		foreach(\Polyfony\Profiler::IMPORTANT_PHP_INI as $parameter) {
			$routing_dropdown->addItem([
				'html'=>new Element('code',[
					'html'=>
						(new Element('strong', 	['text'=>$parameter.':'])) . 
						(new Element('span', 		['text'=>ini_get($parameter)]))
				])
			]);
		}

		return $routing_dropdown;

	}

	public static function getFooter(
		\Bootstrap\Dropdown $routing_dropdown, 
		\Polyfony\Route $route
	) :\Bootstrap\Dropdown {

		return $routing_dropdown
			->addDivider()
			->addItem([
				'html'=>'<strong>Route</strong> '.(new Element('code',[
					'text'=>$route->name
				]))
			])
			->addItem([
				'html'=>'<strong>Bundle</strong> '.(new Element('code',[
					'text'=>$route->bundle
				]))
			])
			->addItem([
				'html'=>'<strong>Controller</strong> '.(new Element('code',[
					'text'=>$route->controller
				]))
			])
			->addItem([
				'html'=>'<strong>Action</strong> '.	(new Element('code',[
					'text'=>$route->action
				]))
			]);

	}

	private static function getStatusLabel() :Element {
		return 
			\Polyfony\Response::getStatus() >= 200 && 
			\Polyfony\Response::getStatus() < 300 ? 
				new Element('span',[
					'class'=>'badge badge-success',
					'text'=>\Polyfony\Response::getStatus()
				]) : 
				new Element('span',[
					'class'=>'badge badge-warning',
					'text'=>\Polyfony\Response::getStatus()
				]);
	}

	public static function getComponent() :\Bootstrap\Dropdown {

		// ROUTING
		$route = \Polyfony\Router::getCurrentRoute();

		$full_route = 
			" " . self::getStatusLabel() . 
			" {$route->bundle}/{$route->controller}@<strong>{$route->action}</strong>";
		$routing_dropdown = new \Bootstrap\Dropdown();
		$routing_dropdown
			->setTrigger([
				'html'	=>$full_route,
				'class'	=>'btn btn-light' . (\Polyfony\Config::get('profiler','use_small_buttons') ? ' btn-sm' : ''),
				'style'	=>'margin-left:10px'
			], 'fa fa-code-branch');

		$routing_dropdown = self::getHeader($routing_dropdown);
		$routing_dropdown = self::getBody($routing_dropdown);
		$routing_dropdown = self::getFooter($routing_dropdown, $route);
		
		return $routing_dropdown;

	}

}

?>
