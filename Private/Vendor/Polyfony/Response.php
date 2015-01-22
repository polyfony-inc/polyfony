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

	// list of http status codes and messages
	protected static $_codes = array(
			// 100 status range
			'100'=>'Continue', '101'=>'Switching Protocols', '102'=>'Processing',
			// 200 status range
			'200'=>'OK', '201'=>'Created', '202'=>'Accepted', '203'=>'Non-Authoritative Information', '204'=>'No Content',
			'205'=>'Reset Content', '206'=>'Partial Content', '207'=>'Multi-Status', '208'=>'Already Reported', '226'=>'IM Used',
			// 300 status range
			'300'=>'Multiple Choices', '301'=>'Moved Permanently', '302'=>'Found', '303'=>'See Other', '304'=>'Not Modified',
			'305'=>'Use Proxy', '306'=>'Switch Proxy', '307'=>'Temporary Redirect', '308'=>'Permanent Redirect',
			// 400 status range
			'400'=>'Bad Request', '401'=>'Unauthorized', '402'=>'Payment Required', '403'=>'Forbidden', '404'=>'Not Found',
			'405'=>'Method Not Allowed', '406'=>'Not Acceptable', '407'=>'Proxy Authentication Required',
			'408'=>'Request Timeout', '409'=>'Conflict', '410'=>'Gone', '411'=>'Length Required', '412'=>'Precondition Failed',
			'413'=>'Request Entity Too Large', '414'=>'Request-URI Too Long', '415'=>'Unsupported Media Type',
			'416'=>'Requested Range Not Satisfiable', '417'=>'Expectation Failed', '418'=>'I\'m a teapot',
			// 500 status range
			'500'=>'Internal Server Error', '501'=>'Not Implemented', '502'=>'Bad Gateway', '503'=>'Service Unavailable',
			'504'=>'Gateway Timeout', '505'=>'HTTP Version Not Supported', '506'=>'Variant Also Negotiates', 
			'507'=>'Insufficient Storage', '508'=>'Loop Detected', '509'=>'Bandwidth Limit Exceeded',
		);

	// init the response
	public static function init() {
		
		// check if we can render a response from the cache
		self::isCached() == false ?: self::renderFromCache();
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
		// default is to disable the output cache (0 hour of cache)
		self::$_outputCache = 0;
		// set the default status as ok
		self::setStatus(200);
		// set default language
		self::setHeaders(array(
			'X-Powered-By'		=> 'Polyfony',
			'Server'			=> 'Undisclosed',
			'Content-Language'	=> Locales::getLanguage()
		));
		// set default charset
		self::setCharset(Config::get('response', 'default_charset'));
		// set the default type
		self::setType(Config::get('response', 'default_type'));
		
	}

	private static function isCached() {
		// the cache has this request signature in store, cache is enabled, and browser allows cache
		return(
			Config::get('response', 'cache') && Cache::has(Request::getSignature()) && 
			Request::header('Cache-Control') != 'max-age=0' ? true : false
		);
	}
	
	private static function isCachable() {
		// response is cachable, cache time is set, status is 200, type is not file,  method is get
		return(
			Config::get('response', 'cache') && self::$_status == '200' && 
			self::$_outputCache && self::$_type != 'file' && !Request::isPost()
		);
	}

	public static function disableBrowserCache() {
		// disable the browser's cache
		self::$_browserCache = false;
	}

	public static function enableOutputCache($hours = 24) {
		// enable the generated output to be cached for some time
		self::$_outputCache = round($hours * 3600);
	}
	
	public static function setCharset($charset) {
		// set the charset in meta tags and http headers
		self::$_charset = $charset;
	}
	
	public static function setRedirect($url, $delay=0) {
		// set the redirect header
		self::setHeaders(array(
			'Refresh' => "{$delay};url=$url"
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
	
		// set the actual status or 500 if incorrect
		self::$_status = in_array($code, array_keys(self::$_codes)) ? $code : 500;
		
	}

	// set raw content
	public static function setContent($content, $replace=false) {
		
		// if the content is an array
		if(is_array($content)) {
			// replace direclty without consideration for the replace parameter
			self::$_content = $content;
		}
		// content is a string type
		else {
			// replace content or append to already existing
			self::$_content = $replace ? $content : self::$_content . $content;
		}

	}

	public static function getType() {
		// the current output type
		return(self::$_type);
	}

	public static function getCharset() {
		// the current charset
		return(self::$_charset);
	}

	// format an return metas
	private static function prependMetas() {
		// de-deuplicate js files
		self::$_metas = array_unique(self::$_metas);
		// for each file
		foreach(self::$_metas as $meta => $value) {
			// add it
			self::$_content = '<meta name="'.$meta.'" content="' . Format::htmlSafe($value) . '" />' . self::$_content;
			// if the meta is a title, it's a bit special
			self::$_content = $meta == 'title' ? '<title>' . Format::htmlSafe($value) . '</title>' . self::$_content : self::$_content;
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
		
		// if response type is file, get the content of the file from the path, else return the normal content
		return(self::$_type == 'file' ? file_get_contents(self::$_content) : self::$_content);
		
	}

	private static function renderFromCache() {

		// get the body and headers from the cache
		list($headers, $body) = Cache::get(Request::getSignature());
		// stop the profiler
		Profiler::stop();
		// get the profiler data
		$profiler = Profiler::getData();
		// if the profiler is enabled
		if(Config::get('profiler', 'enable')) {
			// memory usage	
			$headers['X-Memory-Usage'] = Format::size($profiler['memory']);
			// execution time
			$headers['X-Execution-Time'] = round($profiler['time'] * 1000) . ' ms';
		}
		// tell that we are from the cache
		$headers['X-Polyfony-Cache'] = 'hit';
		// for each header associated with the cached request
		foreach($headers as $header => $value) {
			// output that header
			header("{$header}: {$value}");
		}
		// output the content and stop here
		die(base64_decode($body));

	}
	
	private static function formatContent() {
		
		// base headers
		$headers = array();
		
		// in case we are outputing anything but html
		if(!in_array(self::$_type, array('html', 'html-page'))) {
			// remove any already buffered data
			self::clean();
		}

		// case of html page
		if(self::$_type == 'html-page') {
			// add the profiler
			self::$_content .= Config::get('profiler', 'enable_stack', true) ? Profiler::getHtml() : '';
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
			<meta http-equiv="content-type" content="text/html; charset=' . self::$_charset . '" />' . self::$_content;
		}
		// elseif the type is json
		elseif(self::$_type == 'json') {
			// add the profiler if required
			self::$_content = array_merge(self::$_content, Config::get('profiler', 'enable_stack', true) ? Profiler::getArray() : array());
			// encode the content to json
			self::$_content = json_encode(self::$_content);
		}
		// elseif the type is plain text
		elseif(self::$_type == 'text') {
			// if the content is of type array, var_export it
			self::$_content = is_array(self::$_content) ? var_export(self::$_content, true) : self::$_content;
		}
		// elseif the type is file
		elseif(self::$_type == 'file') {
			// detect the mimetype to set the proper header
			$headers['Content-Type'] = Filesystem::type(self::$_content);
			// detect the modification time of the file
			self::$_modification = filemtime(self::$_content);
		}

		// if the type is not a file
		if(self::$_type != 'file') {
			// obfuscate
			self::$_content = Config::get('response', 'obfuscate') ? Format::obfuscate(self::$_content) : self::$_content;
			// if compression is allowed
			if(Config::get('response', 'compress')) {
				// compress
				self::$_content = gzencode(self::$_content);
				// add header
				$headers['Content-Encoding'] = 'gzip';
			}
		}

		// if checksum is enabled
		if(Config::get('response', 'checksum')) {
			// generate that checksum
			$headers['Content-MD5'] = self::$_type == 'file' ? md5_file(self::$_content) : md5(self::$_content);
		}
		// the content length (after any compression or obfuscation occured)
		$headers['Content-Length'] = self::$_type == 'file' ? filesize(self::$_content) : strlen(self::$_content);
		// if we have a modification date
		if(self::$_modification) {
			// output the proper modification date
			$headers['Last-Modified'] = date('r', self::$_modification);
		}
		// if cache is disabled or we are outputing an error
		if(!self::$_browserCache || self::$_status != 200) {
			// output specific headers to disable browser cache
			$headers['Cache-Control'] = 'must-revalidate, post-check=0, pre-check=0';	
		}
		// if the profiler is enabled
		if(Config::get('profiler','enable')) {
			// get the profiler data
			$profiler = Profiler::getData();
			// memory usage	
			$headers['X-Memory-Usage'] 		= Format::size($profiler['memory']);
			// execution time
			$headers['X-Execution-Time'] 	= round($profiler['time'] * 1000) . ' ms';
		}
		// if the request is cachable
		if(self::isCachable()) {
			// add the caching time
			$headers['Date'] 	= date('r');
			// add the caching until (so that the browser too can cache)
			$headers['Expires'] = date('r', time() + self::$_outputCache);
			// tell that we are not from the cache
			$headers['X-Polyfony-Cache'] = 'miss';
		}
		// set some headers
		self::setHeaders($headers);
		
	}	

	public static function clean() {
		// clean the reponse
		return(ob_get_clean());
	}

	public static function render() {

		// if no content is set yet we garbage collect
		self::$_content = self::$_content ?: self::clean();
		// set the current protocol of fallback to HTTP 1.1 and set the status code plus message
		header(Request::server('SERVER_PROTOCOL', 'HTTP/1.1') . ' ' . self::$_status . ' ' . self::$_codes[self::$_status]);
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
		echo self::getContent();
		// if cache is enabled and page is cachable
		self::isCachable() == false ?: self::cache(); 
		// it ends here
		exit;
		
	}
	
	private static function cache() {
		// store the content and the header of this response
		Cache::put(
			// with the key being a signature of that request
			Request::getSignature(), 
			array(
				self::$_headers,
				base64_encode(self::$_content)
			), 
			// replace any already existing cache file
			true, 
			// set the cache for some time
			self::$_outputCache
		);
	}
	
	public static function download($file_name, $force=false) {
		// set download headers
		self::setHeaders(array(
			'Content-Type'			=> $force ? 'application/octet-stream' : self::$_headers['Content-Type'],
			'Content-Description'	=>'File Transfer',
			'Content-Disposition'	=>'attachment; filename="' . Format::fsSafe($file_name) . '"'
		));
		// render
		self::render();
	}

}

?>
