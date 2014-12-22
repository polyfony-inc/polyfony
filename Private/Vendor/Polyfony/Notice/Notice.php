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

namespace Polyfony\Notice;

class Notice {
	
	private $title;
	private $message;
	private $code;
	private $type;

	public function __construct($message=null,$title=null,$code=null) {
		

		
	}

	public function getMessage() {
		
	}
	
	public function getTitle() {
		
	}
	
	public function getType() {
		
	}
	
	public function getCode() {
		
	}
	
	public function __toString() {
	
		// format the notice 
		// h1
		// p
		return 'Notice !';
		
	}

	
}

?>