<?php
/**
 * PHP Version 5
 * Google Static Map images helper
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Google;

class Map {

	// the map api url
	private static $_api_url = 'https://maps.googleapis.com/maps/api/staticmap';

	// the options, url and markers
	private $options;
	private $markers;
	private $url;

	// main constructor
	public function __construct($type = 'roadmap', $size = 600, $zoom = 6 , $latitude = 46.8, $longitude = 1.7) {
		// initialize
		$this->url = self::$_api_url;
		$this->options = array();
		$this->markers = array();
		// set default
		$this->zoom($zoom);
		$this->size($size, $size);
		$this->type($type);
		$this->center($latitude, $longitude);
	}

	// set the desired size
	public function size($width, $height) {
		// assign
		$this->options['size'] = $width . 'x' . $height;
		// return self for chaining
		return($this);
	}

	// set the center position
	public function center($latitude, $longitude) {
		// assign
		$this->options['center'] = $latitude . ',' . $longitude;
		// return self for chaining
		return($this);
	}

	// set a marker
	public function marker($latitude, $longitude, $color=null, $label=null) {
		// build attributes
		$color = $color ? "color:" . $color  : '';
		$label = $label ? "label:" . strtoupper(substr($label,0,1)) : '';
		// assign
		$this->markers[] = ($color ? $color . '|' : '') . ($label ? $label . '|' : '') . $latitude . ',' . $longitude;
		// return self for chaining
		return($this);
	}

	// set an option
	public function option($key, $value) {
		// assign
		$this->options[$key] = $value;
		// return self for chaining
		return($this);
	}

	// set the zoom level
	public function zoom($zoom) {
		// assign
		$this->options['zoom'] = intval($zoom);
		// return self for chaining
		return($this);
	}

	// set the map type
	public function type($type) {
		// assign
		$this->options['maptype'] = $type;
		// return self for chaining
		return($this);
	}

	// set as retina
	public function retina($enable=true) {
		// assign
		$this->options['scale'] = $enable ? 2 : 1;
		// return self for chaining
		return($this);
	}

	// return the image url
	public function url() {
		// prepare the url
		$url = $this->url . '?';

		// if markers then unset center and zoom options
		// Google will do the job
		if($this->markers){
			unset($this->options['center']);
		}
		// for each option
		foreach($this->options as $key => $value) {
			// append it
			$url .= urlencode($key) . '=' . urlencode($value) . '&';
		}
		// if markers
		if($this->markers) {
			foreach($this->markers as $aMarker){
				$url .= '&markers=' . urlencode($aMarker);
			}
		}
		// return the url
		return(trim($url,'&'));
	}

	// magic conversion
	public function __toString() {
		// return the generated url
		return $this->url();
	}

}

?>
