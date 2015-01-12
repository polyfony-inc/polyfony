<?php
/**
 * PHP Version 5
 * Mail generation and sending class
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;
 
class Mail {

	public function __construct($id=null) {
		// retrieve from the database if id exists
	}

	public function type($type) {
		// html or text
	}

	public function to($recipient, $replace=false) {

	}

	public function cc($recipient, $replace=false) {

	}

	public function bcc($recipient, $replace=false) {

	}

	public function attach($file_path, $file_name) {

	}

	public function template($template_path) {

	}

	public function set($variable, $value) {

	}

	public function body($body, $replace=false) {

	}

	public function subject($subject, $replace=false) {

	}

	public function send($save=true) {
		// replace recipients in local mode
	}


}

?>
