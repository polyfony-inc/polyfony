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

namespace Polyfony\Store;

interface StoreInterface {
	
	
    /**
     * Check whether the variable exists in the store.
     *
     * @access public
     * @param  string  $variable The name of the variable to check existence of.
     * @return boolean           If the variable exists or not.
     */
    public static function has($variable);
    
    
    /**
     * Store a variable for use.
     *
     * @access public
     * @param  string  $variable  The name of the variable to store.
     * @param  mixed   $value     The data we wish to store.
     * @param  boolean $overwrite Whether we are allowed to overwrite the variable.
     * @return boolean            If we managed to store the variable.
     */
    public static function put($variable, $value, $overwrite);
    
    
    /**
     * Return the variable's value from the store.
     *
     * @access public
     * @param  string $variable The name of the variable in the store.
     * @return mixed
     */
    public static function get($variable);
    
    
    /**
     * Remove the variable in the store.
     *
     * @access public
     * @param  string $variable The name of the variable to remove.
     * @return boolean          If the variable was removed successfully.
     */
    public static function remove($variable);
}

?>
