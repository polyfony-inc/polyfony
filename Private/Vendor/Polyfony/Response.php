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

class Response {

	// set manually
	protected static $_content;			// raw content before internal formatting
	protected static $_meta;			// list of meta tags
	protected static $_assets;			// list of assets
	protected static $_type;			// type of the output (html/json/…)
	protected static $_headers;			// list of headers
	protected static $_status;			// the HTTP status code to use
	protected static $_redirect;		// url to redirect to
	protected static $_delay;			// delay before redirection
	protected static $_charset;			// charset of the response
	protected static $_modification;	// modification date of the content
	protected static $_browserCache;	// allow browser to cache the response
	protected static $_outputCache;		// allow the framework to cache the response
	
	// computed by the class itself
	protected static $_formatted;		// content after formatting
	protected static $_length;			// length of the content
	protected static $_checksum;		// checksum of the content

	// init the response
	public static function init() {
	
		// start the output buffer
		ob_start();
		
		// set the default status
		self::setStatus(200);
		
		// set the default type
		self::setType(Config::get('response','default_type'));
		
	}

	public static function setAssets($type,$assets) {
		
		// if single element provided
		$assets = is_array($assets) ? $assets : array($assets);
		// for each assets to set
		foreach($assets as $asset) {
			// if asset is absolute
			$asset = (substr($asset,0,1) == '/' or substr($asset,0,4) == 'http') ? $asset : "/assets/{$type}/{$asset}";
			// push in the list
			self::$_assets[$type] = $asset;
		}
		
	}

	public static function setMetas($metas) {
		
		// if single element provided
		$metas = is_array($metas) ? $metas : array($metas);
		// for each meta to set
		foreach($metas as $meta => $value) {
			// push meta
			self::$_meta[$meta] = $value;
		}
		
	}

	public static function setHeaders($headers) {	
		
		// if single element provided
		$headers = is_array($headers) ? $headers : array($headers);
		
	}

	public static function setType($type) {
	}

	public static function setContent($content) {
	}

	public static function formatContent() {
		
		// do nothing for now
		
	}

	public static function outputContent() {
	
		// echo as is for now
		echo self::$_content;
		
	}
	
	// register a status header for that response
	public static function setStatus($code) {
	
		// declare all the status
		$status = array(
			// 100 status range
			'100'=>'Continue',
			'101'=>'Switching Protocols',
			'102'=>'Processing',
			
			// 200 status range
			'200'=>'OK',
			'201'=>'Created',
			'202'=>'Accepted',
			'203'=>'Non-Authoritative Information',
			'204'=>'No Content',
			'205'=>'Reset Content',
			'206'=>'Partial Content',
			'207'=>'Multi-Status',
			'208'=>'Already Reported',
			'226'=>'IM Used',
			
			// 300 status range
			'300'=>'Multiple Choices',
			'301'=>'Moved Permanently',
			'302'=>'Found',
			'303'=>'See Other',
			'304'=>'Not Modified',
			'305'=>'Use Proxy',
			'306'=>'Switch Proxy',
			'307'=>'Temporary Redirect',
			'308'=>'Permanent Redirect',
			
			// 400 status range
			'400'=>'Bad Request',
			'401'=>'Unauthorized',
			'402'=>'Payment Required',
			'403'=>'Forbidden',
			'404'=>'Not Found',
			'405'=>'Method Not Allowed',
			'406'=>'Not Acceptable',
			'407'=>'Proxy Authentication Required',
			'408'=>'Request Timeout',
			'409'=>'Conflict',
			'410'=>'Gone',
			'411'=>'Length Required',
			'412'=>'Precondition Failed',
			'413'=>'Request Entity Too Large',
			'414'=>'Request-URI Too Long',
			'415'=>'Unsupported Media Type',
			'416'=>'Requested Range Not Satisfiable',
			'417'=>'Expectation Failed',
			'418'=>'I\'m a teapot',
			
			// 500 status range
			'500'=>'Internal Server Error',
			'501'=>'Not Implemented',
			'502'=>'Bad Gateway',
			'503'=>'Service Unavailable',
			'504'=>'Gateway Timeout',
			'505'=>'HTTP Version Not Supported',
			'506'=>'Variant Also Negotiates',
			'507'=>'Insufficient Storage',
			'508'=>'Loop Detected',
			'509'=>'Bandwidth Limit Exceeded',
		);
		
		// if the status does not exist
		if(!array_key_exists($code,$status)) {
			// don't go further if the code is not supported
			return;	
		}
		// get the current protocol
		$protocol = Request::server('SERVER_PROTOCOL','HTTP/1.1');
		// set the actual status
		self::$_status = "{$protocol} {$status[$code]}";
		
	}

	public static function render() {

		// if no content is set yet we garbage collect
		self::$_content = self::$_content ?: ob_get_clean();

		// output status
		header(self::$_status);
			
		// format the content
		self::formatContent();	
		
		// output the content
		self::outputContent();
		
		// it ends here
		exit;
		
	}

}

?>