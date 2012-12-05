<?php
/**
 * Shopware 4.0
 * Copyright Â© 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 [*] The texts of the GNU Affero General Public License with an additional
 [*] permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Shopware_Core
 * @subpackage Class
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     $Author$
 */
set_time_limit(1000);
// Parameters
$phantomJs = "/usr/bin/phantomjs";
$cookie = "/tmp/cookies.txt";
$loginScript = dirname(__FILE__)."/login.js";
$executionScript = dirname(__FILE__)."/client.js";

$mysql = array("host"=>"localhost","user"=>"root","password"=>"","database"=>"shopware_phantom");

// Login to shopware backend via phantom


// Loop through all shopware controllers and call phantom to check javascript
mysql_connect($mysql["host"],$mysql["user"],$mysql["password"]);
mysql_select_db($mysql["database"]);

$query = mysql_query("SELECT * FROM s_core_menu WHERE (onclick = '' OR onclick IS NULL) AND controller != ''");

while ($getController = mysql_fetch_assoc($query)){
    if (in_array($getController["controller"],array("Content","ConfigurationMenu","Marketing","Payments"))) continue;
	$controller = $getController["controller"];
	$action = strtolower($getController["action"]);

    $login = array();
    exec($phantomJs." --cookies-file=".escapeshellarg($cookie)." ".$loginScript,$login);
    $login = join("\n",$login);

    if (strpos($login,"Login successfully")===false){
    	$a = strpos($login,"Login successfully");
    	$b = strpos(strtolower($login),"fail");
    	file_put_contents('php://stderr', PHP_EOL.PHP_EOL . "Phantom Login-Script failed ($a#$b)" . PHP_EOL.$login);
    	exit(1);
    }

	echo "Proceed: $controller > $action.\n";
	sleep(5);
	$openController = array();
	exec($phantomJs." --cookies-file=".escapeshellarg($cookie)." ".$executionScript." ".$controller." ".$action,$openController);
	$openController = join("\n",$openController);
	echo $openController."\n\n";
	if (strpos(strtolower($openController),"fail")!==false){
		file_put_contents('php://stderr', PHP_EOL.PHP_EOL . "Phantom Controller processing failed: ($controller/$action)" . PHP_EOL.$openController);
		exit(1);
	}
}



exit(0);