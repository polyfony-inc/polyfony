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
		// start the output buffer
		ob_start();
		// set default assets
		self::$_assets = [
			// as empty arrays
			'Css'	=>[],
			'Js'	=>[]
		];
		// set default headers
		self::$_headers = [];
		// set default metas
		self::$_metas = [];
		// default is to allow browser cache
		self::$_browserCache = true;
		// default is to disable the output cache (0 hour of cache)
		self::$_outputCache = 0;
		// set the default status as ok
		self::setStatus(200);
		// set default language
		self::setHeaders([
			// hide the php version
			'X-Powered-By'		=> Config::get('response', 'header_x_powered_by'),
			// hide the web server
			'Server'			=> Config::get('response', 'header_server')
		]);
		// set default charset
		self::setCharset(Config::get('response', 'default_charset'));
		// set the default type
		self::setType(Config::get('response', 'default_type'));
		// init the assets packing filesystem
		self::initAssetsPacking();
		// marker
		Profiler::releaseMarker('Response.init', 'framework');
		
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
		// the cache has this request signature in store, cache is enabled, and browser allows cache
		return(
			Config::get('response', 'cache') && Cache::has(Request::getSignature()) && 
			Request::header('Cache-Control') != 'max-age=0' ? true : false
		);
	}
	
	private static function isCachable() :bool {
		// response is cachable, cache time is set, status is 200, type is not file,  
		// method is get, it is not ran is CLI mode (as cli is used for maintenance mainly)
		return(
			Config::get('response', 'cache') && self::$_status == 200 && 
			self::$_outputCache && self::$_type != 'file' && !Request::isPost() &&
			!Request::isCli()
		);
	}

	public static function disableBrowserCache() :void {
		// disable the browser's cache
		self::$_browserCache = false;
	}

	public static function enableOutputCache($hours = 24) :void {
		// enable the generated output to be cached for some time
		self::$_outputCache = round($hours * 3600);
	}
	
	public static function setCharset(string $charset) :void {
		// set the charset in meta tags and http headers
		self::$_charset = $charset;
	}
	
	public static function setRedirect(string $url, int $delay=0) :void {
		// if a delay is provided
		if($delay) {
			// set the refresh header that support delays, it is not at all understood by Google Bot
			self::setHeaders([
				'Refresh' => "{$delay};url=$url"
			]);	
		}
		// no delay is provided, use location that is understood by Google Bot
		else {
			// use standard redirect
			self::setHeaders([
				'Location' => $url
			]);
		}
	}

	public static function setAssets(string $type, $assets) :void {
		// if single element provided
		$assets = is_array($assets) ? $assets : [$assets];
		// for each assets to set
		foreach($assets as $asset) {
			// convert the case of the type
			$type = ucfirst(strtolower($type));
			// if asset is absolute
			$asset = (substr($asset,0,1) == '/' or substr($asset,0,4) == 'http') ? $asset : "/Assets/{$type}/{$asset}";
			// push in the list
			self::$_assets[$type][] = $asset;
		}
	}

	public static function setMetas(array $metas, $replace=false) :void {
		// replace or merge with current metas
		self::$_metas = $replace ? self::$_metas : array_merge(self::$_metas,$metas);
	}

	public static function setHeaders(array $headers) :void {	
		// merge current and new headers (replacing old ones)
		self::$_headers = array_merge(self::$_headers,$headers);
	}

	public static function setType(string $type) :void {
		
		// remove previously output data on change of type
		ob_clean();

		// if the type is allowed
		if(in_array($type, array_keys(self::TYPES))) {
			// update the current type
			self::$_type = $type;
		}
		// add the header
		self::setHeaders([
			'Content-type'=> self::TYPES[$type] . '; charset='.self::$_charset
		]);
		
	}

	// register a status header for that response
	public static function setStatus(int $code) :void {
	
		// set the actual status or 500 if incorrect
		self::$_status = in_array($code, array_keys(self::CODES)) ? $code : 500;
		
	}

	// get the status header
	public static function getStatus() :int {
	
		// get the currently set status
		return self::$_status;
		
	}

	// set raw content
	public static function setContent($content) :void {
		
		// remove any bufferred output
		self::clean();
		// replace direclty
		self::$_content = $content;

	}

	public static function getType() :string {
		// the current output type
		return(self::$_type);
	}

	public static function getCharset() :string {
		// the current charset
		return(self::$_charset);
	}

	// format an return metas
	private static function prependMetas() :void {
		// de-deuplicate js files
		self::$_metas = array_unique(self::$_metas);
		// return to the original order
		krsort(self::$_metas);
		// for each file
		foreach(self::$_metas as $meta => $value) {
			// add it
			self::$_content = '<meta name="'.$meta.'" content="' . Format::htmlSafe($value) . '" />' . self::$_content;
			// if the meta is a title, it's a bit special
			self::$_content = $meta == 'title' ? '<title>' . Format::htmlSafe($value) . '</title>' . self::$_content : self::$_content;
		}
	}

	// format and return javascripts
	private static function appendScripts() :void {
		// de-deuplicate js files
		self::$_assets['Js'] = array_unique(self::$_assets['Js']);
		// if there are not assets
		if(!self::$_assets['Js']) { return; }
		// if we are allowed to pack js files
		if(Config::isProd() && Config::get('response','pack_js') == 1) {
			// generate a unique name for the packed css files
			$js_pack_name = Keys::generate(self::$_assets['Js']) . '.js';
			$js_pack_file = "../Private/Storage/Cache/Assets/Js/{$js_pack_name}";
			// if the pack file doesn't exist yet
			if(!file_exists($js_pack_file)) {
				// the content of the pack
				$js_pack_contents = '';
				// for each asset
				foreach(self::$_assets['Js'] as $file) {
					// modify the filename
					if(substr($file, 0,2) == '//') {
						$file = "https:{$file}"; 
					}
					elseif(substr($file,0,1) == '/') {
						$file = ".{$file}";
					}
					// append he contents of that file to the pack
					$js_pack_contents .= " \n".file_get_contents($file);
				}
				// if minifying is allowed
				if(Config::get('response','minify')) {
					// instanciate a new minifier
					$minifier = new \MatthiasMullie\Minify\JS();
					// add our css contents
					$minifier->add($js_pack_contents);
					// minify 
					$js_pack_contents = $minifier->minify();
				}
				// populate the cache file
				file_put_contents($js_pack_file, $js_pack_contents);
			}
			// replace the assets import rules with the whole pack
			self::$_assets['Js'] = ["/Assets/Js/Cache/{$js_pack_name}"];
		}

		// for each file
		foreach(self::$_assets['Js'] as $file) {
			// add it
			self::$_content .= '<script type="text/javascript" src="'. $file .'"></script>';
		}
	}
	
	// format an return stylesheets
	private static function prependStyles() :void {
		// de-deuplicate css files
		self::$_assets['Css'] = array_unique(self::$_assets['Css']);
		// if there are not assets
		if(!self::$_assets['Css']) { return; }
		// if we are allowed to pack css files
		if(Config::isProd() && Config::get('response','pack_css') == 1) {
		// generate a unique name for the packed css files
			$css_pack_name = Keys::generate(self::$_assets['Css']) . '.css';
			$css_pack_file = "../Private/Storage/Cache/Assets/Css/{$css_pack_name}";
			// if the pack file doesn't exist yet
			if(!file_exists($css_pack_file)) {
				// the content of the pack
				$css_pack_contents = '';
				// for each asset
				foreach(self::$_assets['Css'] as $file) {
					// modify the filename
					if(substr($file, 0,2) == '//') {
						$file = "https:{$file}"; 
					}
					elseif(substr($file,0,1) == '/') {
						$file = ".{$file}";
					}
					// append he contents of that file to the pack
					$css_pack_contents .= " \n".file_get_contents($file);
				}
				// if minifying is allowed
				if(Config::get('response','minify')) {
					// instanciate a new minifier
					$minifier = new \MatthiasMullie\Minify\CSS();
					// add our css contents
					$minifier->add($css_pack_contents);
					// minify 
					$css_pack_contents = $minifier->minify();
				}
				// populate the cache file
				file_put_contents($css_pack_file, $css_pack_contents);
			}
			// replace the assets import rules with the whole pac
			self::$_assets['Css'] = ["/Assets/Css/Cache/{$css_pack_name}"];
		}
		// return to the original order
		krsort(self::$_assets['Css']);
		// for each file
		foreach(self::$_assets['Css'] as $file) {
			// support media specific CSS
			$href = is_array($file) ? $file[0] : $file;
			// default is for all medias
			$media = is_array($file) ? $file[1] : 'all';
			// add it
			self::$_content = '<link rel="stylesheet" media="' . $media . '" type="text/css" href="' . $href . '" />' . self::$_content;
		}
		
	}

	private static function initAssetsPacking() :void {
		// if we are allowed to use the assets packing feature
		if(Config::isProd() && (Config::get('response','pack_css') == 1 || Config::get('response','pack_js') == 1 )) {
			// create css and js packing cache directories if they don't exist yet
			is_dir('../Private/Storage/Cache/Assets/Css/') ?: 	mkdir('../Private/Storage/Cache/Assets/Css/', 0777, true);
			is_dir('../Private/Storage/Cache/Assets/Js/') ?: 	mkdir('../Private/Storage/Cache/Assets/Js/', 0777, true);
			// if the general assets file do not exist
			is_dir('./Assets/Css/') ?: 	mkdir('./Assets/Css/', 0777, true);
			is_dir('./Assets/Js/') ?: 	mkdir('./Assets/Js/', 0777, true);
			// create css and js public symlinks if it doesn't exist already
			is_link('./Assets/Css/Cache') ?: 	symlink('../../../Private/Storage/Cache/Assets/Css/', './Assets/Css/Cache');
			is_link('./Assets/Js/Cache') ?: 	symlink('../../../Private/Storage/Cache/Assets/Js/', './Assets/Js/Cache');
		}
	}

	// return current content
	private static function getContent() {
		
		// if response type is file, get the content of the file from the path, else return the normal content
		return(self::$_type == 'file' ? file_get_contents(self::$_content) : self::$_content);
		
	}

	// return the footprint a the response
	private static function getFootprint() :string {

		// get the profiler data
		$profiler = Profiler::getData();
		// assemble and return memory with time
		return(round($profiler['time'] * 1000, 1) . ' ms '. Format::size($profiler['memory']));

	}

	private static function renderFromCache() :string {

		// get the body and headers from the cache
		list($headers, $body) = Cache::get(Request::getSignature());
		// if the profiler is enabled
		!Config::get('profiler', 'enable_headers') ?: $headers['X-Cache-Footprint'] = self::getFootprint();
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
			// add the profiler
			self::$_content .= Config::get('profiler', 'enable') ? new Profiler\Html : '';
			// wrap in the body
			self::$_content = '</head><body>' . self::$_content;
			// preprend metas
			self::prependMetas();
			// preprend css
			self::prependStyles();
			// preprend scripts
			self::appendScripts();
			// add metas and style up top
			self::$_content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml"><head>
			<meta http-equiv="content-type" content="text/html; charset=' . self::$_charset . '" />' . self::$_content . '</body></html>';
			
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
		// if cache is disabled or we are outputing an error
		if(!self::$_browserCache || self::$_status != 200) {
			// output specific headers to disable browser cache
			$headers['Cache-Control'] = 'must-revalidate, post-check=0, pre-check=0';	
		}
		// if the profiler is enabled
		if(Config::get('profiler','enable_headers')) {
			// memory usage	and execution time
			$headers['X-Footprint'] = self::getFootprint();
		}
		// if the request is cachable
		if(self::isCachable()) {
			// add the caching time
			$headers['Date'] 	= date('r');
			// add the caching until (so that the browser too can cache)
			$headers['Expires'] = date('r', time() + self::$_outputCache);
			// tell that we are not from the cache
			$headers['X-Cache'] = 'miss';
		}
		// set some headers
		self::setHeaders($headers);

	}

	public static function clean() :string {
		// clean the reponse
		return(ob_get_clean());
	}

	public static function render() :void {
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
	
	public static function download($file_name) :void {
		// set download headers
		self::setHeaders([
			'Content-Description'	=>'File Transfer',
			'Content-Disposition'	=>'attachment; filename="' . Format::fsSafe($file_name) . '"'
		]);
		// render
		self::render();
	}

}

?>
