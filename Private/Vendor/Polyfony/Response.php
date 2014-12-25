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
	protected static $_metas;			// list of meta tags
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
		
		// set default assets
		self::$_assets = array(
			// as empty arrays
			'css'	=>array(),
			'js'	=>array()
		);
		// set default headers
		self::$_headers = array();
		// set default metas
		self::$_metas = array();
		
		// default is to allow browser cache
		self::$_browserCache = true;
		
		// set the default status
		self::setStatus(200);
		
		// set default language
		self::setHeaders(array(
			'X-Powered-By'		=> 'Polyfony',
			'Server'			=> 'Undisclosed',
			'Content-Language'	=> Locales::getLanguage()
		));
		
		// set defautl charset
		self::setCharset(Config::get('response','default_charset'));
		
		// set the default type
		self::setType(Config::get('response','default_type'));
		
	}
	
	public static function disableBrowserCache() {
		
		self::$_browserCache = false;
			
	}
	
	public static function setCharset($charset) {
	
		self::$_charset = $charset;
		
	}
	
	public static function setRedirect($url,$delay=0) {
		
		// set the redirect header
		self::setHeaders(array(
			'Refresh' => "{intval($delay)};url=$url"
		));
			
	}

	public static function setAssets($type,$assets) {
		// if single element provided
		$assets = is_array($assets) ? $assets : array($assets);
		// for each assets to set
		foreach($assets as $asset) {
			// if asset is absolute
			$asset = (substr($asset,0,1) == '/' or substr($asset,0,4) == 'http') ? $asset : "/assets/{$type}/{$asset}";
			// push in the list
			self::$_assets[$type][] = $asset;
		}
	}

	public static function setMetas($metas, $replace=false) {
		// replace or merge with current metas
		self::$_metas = $replace ? self::$_metas : array_merge(self::$_metas,$metas);
	}

	public static function setHeaders($headers) {	
		// if array provided
		if(is_array($headers)) {
			// merge current and new headers (replacing old ones)
			self::$_headers = array_merge(self::$_headers,$headers);
		}
	}

	public static function setType($type) {
		
		// if the type is allowed
		if(in_array($type,array('html-page','json','file','csv','xml','html','js','css','text'))) {
			// update the current type
			self::$_type = $type;
		}
		
		// list of available types
		$types = array(
			'html-page'	=>'text/html',
			'html'		=>'text/html',
			'json'		=>'application/json',
			'file'		=>'application/octet-stream',
			'csv'		=>'text/csv',
			'xml'		=>'text/xml',
			'js'		=>'text/javascript',
			'css'		=>'text/css',
			'text'		=>'text/plain'
		);
		
		// add the header
		self::setHeaders(array(
			'Content-type'=> $types[$type] . '; charset='.self::$_charset
		));
		
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
		self::$_status = "{$protocol} $code {$status[$code]}";
		
	}

	// set raw content
	public static function setContent($content, $replace=false) {
		
		// replace content or append to already existing
		self::$_content = $replace ? $content : self::$_content . $content;

	}

	public static function getType() {
		// the current output type
		return(self::$_type);
	}

	// format an return metas
	private static function prependMetas() {
		// de-deuplicate js files
		self::$_metas = array_unique(self::$_metas);
		// for each file
		foreach(self::$_metas as $meta => $value) {
			// add it
			self::$_content = '<meta name="'.$meta.'" content="' . htmlentities($value) . '" />' . self::$_content;
			// if the meta is a title, it's a bit special
			self::$_content = $meta == 'title' ? '<title>' . htmlentities($value) . '</title>' . self::$_content : self::$_content;
		}
	}

	// format and return javascripts
	private static function prependScripts() {
		// de-deuplicate js files
		self::$_assets['js'] = array_unique(self::$_assets['js']);
		// for each file
		foreach(self::$_assets['js'] as $file) {
			// add it
			self::$_content = '<script type="text/javascript" src="'. $file .'"></script>' . self::$_content;
		}
	}
	
	// format an return stylesheets
	private static function prependStyles() {
		// de-deuplicate css files
		self::$_assets['css'] = array_unique(self::$_assets['css']);
		// for each file
		foreach(self::$_assets['css'] as $file) {
			// support media specific CSS
			$href = is_array($file) ? $file[0] : $file;
			// default is for all medias
			$media = is_array($file) ? $file[1] : 'all';
			// add it
			self::$_content = '<link rel="stylesheet" media="' . $media . '" type="text/css" href="' . $href . '" />' . self::$_content;
		}
	}

	// return current content
	private static function getContent() {
		
		// return as is
		return(self::$_content);
		
	}
	
	private static function formatContent() {
		
		// base headers
		$headers = array();
		
		// case of html page
		if(self::$_type == 'html-page') {
			// wrap in the body
			self::$_content = '</head><body>' . self::$_content . '</body></html>';
			// preprend metas
			self::prependMetas();
			// preprend scripts
			self::prependScripts();
			// preprend css
			self::prependStyles();
			// add metas and style up top
			self::$_content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml"><head>
			<meta http-equiv="content-type" content="text/html; charset='.self::$_charset.'" />' . self::$_content;
		}
		
		// if the type is not a file
		if(self::$_type != 'file') {
			// indent
			self::$_content = Config::get('response','indent') ? Format::indent(self::$_content) : self::$_content;
			// obfuscate
			self::$_content = Config::get('response','obfuscate') ? Format::obfuscate(self::$_content) : self::$_content;
		}
		
		// if the type is not a file and we are allowed to compress
		if(self::$_type != 'file' and Config::get('response','compress')) {
			// compress
			self::$_content = gzencode(self::$_content);
			// add header
			$headers['Content-Encoding'] = 'gzip';
		}

		// if checksum is enabled
		if(Config::get('response','checksum')) {
			// generate that checksum
			$headers['Content-MD5'] = self::$_type == 'file' ? md5_file(self::$_content) : md5(self::$_content);
		}
		// the content length
		$headers['Content-length'] = self::$_type == 'file' ? filesize(self::$_content) : strlen(self::$_content);
		// if we have a modification date
		if(self::$_modification) {
			// output the proper modification date
			$headers['Last-Modified'] = date('r',self::$_modification);
		}
		// if cache is disabled -> specify Cache-control headers
		if(!self::$_browserCache) {
			// output specific headers
			$headers['Cache-Control'] = 'must-revalidate, post-check=0, pre-check=0';	
		}
		// if the profiler is enabled
		if(Config::get('profiler','enable')) {
			// get the profiler data
			$profiler = Profiler::getData();
			// memory usage	
			$headers['X-Memory-Usage'] = Format::size($profiler['memory']);
			// execution time
			$headers['X-Execution-Time'] = round($profiler['time']*1000) . ' ms';
		}
		// set some headers
		self::setHeaders($headers);
		
	}	

	public static function render() {

		// if no content is set yet we garbage collect
		self::$_content = self::$_content ?: ob_get_clean();

		// output status
		header(self::$_status);
		
		// stop the profiler
		Profiler::stop();
		
		// format the content
		self::formatContent();
		
		// for each header
		foreach(self::$_headers as $header_key => $header_value) {
			// output the header
			header("{$header_key}: {$header_value}");		
		}
		
		// if the type is file output from the file indicated as content else just output
		echo self::$_type == 'file' ? file_get_contents(self::$_content) : self::$_content;
		
		// if cache is enabled and page is cachable
		self::cache();
		
		// it ends here
		exit;
		
	}
	
	public static function cache() {
		
		// if status is 200
		
		// save contents
		
		// save headers
		
	}
	
	public static function download($file_name) {
		
	}

}

?>