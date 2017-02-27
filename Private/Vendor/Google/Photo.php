<?php
/**
 * PHP Version 5
 * Google Streetview image helper
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Google;

class Photo {
	
	// api url
	private static $_api_url = 'https://maps.googleapis.com/maps/api/streetview';

	// options
	private $url;
	private $options;

	// constructor
	public function __construct($size = 600, $fov = 90, $pitch = 10) {
		$this->url = self::$_api_url;
		$this->options = array(
			'size'	=>$size . 'x' . $size,
			'fov'	=>$fov,
			'pitch'	=>$pitch
		);
	}

	// set an option
	public function option($key, $value) {
		// assign
		$this->options[$key] = $value;
		// return self for chaining
		return($this);
	}

	// set the desired size
	public function size($width, $height) {
		// assign
		$this->options['size'] = $width . 'x' . $height;
		// return self for chaining
		return($this);
	}

	// set the position
	public function position($latitude, $longitude) {
		// assign
		$this->options['location'] = $latitude . ',' . $longitude;
		// return self for chaining
		return($this);
	}

	// set the street view photo given the address
	public function address($address, $zip_code=null ,$city=null) {
		// assemble the address portions if any
		$address = trim($address . ' ' . $zip_code . ' ' .$city);
		// if a position is found
		$this->options['location'] = $address;
		// return self for chaining
		return($this);
	}

	// get the url of the photo
	public function url() {
		// prepare the url
		$url = $this->url . '?';
		// for each option
		foreach($this->options as $key => $value) {
			// append it
			$url .= urlencode($key) . '=' . urlencode($value) . '&';
		}
		// return the url
		return(trim($url,'&'));
	}

}

?>
