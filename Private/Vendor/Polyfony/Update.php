<?php
/**
 * Autonomous class to synchronize your application with the production server
 * This class should not be used from within Polyfony
 *
 * /root-folder/
 * 		| Public/
 *		| Private/
 *		| update.sh
 *
 * ********************************************************
 * 
 * #!/usr/bin/env php
 * # update.sh
 * <?php
 * 
 * // include the update class
 * require('Private/Vendor/Polyfony/Update.php');
 *
 * // configure the update script
 * Update::server('remote.server.ns');
 * Update::user('root');
 * Update::rsyncOptions('-avzlp');
 * Update::localDirectory('/dev/path/domain/');
 * Update::remoteDirectory('/prod/path/domain/');
 *
 * // declare the folders to sync without any doubt
 * Update::folder('/Public/');
 * Update::folder('/Private/Bundles/');
 * Update::folder('/Private/Config/');
 * Update::folder('/Private/Vendor/');
 * 
 * // declare the folder to sync with a confirmation
 * Update::folder('/Private/Storage/Database/', 	true);
 * Update::folder('/Private/Storage/Logs/', 		true);
 * Update::folder('/Private/Storage/Cache/', 		true);
 * Update::folder('/Private/Storage/Store/', 		true);
 * Update::folder('/Private/Storage/Data/', 		true);
 * 
 * // actually sync
 * Update::run();
 *
 * ?>
 * 
 * ********************************************************
 * 
 * Then run the update.sh script like this :
 * 
 * ./update.sh up
 * to upload to the production server
 * ./update.sh down
 * to download from the production server
 *
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;
 
class Update {

	

}

?>
