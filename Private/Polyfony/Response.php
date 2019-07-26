<?php

namespace Polyfony;

class Response {

	// set manually
	protected static $_content;					// raw content before internal formatting
	protected static $_type;					// type of the output (html/json/…)
	protected static $_headers 			= [];	// list of headers
	protected static $_status 			= 200;	// the HTTP status code to use
	protected static $_redirect;				// url to redirect to
	protected static $_delay;					// delay before redirection
	protected static $_charset;					// charset of the response
	protected static $_modification;			// modification date of the content

	// stuff about caching
	protected static $_browserCache 	= true;	// allow browser to cache the response
	protected static $_outputCache 		= 0;	// allow the framework to cache the response
	
	// computed by the class itself
	protected static $_formatted;				// content after formatting
	protected static $_length;					// length of the content
	protected static $_checksum;				// checksum of the content

	// list of http status codes and messages
	const CODES = [
		// 100 status range
		100=>'Continue', 101=>'Switching Protocols', 102=>'Processing',
		// 200 status range
		200=>'OK', 201=>'Created', 202=>'Accepted', 203=>'Non-Authoritative Information', 204=>'No Content',
		205=>'Reset Content', 206=>'Partial Content', 207=>'Multi-Status', 208=>'Already Reported', 226=>'IM Used',
		// 300 status range
		300=>'Multiple Choices', 301=>'Moved Permanently', 302=>'Found', 303=>'See Other', 304=>'Not Modified',
		305=>'Use Proxy', 306=>'Switch Proxy', 307=>'Temporary Redirect', 308=>'Permanent Redirect',
		// 400 status range
		400=>'Bad Request', 401=>'Unauthorized', 402=>'Payment Required', 403=>'Forbidden', 404=>'Not Found',
		405=>'Method Not Allowed', 406=>'Not Acceptable', 407=>'Proxy Authentication Required',
		408=>'Request Timeout', 409=>'Conflict', 410=>'Gone', 411=>'Length Required', 412=>'Precondition Failed',
		413=>'Request Entity Too Large', 414=>'Request-URI Too Long', 415=>'Unsupported Media Type',
		416=>'Requested Range Not Satisfiable', 417=>'Expectation Failed', 418=>'I\'m a teapot', 451=>'Censored',
		// 500 status range
		500=>'Internal Server Error', 501=>'Not Implemented', 502=>'Bad Gateway', 503=>'Service Unavailable',
		504=>'Gateway Timeout', 505=>'HTTP Version Not Supported', 506=>'Variant Also Negotiates', 
		507=>'Insufficient Storage', 508=>'Loop Detected', 509=>'Bandwidth Limit Exceeded',
	];

	// list of response types
	const TYPES = [
		'html-page'	=>'text/html',
		'html'		=>'text/html',
		'json'		=>'application/json',
		'file'		=>'application/octet-stream',
		'csv'		=>'text/csv',
		'xls'		=>'application/vnd.ms-excel',
		'xlsx'		=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xml'		=>'text/xml',
		'js'		=>'text/javascript',
		'css'		=>'text/css',
		'text'		=>'text/plain'
	];

	// init the response
	public static function init() :void {
		
		// marker
		Profiler::setMarker('Response.init', 'framework');
		// check if we can render a response from the cache
		self::isCached() === false ?: self::renderFromCache();
		// start the output buffer (collecting anything that is outputted anywhere, to output it later with our own Response formatting)
		ob_start();
		// set default language
		self::setHeaders([
			// hide the php version
			'X-Powered-By'		=> Config::get('response', 'header_x_powered_by'),
			// hide the web server
			'Server'			=> Config::get('response', 'header_server')
		]);
		// set default charset
		self::setCharset(		Config::get('response', 'default_charset'));
		// set the default type
		self::setType(			Config::get('response', 'default_type'));
		// marker
		Profiler::releaseMarker('Response.init');
		
	}

	// setters shortcut
	public static function set(array $array) :void {

		// simple setters shortcuts
		!isset($array['js']) ?: 		self::setAssets('js', $array['js']);
		!isset($array['css']) ?: 		self::setAssets('css', $array['css']);
		!isset($array['type']) ?: 		self::setType($array['type']);
		!isset($array['metas']) ?: 		self::setMetas($array['metas']);
		!isset($array['status']) ?: 	self::setStatus($array['status']);
		!isset($array['content']) ?: 	self::setContent($array['content']);
		!isset($array['charset']) ?: 	self::setCharset($array['charset']);
		!isset($array['headers']) ?: 	self::setHeaders($array['headers']);
		!isset($array['redirect']) ?: 	self::setRedirect($array['redirect'][0], $array['redirect'][1]);
		
	}

	private static function isCached() :bool {
		return
			// we are allowed to (use) cache(d) responses
			Config::get('response', 'cache') && 
			// the cache has a response for this request's signature
			Cache::has(Request::getSignature()) && 
			// the request has not explicitely asked for non-cached responses
			Request::isCacheAllowed();
	}
	
	private static function isCachable() :bool {
		return 
			// we are allowed to cache responses
			Config::get('response', 'cache') && 
			// the response satus code is a success one
			self::$_status == 200 && 
			// we have been explicitely told to cache that specific response
			self::$_outputCache && 
			// the response is not a file
			self::$_type != 'file' && 
			// the request is a get
			Request::isGet() &&
			// the request is not comming from a command line interface
			!Request::isCli();
	}

	public static function disableBrowserCache() :void {
		// disable the browser's cache
		self::$_browserCache = false;
	}

	public static function enableOutputCache($hours = 24) :void {
		// enable the generated output to be cached BY THE FRAMEWORK for some time
		self::$_outputCache = round($hours * 3600);
	}

	public static function disableOutputCache() :void {
		// disable the caching of generated output
		self::$_outputCache = 0;
	}
	
	public static function setCharset(string $charset) :void {
		// set the charset in meta tags and http headers
		self::$_charset = $charset;
	}
	
	public static function setRedirect(string $url, int $delay=0) :void {
		// if a delay is provided
		$delay ?
			// set the refresh header that support delays
			// BEWARE : it is not at all understood by Google Bot
			self::setHeaders(['Refresh' => "{$delay};url=$url"]) : 	
			// or else, use standard redirect
			self::setHeaders(['Location' => $url]);
	}

	public static function setAssets(string $type, $assets) :void {
		// if single element provided
		$assets = is_array($assets) ? $assets : [$assets];
		// for css assets
		if(strtolower($type) == 'css') {
			// pass to the class that now handles that
			Response\HTML::setLinks($assets);
		}
		// for js assets
		elseif(strtolower($type) == 'js') {
			// pass to the class that now handles that
			Response\HTML::setScripts($assets);
		}
	}

	public static function setMetas(array $metas, $replace=false) :void {
		// pass to the class that now handles that
		Response\HTML::setMetas($metas, $replace);
	}

	public static function setHeaders(array $headers) :void {	
		// merge current and new headers (replacing old ones)
		self::$_headers = array_merge(self::$_headers,$headers);
	}

	public static function setType(string $type) :void {
		
		// remove previously output(ed?) data on change of type
		ob_clean();
		// if the type is allowed we update the current type
		!array_key_exists($type, self::TYPES) ?: self::$_type = $type;
		// add the header
		self::setHeaders([
			'Content-type'=> self::TYPES[$type] . '; charset='.self::$_charset
		]);
		
	}

	// register a status header for that response
	public static function setStatus(int $code) :void {
		// set the actual status or 500 if incorrect
		self::$_status = array_key_exists($code, self::CODES) ? $code : 500;
	}

	// set raw content
	public static function setContent($content) :void {
		//  make sure that the file exists
		if(self::getType() == 'file' && !file_exists($content)) {
			// change back the response type
			self::setType('html');
			// stop the execution
			Throw new Exception(
				"Response::setContent() The file [$content] does not exist", 
				404
			);
		}
		// remove any bufferred output
		self::clean();
		// replace direclty
		self::$_content = $content;
	}

	// set the modification date of the reponse
	public static function setModification(int $timestamp) :void {
		// set as is
		self::$_modification = $timestamp;
	}

	// get the status header
	public static function getStatus() :int {
		// get the currently set status
		return self::$_status;
	}

	public static function getType() :string {
		// the current output type
		return self::$_type;
	}

	public static function getCharset() :string {
		// the current charset
		return self::$_charset;
	}

	// return current content
	private static function getContent() {
		
		// if response type is file, get the content of the file from the path, else return the normal content
		return self::$_type == 'file' ? file_get_contents(self::$_content) : self::$_content;
		
	}

	private static function renderFromCache() :string {

		// get the body and headers from the cache
		list($headers, $body) = Cache::get(Request::getSignature());
		// if the profiler is enabled
		!Config::get('profiler', 'enable_headers') ?: 
			$headers['X-Cache-Footprint'] = Profiler::getFootprint();
		// tell that we are from the cache
		$headers['X-Cache'] = 'hit';
		// for each header associated with the cached request
		foreach($headers as $header => $value) {
			// output that header
			header("{$header}: {$value}");
		}
		// output the content and stop here
		die(base64_decode($body));

	}
	
	private static function formatContent() :void {
		
		// base headers
		$headers = [];
		// case of html page
		if(self::$_type == 'html-page') {
			// build and get an html page
			self::$_content = Response\HTML::buildAndGetPage(self::$_content);
		}
		// elseif the type is json
		elseif(self::$_type == 'json') {
			// add the profiler if required
			self::$_content = Config::get('profiler', 'enable') && is_array(self::$_content) ? 
				array_merge(self::$_content, Profiler::getArray()) : 
				self::$_content;
			// encode the content to json
			self::$_content = json_encode(self::$_content);
		}
		// elseif the type is file
		elseif(self::$_type == 'file') {
			// get a new fileinfo object
			$info = new \finfo(FILEINFO_MIME);
			// get the mimetype
			$vague_type = $info->file(self::$_content);
			// deduce the mimetype of the file
			list($content_type) = strpos($vague_type,';') !== false ? explode(';',$vague_type) : [$vague_type];
			// detect the mimetype to set the proper header
			$headers['Content-Type'] = $content_type;
			// detect the modification time of the file
			self::$_modification = filemtime(self::$_content);
		}
		// elseif the type is a csv (from an array)
		elseif(self::$_type == 'csv') {
			// transform into a proper csv file (we could get the vnd... as returned argument)
			list(
				self::$_content, 
				self::$_charset
			) = Response\CSV::buildAndGetDocument(self::$_content);
		}
		elseif(self::$_type == 'xlsx') {
			// transform into a proper xlsx file
			self::$_content = Response\XLSX::buildAndGetDocument(self::$_content);
		}
		elseif(self::$_type == 'xls') {
			// transform into a proper xls file
			self::$_content = Response\XLS::buildAndGetDocument(self::$_content);
		}
		// in case we are outputing html in any form and obfucation is enabled
		if(Config::get('response', 'minify') && in_array(self::$_type, array('html', 'html-page'))) {
			// minify
			self::$_content = Format::minify(self::$_content);
		}
		// if the type is not a file and compression is allowed
		if(self::$_type != 'file' && Config::get('response', 'compress') && !Request::isCli()) {
			// compress
			self::$_content = gzencode(self::$_content);
			// add header
			$headers['Content-Encoding'] = 'gzip';
		}
		// if checksum is enabled
		if(Config::get('response', 'checksum')) {
			// generate that checksum
			$headers['Content-MD5'] = self::$_type == 'file' ? md5_file(self::$_content) : md5(self::$_content);
		}
		// the content length (after any compression or obfuscation occured)
		$headers['Content-Length'] 	= self::$_type == 'file' ? filesize(self::$_content) : strlen(self::$_content);
		// always show the current environment
		$headers['X-Environment'] 	= Config::isDev() ? 'Dev' : 'Prod';
		// if we have a modification date
		if(self::$_modification) {
			// output the proper modification date
			$headers['Last-Modified'] = date('r', self::$_modification);
		}
		// if browser cache is disabled or we are outputing an error
		if(!self::$_browserCache || self::$_status != 200) {
			// output specific headers to excplicitely disable browser cache
			$headers['Cache-Control'] = 'must-revalidate, post-check=0, pre-check=0';	
		}
		// if the profiler is enabled
		if(Config::get('profiler','enable_headers')) {
			// memory usage	and execution time
			$headers['X-Footprint'] = Profiler::getFootprint();
		}
		// if the request is cachable by the framework
		if(self::isCachable()) {
			// add the caching time
			$headers['Date'] 	= date('r');
			// tell that we are not from the cache yet
			$headers['X-Cache'] = 'miss';
			// if the browser is alowed allowed to cache the response on its side too
			if(self::$_browserCache) {
				// we tell him when the cached file will expire on the server side
				$headers['Expires'] = date('r', time() + self::$_outputCache);
			}
		}
		// set some headers
		self::setHeaders($headers);

	}

	public static function clean() :string {
		// clean the reponse
		return(ob_get_clean());
	}

	public static function render() :void {
		// trigger the beforeRender event
		Events::trigger('beforeRender');
		// if no content is set yet we garbage collect
		self::$_content = self::$_content ?: self::clean();
		// set the current protocol of fallback to HTTP 1.1 and set the status code plus message
		header(Request::server('SERVER_PROTOCOL', 'HTTP/1.1') . ' ' . self::$_status . ' ' . self::CODES[self::$_status]);
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
		self::isCachable() === false ?: self::cache(); 
		// flush the cache to release the output an allow events processing
		fastcgi_finish_request();
		// release any ongoing session so that onTerminate event aren't locking
		session_write_close();
		// trigger the associated event
		Events::trigger('onTerminate');
		// it ends here
		exit;
		
	}
	
	private static function cache() :void {
		// store the content and the header of this response
		Cache::put(
			// with the key being a signature of that request
			Request::getSignature(), 
			[
				self::$_headers,
				base64_encode(self::$_content)
			], 
			// replace any already existing cache file
			true, 
			// set the cache for some time
			self::$_outputCache
		);
	}
	
	public static function download(string $file_name) :void {
		// set download headers
		self::setHeaders([
			'Content-Description'	=>'File Transfer',
			'Content-Disposition'	=>'attachment; filename="' . Format::fsSafe($file_name) . '"'
		]);
		// render
		self::render();
	}

	public static function previous(?int $redirection_after_delay = 0) :void {
		// set the redirection to the previous page
		self::setRedirect(Request::server('HTTP_REFERER'), $redirection_after_delay);
		// prevent further instruction processing
		self::render();
	}

}

?>
