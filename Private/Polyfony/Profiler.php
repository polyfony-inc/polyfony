<?php
/**
 * PHP Version 5
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Profiler {

	// handles gobal execution time
	protected static $_startTime;
	protected static $_startMemory;
	protected static $_endTime;
	protected static $_endMemory;
	protected static $_totalTime;
	protected static $_totalMemory;
	
	// handles different portions of execution times
	protected static $_stack = array();

	const USERS = [
		'fw', 'controller', 'view', 'db', 'mail'
	];

	public static function init() :void {
	
		// start time
		self::$_startTime = microtime(true);

	}
	
	private static function stop() :void {
		
		// for each unreleased marker
		foreach(self::$_stack as $id => $element) {
			if($element['duration'] === null) {
				// manually release it
				self::releaseMarker($element['name']);
			}
		}

		// end time and end memory
		self::$_endTime		= microtime(true);
		self::$_totalTime	= self::$_endTime - self::$_startTime;
		self::$_totalMemory	= memory_get_usage();
		
	}
	
	public static function setMarker($name=null, $user=null, $additional_infos=null) {
		// profiler is disabled
		if (!Config::get('profiler', 'enable')) {
			return false;
		}
		// generate a marker id
		$name = $name ? $name : uniqid();
		// stack the marker
		self::$_stack[$name] = array(
			'name'		=> $name,
			'user'		=> in_array($user, self::USERS) ? $user : 'fw',
			'start'		=> microtime(true),
			'duration'	=> null,
			'memory'	=> memory_get_usage()
		);
		// return the id, for releasing it later
		return $name;
	}

	public static function releaseMarker($id) :void {

		if(Config::get('profiler', 'enable') && array_key_exists($id, self::$_stack)) {
			self::$_stack[$id]['duration'] 	= microtime(true) - self::$_stack[$id]['start'];
			self::$_stack[$id]['memory'] 	= memory_get_usage() - self::$_stack[$id]['memory'];
		}
		

	}
	
	public static function getData() :array {
		// stop the profiler
		self::stop();
		// return stacked data
		return array(
			'time'			=> self::$_totalTime,
			'start_time'	=> self::$_startTime,
			'memory'		=> self::$_totalMemory,
			'stack'			=> self::$_stack
		);
	}

	public static function getArray() :array {
		self::stop();

		// return stacked data
		return array('Profiler' => self::$_stack);
	}

	public static function getHtml() :string {
		$data = self::getData();

		$stack = new Element('div', [
			'id'=>'pfStack',
			'style'=>'position:absolute;bottom:0;left:0;background#c3c3c3;padding-left:10px;padding-bottom:10px;'
		]);
		$timing_modal = new \Bootstrap\Modal('large');

		$timing_body = [];
		foreach(self::$_stack as $elem) {

			if($elem['user'] == 'fw') {
				$class = 'secondary';
			}
			elseif($elem['user'] == 'controller') {
				$class = 'success';
			}
			elseif($elem['user'] == 'view') {
				$class = 'primary';
			}
			elseif($elem['user'] == 'db') {
				$class = 'danger';
			}
			elseif($elem['user'] == 'mail') {
				$class = 'warning';
			}
			else {
				$class = 'secondary';
			}

			// not 100% but 95% (to allow for non overflowing elements on the right)
			$width = round($elem['duration'] * 95 / $data['time'],1);
			if($width < 2.5) {
				$width = 2.5;
			}
			$relative_start = $elem['start'] - $data['start_time'];
			

			$relative_start_percent = round($relative_start * 95 / $data['time'],1);

			$readable_duration = $elem['duration'] ? round($elem['duration'] * 1000, 1) . ' ms' : 'NaN';
			$readable_memory = $elem['memory'] ? Format::size($elem['memory']) : 'Nan';

			$timing_body[] = new Element('div', [
				'style'	=>"padding: 4px; font-size: 8px;width:{$width}%;margin-left:{$relative_start_percent}%;margin-bottom:1px;border-radius:4px;", 
				'class'	=>"bg-{$class}"
			]);
			$timing_body[] = new Element('div', [
				'style'	=>"font-size: 11px;margin-left:{$relative_start_percent}%;margin-bottom:1px;", 
				'class'	=>"text-{$class}",
				'text'	=>"{$elem['name']} ({$readable_duration} {$readable_memory})"
			]);
		}

		$legend=[];
		$legend[] = new Element('span',['class'=>'badge badge-secondary','text'=>'Framework']);
		$legend[] = new Element('span',['class'=>'badge badge-primary','text'=>'Controllers']);
		$legend[] = new Element('span',['class'=>'badge badge-success','text'=>'Views']);
		$legend[] = new Element('span',['class'=>'badge badge-danger','text'=>'Database']);
		$legend[] = new Element('span',['class'=>'badge badge-warning','text'=>'Emails']);
		$legend[] = new Element('span',['class'=>'badge badge-info','text'=>'User defined']);

		$timing_modal
			->setTrigger([
				'html'	=>' Execution time '.
				(new Element('span',[
					'class'=>'badge badge-light',
					'text'=>round($data['time'], 3) . ' sec'
				])),
				'class'	=>'btn btn-primary'
			], 'fa fa-stopwatch')
			->setTitle([
				'text'=>' Execution stack'
			], 'fa fa-stopwatch')
			->setBody([
				'style'=>'background:url(data:image/gif;base64,R0lGODlhGwABAIAAAPHx8fHx8SH5BAEKAAEALAAAAAAbAAEAAAIFjI+pCQUAOw==)',
				'html'=>implode(' ',$timing_body)
			])
			->setFooter([
				'html'=>implode(' ',$legend)
			]);


		$memory_dropdown = new \Bootstrap\Dropdown();
		$memory_dropdown
			->setTrigger([
				'html'	=>' Memory '.
				(new Element('span',[
					'class'=>'badge badge-light',
					'text'=>Format::size($data['memory'])
				])),
				'class'	=>'btn btn-light',
				'style'	=>'margin-left:10px'
			], 'fa fa-microchip');

		$peak = new Element('span',['text'=>Format::size(memory_get_peak_usage()),'class'=>'badge badge-secondary']);
		$memory_dropdown->addItem([
			'html'=>"<strong>Peak</strong> {$peak}"
		]);
		$total = new Element('span',['text'=>Format::size(memory_get_usage()),'class'=>'badge badge-secondary']);
		$memory_dropdown->addItem([
			'html'=>"<strong>Current</strong> {$total}"
		]);

		$route = Router::getCurrentRoute();
		$full_route = " {$route->bundle}/{$route->controller}@<strong>{$route->action}</strong>";

		$routing_dropdown = new \Bootstrap\Dropdown();
		$routing_dropdown
			->setTrigger([
				'html'	=>$full_route,
				'class'	=>'btn btn-light',
				'style'	=>'margin-left:10px'
			], 'fa fa-code-branch');

		$routing_dropdown->addItem([
			'html'=>'<strong>Status</strong> '.(Response::getStatus() >= 200 && Response::getStatus() < 300 ? 
			new Element('span',['class'=>'badge badge-success','text'=>Response::getStatus()]) : 
			new Element('span',['class'=>'badge badge-warning','text'=>Response::getStatus()])
			)
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>SSL/TLS</strong> '.(Request::isSecure() ? 
			new Element('span',['class'=>'badge badge-success','text'=>'Yes']) :
			new Element('span',['class'=>'badge badge-warning','text'=>'No']) 
			)
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>Method</strong> '.(new Element('span',['class'=>'badge badge-primary','text'=>strtoupper(Request::getMethod())]))
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>Environment</strong> '.(new Element('span',['class'=>'badge badge-primary','text'=>Config::isProd() ? 'Prod' : 'Dev']))
		]);

		$routing_dropdown->addDivider();

		foreach(Request::getUrlParameters() as $parameter => $value) {

			$routing_dropdown->addItem([
			'html'=>new Element('code',[
				'html'=>
					(new Element('strong', ['text'=>$parameter.':'])) . 
					(new Element('span', ['text'=>$value]))
			])
		]);

		}

		$routing_dropdown->addDivider();
		$routing_dropdown->addItem([
			'html'=>'<strong>Route</strong> '.(new Element('code',['text'=>$route->name]))
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>Bundle</strong> '.(new Element('code',['text'=>$route->bundle]))
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>Controller</strong> '.(new Element('code',['text'=>$route->controller]))
		]);
		$routing_dropdown->addItem([
			'html'=>'<strong>Action</strong> '.(new Element('code',['text'=>$route->action]))
		]);
		
		


		$security_dropdown = new \Bootstrap\Dropdown();
		$security_dropdown
			->setTrigger([
				'text'	=>' ' . (Security::get('login') ? Security::get('login') : 'Guest'),
				'class'	=>'btn btn-light',
				'style'	=>'margin-left:10px'
			], 'fa fa-user-circle');

		$user = new Element('code', ['text'=>Security::get('login')]);
		$security_dropdown->addItem([
			'html'=>"<strong>User</strong> {$user}"
		]);

		$level = new Element('span', ['class'=>'badge badge-primary','text'=>Security::get('id_level')]);
		$security_dropdown->addItem([
			'html'=>"<strong>Level</strong> {$level}"
		]);

		$modules = [];
		foreach((array) Security::get('modules_array') as $module) {
			$modules[] = new Element('span', ['class'=>'badge badge-primary','text'=>$module]);
		}
		$modules = implode(' ', $modules);
		$security_dropdown->addItem([
			'html'=>"<strong>Modules</strong> {$modules}"
		]);

		$security_dropdown->addDivider();

		$loggedin = new Element('span', ['text'=>Security::get('last_login_date'),'class'=>'badge badge-secondary']);
		$security_dropdown->addItem([
			'html'=>"<strong>Logged</strong> {$loggedin}"
		]);

		$expiration = new Element('span', ['text'=>Security::get('session_expiration_date'),'class'=>'badge badge-secondary']);
		$security_dropdown->addItem([
			'html'=>"<strong>Expires</strong> {$expiration}"
		]);


		$queries_dropdown = new \Bootstrap\Dropdown();
		$queries_dropdown
			->setTrigger([
				'html'	=>' Queries <span class="badge badge-light">1</span>',
				'class'	=>'btn btn-light',
				'style'	=>'margin-left:10px'
			], 'fa fa-database');

		$queries_dropdown->addItem([
			'html'=>'<strong>#1</strong> <code>SELECT * FROM Accounts</code> <span class="badge badge-secondary">4 affected</span>'
		]);

		$stack
			->adopt($timing_modal)
			->adopt($memory_dropdown)
			->adopt($routing_dropdown)
			->adopt($queries_dropdown)
			->adopt($security_dropdown);

		// return the formatted stack
		return((string) $stack);
	}
	
}

?>
