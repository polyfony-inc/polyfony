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
	
	// register an error header and optionaly set content at the same time
	public static function error($number,$content=null) {
		// do domething more clever than before to output html errors if required
	}

	public static function render() {
		
		// if no content is set yet we garbage collect
		self::$_content = self::$_content ?: ob_get_clean();
			
		// format the content
		self::formatContent();	
		
		// output the content
		self::outputContent();
		
		// it ends here
		exit;
		
	}

}

?>