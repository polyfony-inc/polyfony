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

namespace Google;

class Photo {
	
	// api url
	private static $_api_url = 'https://maps.googleapis.com/maps/api/streetview?size=400x400&location=40.720032,-73.988354&fov=90&heading=235&pitch=10';

	// set the position
	public function position($latitude, $longitude) {

	}

	// set the street view photo given the address
	public function address($address, $zip_code ,$city) {

		// assemble the address portions if any

	}

	// get the url of the photo
	public function url() {

	}

	// get the map image file
	public function image() {

	}

}

?>
