<?php
/**
 * PHP Version 5
 * Simple HTTP Request abstraction class to ease http handling without the pecl_http (HttpRequest) module
 * It uses CURL which is most commonly installed
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Optional;

class Request {

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
	
	// the constructor
	public function __construct($url='/', $method='GET', $timeout=60, $retry=3) {

		// request initialization
		$this->url 				= $url;
		$this->method 			= $method;
		$this->timeout 			= $timeout;
		$this->retry 			= $retry;
		$this->data 			= array();
		$this->cookies 			= array();

		// response initialization
		$this->response 		= array(
			'success'	=> false,
			'headers'	=> array(),
			'content'	=> null
		);

		// new curl instance
		$this->curl 			= curl_init();

	}

	// set the destination url
	public function url($url) {
		// set directly the url
		$this->url = $url;
		// return self
		return($this);
	}

	// set a get/post parameter
	public function data($key, $value=null) {
		// set a value
		$this->data[$key] = $value;
		// return self
		return($this);
	}

	// set a file to be posted
	public function attachment($key, $path) {
		// add to the list of files
		$this->data['@'.$key] = $path;
		// return self
		return($this);
	}

	// set a cookie
	public function cookie($key, $value) {
		// set directly a header association
		$this->cookies[$key] = $value;
		// return self
		return($this);
	}

	// set the method for this request
	public function method($method) {
		// set the method with case conversion
		$this->method = strtoupper($method);
		// return self
		return($this);
	}

	// set the timeout
	public function timeout($seconds) {
		// set the timeout
		$this->timeout = intval($seconds);
		// return self
		return($this);
	}

	// get shortcut
	public function get() {
		// set the method
		$this->method('GET');
		// send the request
		return($this->send());
	}

	// post shortcut
	public function post() {
		// set the method
		$this->method('POST');
		// send the request
		return($this->send());
	}

	// build and send the request
	public function send() {

		// if the method is post
		if($this->method == 'POST') {
			// set the post data
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->data);
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
		// bugfix for expectation failed errors
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Expect:'));
		// impersonate the current user agent
		curl_setopt($this->curl, CURLOPT_USERAGENT, \Polyfony\Request::server('HTTP_USER_AGENT'));
		// set the url
		curl_setopt($this->curl, CURLOPT_URL, $this->url);
		// set the timeout for this request
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		// get the response as a string
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true); 
		// allow curl to follow redirects
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
		// soften SSL rules
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false); 
		// soften SSL rules
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);  
		// require the response headers
		curl_setopt($this->curl, CURLOPT_HEADER, true);
		// limit the number of redirections
		curl_setopt($this->curl, CURLOPT_MAXREDIRS, 5);
		// send the actual http request and put the content at its rightful place
		$response = curl_exec($this->curl); 
		// set the response status
		$this->response['success'] = $response ? true : false;
		// if the request failed and we still have some retry
		if(!$this->response['success'] && $this->retry > 0) {
			// sleep for a while
			sleep(2);
			// decrement
			$this->retry -= 1;
			// try again
			$this->send();
		}
		// if request succeeded
		if($this->response['success']) {
			// get the size of the headers
			$header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
			// get header portion from the response
			$this->response['headers'] = substr($response, 0, $header_size);
			// get body portion from the response
			$this->response['content'] = substr($response, $header_size);
			// remove the raw response
			unset($response, $header_size);
			// success now depends on more than just getting a response, we need a 200 OK
			$this->response['success'] = stripos($this->response['headers'],'200 OK') !== false ? true : false;
			// format the headers properly
			$this->response['headers'] = explode("\n",$this->response['headers']);
			// for each header
			foreach($this->response['headers'] as $index => $mixed)	{
				// if we find a semicolon
				if(stripos($mixed, ':') !== false) {
					// explode the header
					list($key, $value) = explode(':', $mixed);
					// set the header
					$this->response['headers'][$key] = trim($value);
					// remove the original
					unset($this->response['headers'][$index]);
				}
			}
		}
		// return the request status as boolean
		return($this->response['success']);
	}

	public function getBody($raw = false) {
		// if we want the raw response
		if($raw) {
			// return as is
			return($this->response['content']);
		}
		elseif(stripos($this->getHeader('Content-Type'),'application/json') === 0) {
			// decode and return 
			return(json_decode($this->response['content'], true));
		}
		elseif(stripos($this->getHeader('Content-Type'),'application/xml') === 0) {
			// decode and return 
			return(new \SimpleXMLElement($this->response['content']));
		}
		// the content does not require transformation
		else {
			// return as is
			return($this->getBody(true));
		}
	}

	// get a specific response header
	public function getHeader($key) {
		// return the header of null if missing
		return(isset($this->response['headers'][$key]) ? $this->response['headers'][$key] : null);
	}

	// get directly the type of the response
	public function getType() {
		// return the content type of the reponse or null
		return($this->getHeader('Content-Type') ? $this->getHeader('Content-Type') : null);
	}

	// get the status of the response
	public function getStatus() {
		// return the status of the request as a boolean
		return($this->response['success']);
	}

}

?>
