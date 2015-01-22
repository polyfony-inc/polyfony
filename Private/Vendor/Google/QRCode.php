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

class QRCode {
	
	// api url
	private static $_api_url = 'https://chart.googleapis.com/chart?';

	// generate a new QRCode
	public static function url($data, $size=200) {

		// format the qrcode url
		return(self::$_api_url . "cht=qr&chs={$size}x{$size}&chld=M|0&chl=" . urlencode(json_encode($data)));

	}

}

?>
