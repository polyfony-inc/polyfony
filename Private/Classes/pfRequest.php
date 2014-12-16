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
 
class pfRequest {
	
	private static $_url;
	private static $_get;
	private static $_post;
	private static $_server;
	
	public static function setUrl($url = null) {
		// Set the URL
		self::$_url = $url ?: $_SERVER['REQUEST_URI'];

		// We want to remove the path root from the front request URL, stripping
		// .. out all of the information this class does not care about.
		if (strpos(self::$_url, Config::get('path', 'root')) === 0) {
			self::$_url = '/' . substr(self::$_url, strlen(Config::get('path', 'root')));
		}
	}
	
}

?>