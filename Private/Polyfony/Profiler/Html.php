<?php

namespace Polyfony\Profiler;

// generate an HTML Profiler that will be fixed to the bottom of a page
class Html {

	public function __construct() {

	}

	public function __toString() {

		// get profiler raw data
		$data = \Polyfony\Profiler::getData();

		$profiler = new \Polyfony\Element('div', [
			'id'=>'pfProfiler',
			'style'=>'position:absolute;bottom:0;left:0;background#c3c3c3;padding-left:10px;padding-bottom:10px;'
		]);
		$timing_modal = new \Bootstrap\Modal('xxl');
		$timing_body = [];
		// for each stacked element
		foreach($data['stack'] as $elem) {
			// a color class for that type of element
			$class = \Polyfony\Profiler::USERS_CLASSES[$elem['user']];
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

			// the actual bar/stack element
			$timing_body[] = new \Polyfony\Element('div', [
				'style'	=>"min-height:6px;height:{$height}px;width:{$width}%;min-width:6px;margin-left:{$relative_start_percent}%;margin-bottom:1px;border-radius:4px;", 
				'class'	=>"bg-{$class}"
			]);
			// then label/details for that bar/stack element
			$timing_body[] = new \Polyfony\Element('div', [
				'style'	=>"font-size:11px;margin-left:{$relative_start_percent}%;margin-bottom:1px;", 
				'class'	=>"text-{$class}",
				'html'	=> 
					(new \Polyfony\Element('strong', ['text'=>$elem['name']])) . ' ' .
					(new \Polyfony\Element('i', ['text'=>"{$readable_duration} {$readable_memory}"]))
			]);
		}

		// the general legend for the waterfall graphics
		$legend=[];
		$legend[] = new \Polyfony\Element('span',['class'=>'badge badge-secondary','text'=>'Framework']);
		$legend[] = new \Polyfony\Element('span',['class'=>'badge badge-primary','text'=>'Controllers']);
		$legend[] = new \Polyfony\Element('span',['class'=>'badge badge-success','text'=>'Views']);
		$legend[] = new \Polyfony\Element('span',['class'=>'badge badge-danger','text'=>'Database']);
		$legend[] = new \Polyfony\Element('span',['class'=>'badge badge-warning','text'=>'Emails']);
		$legend[] = new \Polyfony\Element('span',['class'=>'badge badge-info','text'=>'User defined']);

		$timing_modal
			->setTrigger([
				'html'	=>' Execution time '.
				(new \Polyfony\Element('span',[
					'class'=>'badge badge-light',
					'text'=>round($data['time'], 3) . ' sec'
				])),
				'class'	=>'btn btn-primary'
			], 'fa fa-stopwatch')
			->setTitle([
				'html'=>' &nbsp;Execution stack'
			], 'fa fa-stopwatch')
			->setBody([
				'style'=>'background:url(data:image/gif;base64,R0lGODlhGwABAIAAAPHx8fHx8SH5BAEKAAEALAAAAAAbAAEAAAIFjI+pCQUAOw==)',
				'html'=>implode(' ',$timing_body)
			])
			->setFooter([
				'html'=>implode(' ',$legend)
			]);


		// MEMORY
		$memory_dropdown = new \Bootstrap\Dropdown();
		$memory_dropdown
			->setTrigger([
				'html'	=>' Memory '.
				(new \Polyfony\Element('span',[
					'class'=>'badge badge-light',
					'text'=>\Polyfony\Format::size($data['memory'])
				])),
				'class'	=>'btn btn-light',
				'style'	=>'margin-left:10px'
			], 'fa fa-microchip');

		$peak = new \Polyfony\Element('span',['text'=>\Polyfony\Format::size(memory_get_peak_usage()),'class'=>'badge badge-secondary']);
		$memory_dropdown->addItem([
			'html'=>"<strong>Peak</strong> {$peak}"
		]);
		$total = new \Polyfony\Element('span',['text'=>\Polyfony\Format::size(memory_get_usage()),'class'=>'badge badge-secondary']);
		$memory_dropdown->addItem([
			'html'=>"<strong>Current</strong> {$total}"
		]);

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

		// SECURITY
		$security_dropdown = new \Bootstrap\Dropdown();
		$security_dropdown
			->setTrigger([
				'text'	=>' ' . (\Polyfony\Security::get('login') ? \Polyfony\Security::get('login') : 'n/a'),
				'class'	=>'btn btn-light',
				'style'	=>'margin-left:10px'
			], 'fa fa-user-circle');
		$user = new \Polyfony\Element('code', ['text'=>\Polyfony\Security::get('login')]);
		$security_dropdown->addItem([
			'html'=>"<strong>User</strong> {$user}"
		]);
		$level = new \Polyfony\Element('span', ['class'=>'badge badge-primary','text'=>\Polyfony\Security::get('id_level')]);
		$security_dropdown->addItem([
			'html'=>"<strong>Level</strong> {$level}"
		]);
		$modules = [];
		foreach((array) \Polyfony\Security::get('modules_array') as $module) {
			$modules[] = new \Polyfony\Element('span', ['class'=>'badge badge-primary','text'=>$module]);
		}
		$modules = implode(' ', $modules);
		$security_dropdown->addItem([
			'html'=>"<strong>Modules</strong> {$modules}"
		]);
		$security_dropdown->addDivider();
		$loggedin = new \Polyfony\Element('span', ['text'=>\Polyfony\Security::get('last_login_date'),'class'=>'badge badge-secondary']);
		$security_dropdown->addItem([
			'html'=>"<strong>Logged</strong> {$loggedin}"
		]);
		$expiration = new \Polyfony\Element('span', ['text'=>\Polyfony\Security::get('session_expiration_date'),'class'=>'badge badge-secondary']);
		$security_dropdown->addItem([
			'html'=>"<strong>Expires</strong> {$expiration}"
		]);

		// QUERIES
		$queries_count = 0;
		$queries = [];
		foreach($data['stack'] as $element) {

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

		$queries_modal = new \Bootstrap\Modal('large');
		$queries_modal
			->setTrigger([
				'html'	=>' Queries <span class="badge badge-light">'.$queries_count.'</span>',
				'class'	=>'btn btn-danger',
				'style'	=>'margin-left:10px'
			], 'fa fa-database')
			->setTitle(	['html'=>' &nbsp;Queries'], 'fa fa-database')
			->setBody(	['html'=>implode(' ', $queries)]);

		$emails_count = 0;
		$emails = [];
		foreach($data['stack'] as $element) {

			if($element['user'] == 'email') {

				++$emails_count;

				$debug_data = $element['informations']['Email']->getDebugData();
				$recipients = [];

				foreach($debug_data['recipients']['to'] as $recipient_email => $recipient_name) {
					$recipient_container 	= new \Polyfony\Element('span', 	['class'=>'badge badge-dark']);
					$type 					= new \Polyfony\Element('strong', 	['text'=>'To: ']);
					$email 					= new \Polyfony\Element('span', 	['text'=>$recipient_email]);
					$recipients[] 			= $recipient_container
						->adopt($type)
						->adopt($email);
				}
				foreach($debug_data['recipients']['cc'] as $recipient_email => $recipient_name) {
					$recipient_container 	= new \Polyfony\Element('span', 	['class'=>'badge badge-secondary']);
					$type 					= new \Polyfony\Element('strong', 	['text'=>'Cc: ']);
					$email 					= new \Polyfony\Element('span', 	['text'=>$recipient_email]);
					$recipients[] 			= $recipient_container
						->adopt($type)
						->adopt($email);
				}

				$card 					= new \Polyfony\Element('div', 		['style'=>'border-bottom:solid 1px #efefef;padding-bottom:12px;margin-bottom:12px;overflow:hidden;']);
				$title_container 		= new \Polyfony\Element('span', 	['class'=>'text-dark','style'=>'padding-right:20px;']);
				$title_prefix			= new \Polyfony\Element('strong', 	['text'=>"Email #{$emails_count} ",'class'=>'text-danger']);
				$title 					= new \Polyfony\Element('span', 	['text'=>$debug_data['subject']]);
				$parameters_container 	= new \Polyfony\Element('div', 		['style'=>'padding-top:0px']);
				$parameters 			= new \Polyfony\Element('code', 	['class'=>'text-success','text'=>'']);

				$emails[$emails_count] = $card
					->adopt($title_container
						->adopt($title_prefix)
						->adopt($title))
					->adopt($parameters_container
						->adopt(implode(' ', $recipients)));

			}

		}

		$emails_modal = new \Bootstrap\Modal();
		$emails_modal
			->setTrigger([
				'html'	=>' Emails <span class="badge badge-light">'.$emails_count.'</span>',
				'class'	=>'btn btn-warning',
				'style'	=>'margin-left:10px'
			], 'fa fa-envelope')
			->setTitle(	['html'=>' &nbsp;Emails'], 'fa fa-envelope')
			->setBody(	['html'=>implode(' ', $emails)]);
		

		$profiler
			->adopt($timing_modal)
			->adopt($memory_dropdown)
			->adopt($routing_dropdown)
			->adopt($queries_modal)
			->adopt($emails_modal)
			->adopt($security_dropdown);

		return (string) $profiler;

	}

}

?>
