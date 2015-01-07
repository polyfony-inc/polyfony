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

class Position {
	
	// API url
	private static $_api_url = 'https://maps.googleapis.com/maps/api/geocode/json';

	// return a GPS position given an address
	public static function address($address) {
		// new http request
		$request = new \Polyfony\HttpRequest();
		// configure the request
		$success = $request->url(self::$_api_url)->data('address', $address)->get();
		// if the request succeeded
		if($success) {
			// get the response
			$response = $request->getBody();
			// check if the api found results
			if($response['status'] == 'OK') {
				// if gps coordinates are available
				if(isset($response['results'][0]['geometry']['location'])) {
					// return formatted address and position
					return(array(
						'address'	=>$response['results'][0]['formatted_address'],
						'position'	=>$response['results'][0]['geometry']['location']
					));
				}
				// missing position
				else { return(false); }
			}
			// api did not found succeed
			else { return(false); }
		}
		// the request failed
		else { return(false); }
	}

	// return an address given a GPS position
	public static function reverse($latitude, $longitude) {
		// new http request
		$request = new \Polyfony\HttpRequest();
		// configure the request
		$success = $request
			->url(self::$_api_url)
			->data('latlng', $latitude . ',' . $longitude)
			->get();
		// if the request succeeded
		if($success) {
			// get the response
			$response = $request->getBody();
			// check if the api found results
			if($response['status'] == 'OK') {
				// if gps coordinates are available
				if(isset($response['results'][0]['geometry']['location'])) {
					// return formatted address and position
					return(array(
						'address'	=>$response['results'][0]['formatted_address'],
						'position'	=>$response['results'][0]['geometry']['location']
					));
				}
				// missing position
				else { return(false); }
			}
			// api did not found succeed
			else { return(false); }
		}
		// the request failed
		else { return(false); }
	}


}

?>
