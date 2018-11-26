<?php

 //  ___                           _          _ 
 // |   \ ___ _ __ _ _ ___ __ __ _| |_ ___ __| |
 // | |) / -_) '_ \ '_/ -_) _/ _` |  _/ -_) _` |
 // |___/\___| .__/_| \___\__\__,_|\__\___\__,_|
 //          |_|                                

namespace Polyfony;

class HttpRequest {

	// curl instance
	private $curl;

	// request specific variables
	private $url;
	private $method;
	private $timeout;
	private $retry;
	private $data;
	private $cookies;
	private $response;
	private $throwException;
	
	// the constructor
	public function __construct(
		string 	$url 				= null, 
		string 	$method 			= 'GET', 
		int 	$timeout 			= 60, 
		int 	$removed_feature 	= 0,
		bool 	$throwException 	= false
	) {

		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\HttpRequest is deprecated, require php-curl-class/php-curl-class instead', 
			E_USER_DEPRECATED
		);

		// request initialization
		$this->url 				= $url;
		$this->method 			= $method;
		$this->timeout 			= $timeout;
		$this->data 			= [];
		$this->cookies 			= [];

		// response initialization
		$this->response 		= [
			'success'	=> false,
			'headers'	=> [],
			'content'	=> null
		];

		// new curl instance
		$this->curl 			= curl_init();

	}

	public function throwException(
		bool $do_we_throw_exception = true
	) :self {
		// set directly
		$this->throwException = $do_we_throw_exception;
		// return self
		return $this;
	}

	// set the destination url
	public function url($url) :self {
		// set directly the url
		$this->url = $url;
		// return self
		return $this;
	}

	// set a get/post parameter
	public function data($key, $value=null) :self {
		// if we have an array of data
		if(is_array($key)) {
			// for each of those
			foreach($key as $index => $value) {
				// set them
				$this->data($index, $value);
			}
		}
		else {
			// set a value
			$this->data[$key] = $value;
		}
		// return self
		return $this;
	}

	// set a file to be posted
	public function file($key, $path=null) :self {
		// if we have an array of data
		if(is_array($key)) {
			// for each of those
			foreach($key as $index => $path) {
				// set them
				$this->file($index, $path);
			}
		}
		else {
			// add to the list of files
			$this->data[$key] = '@'.$path;
		}
		// return self
		return $this;
	}

	// set a cookie
	public function cookie($key, $value) :self {
		// if we have an array of data
		if(is_array($key)) {
			// for each of those
			foreach($key as $index => $value) {
				// set them
				$this->cookie($index, $value);
			}
		}
		else {
			// add to the list of files
			$this->cookie[$key] = '@'.$value;
		}
		// return self
		return $this;
	}

	// set the method for this request
	public function method(string $method) :self {
		// set the method with case conversion
		$this->method = strtoupper($method);
		// return self
		return $this;
	}

	// set the timeout
	public function timeout(int $seconds) :self {
		// set the timeout
		$this->timeout = $seconds;
		// return self
		return $this;
	}

	// get shortcut
	public function get() :bool {
		// set the method
		$this->method('GET');
		// send the request
		return $this->send();
	}

	// post shortcut
	public function post() :bool {
		// set the method
		$this->method('POST');
		// send the request
		return $this->send();
	}

	private function initializeCurl() :void {
		// if the method is post
		if($this->method == 'POST') {
			// set the post data
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($this->data));
			// set the method as being post
			curl_setopt($this->curl, CURLOPT_POST, 1);
		}
		elseif($this->method == 'GET') {
			// if we have get parameters
			if($this->data) {
				// declare the get encoded string
				$get = '?';
				// for each data to post
				foreach($this->data as $key => $value) {
					// build the string
					$get .= urlencode($key) . '=' . urlencode($value) . '&';
				}
				// alter the url
				$this->url .= trim($get,'&');
			}
		}
		// if some cookies exist
		if($this->cookies) {
			// cookie string
			$cookies = '';
			// for each cookie
			foreach($this->cookies as $key => $value) {
				// append the cookie
				$cookies .= urlencode($key) . '=' . urlencode($value) . ';';
			}
			// set the cookies
			curl_setopt($this->curl, CURLOPT_COOKIE, trim($cookies,';'));
		}
	}

	private function configureCurl() :void {
		// bugfix for expectation failed errors
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, 		['Expect:']);
		// impersonate the current user agent
		curl_setopt($this->curl, CURLOPT_USERAGENT, 		Request::server('HTTP_USER_AGENT'));
		// set the url
		curl_setopt($this->curl, CURLOPT_URL, 				$this->url);
		// set the timeout for this request
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 	$this->timeout);
		// get the response as a string
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 	true); 
		// allow curl to follow redirects
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 	true);
		// soften SSL rules
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 	false); 
		// soften SSL rules
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 	false);  
		// require the response headers
		curl_setopt($this->curl, CURLOPT_HEADER, 			true);
		// limit the number of redirections
		curl_setopt($this->curl, CURLOPT_MAXREDIRS, 		5);
	}

	private function executeCurl() {
		// set a marker id
		$marker_id = 'HttpRequest.'.uniqid();
		// place the marker
		Profiler::setMarker($marker_id, 'user');
		// send the actual http request and put the content at its rightful place
		$response = curl_exec($this->curl); 
		// set the response status
		$this->response['success'] = $response ? true : false;
		// release the marker
		Profiler::releaseMarker($marker_id);
		// return the response
		return $response;
	}

	private function parseCurl($response) :bool {

		// if request succeeded
		if($this->response['success']) {
			// get the size of the headers
			$header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
			// get header portion from the response
			$this->response['headers'] = substr($response, 0, $header_size);
			// get body portion from the response
			$this->response['content'] = substr($response, $header_size);
			// success now depends on more than just getting a response, we need a 200 OK
			$this->response['success'] = stripos($this->response['headers'],'200 OK') !== false ? 
				true : false;
			// format the headers properly
			$this->response['headers'] = explode("\n",$this->response['headers']);
			// for each header
			foreach($this->response['headers'] as $index => $mixed)	{
				// if we find a semicolon
				if(stripos($mixed, ':') !== false) {
					// explode the header
					list($key, $value) = explode(':', $mixed);
					// set the header
					$this->response['headers'][strtolower($key)] = trim($value);
					// remove the original
					unset($this->response['headers'][$index]);
				}
			}
			// if the response was compressed
			if($this->getHeader('content-encoding') == 'gzip') {
				// decode gzip
				$this->response['content'] = gzdecode($this->response['content']);
			}
		}
		// exception handling base on status code this time
		if(!$this->response['success']) {
			// if we are allowed to throw exception
			if($this->throwException) {
				// throw an exception describing the issue
				Throw new Exception(
				//	'The remote request failed ('.$this->getHeader(0).')', 
					'The HttpRequest to '.$this->url.' #'.
					Keys::generate($this).' failed ('.$this->getHeader(0).')', 
					502
				);
			}
			// we are not allowed to throw exception, simply log
			else {
				Logger::warning(
					'The HttpRequest to '.$this->url.' #'.
					Keys::generate($this).' failed ('.$this->getHeader(0).')', 
					$this->getBody()
				);
			}
			
		}
		// return the request status as boolean
		return $this->response['success'];

	}

	// build and send the request
	public function send() :bool {

		// initialize
		$this->initializeCurl();
		$this->configureCurl();
		
		// run & parse
		return $this->parseCurl($this->executeCurl());
		
	}

	public function getBody(bool $raw = false) {
		// if we got json
		if(stripos($this->getHeader('content-type'),'application/json') === 0 && !$raw) {
			// decode and return 
			return json_decode($this->response['content'], true);
		}
		// if we got xml
		elseif(stripos($this->getHeader('content-type'),'application/xml') === 0 && !$raw) {
			// decode and return 
			return new \SimpleXMLElement($this->response['content']);
		}
		// the content does not require transformation
		else {
			// return as is
			return $this->response['content'];
		}
	}

	// get a specific response header
	public function getHeader(string $key) {
		// return the header of null if missing
		return isset($this->response['headers'][$key]) ? 
			$this->response['headers'][$key] : null;
	}

	// get directly the type of the response
	public function getType() :?string {
		// return the content type of the reponse or null
		return $this->getHeader('Content-Type') ?: null;
	}

	// get the status of the response
	public function getStatus() :bool {
		// return the status of the request as a boolean
		return $this->response['success'];
	}

}

?>
