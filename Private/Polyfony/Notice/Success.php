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

class Success extends \Polyfony\Notice {

	const TYPE = 'success';

	public function __toString() {

		// type specific
		return(str_replace('__TYPE__',self::TYPE,$this->getHtml()));

	}
	
}

?>
