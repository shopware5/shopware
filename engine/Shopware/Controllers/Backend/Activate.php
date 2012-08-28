<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
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
 * @package    Shopware_Controllers
 * @subpackage Activate
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

/**
 * todo@all: Documentation
 */
class Shopware_Controllers_Backend_Activate extends Enlight_Controller_Action
{	
	public function indexAction()
	{
		$this->View()->domain = $_SERVER['HTTP_HOST'];
	}
	
	public function skeletonAction(){
		
	}
	
	public function activateAction(){
		$this->View()->setTemplate();
		$license = $this->request->license;
		$license = str_replace(array("\n","\r"," "),"",trim($license));
		$key = $this->request->key;
		$key = str_replace(array("\n","\r"," "),"",trim($key));
		$sql = $this->request->sql;
		$domain = $_SERVER["HTTP_HOST"];
		if (empty($license) || empty($key)){
			echo json_encode(array("success"=>true,"error"=>true));
		}else {
			if ($this->checkLicense($domain,$license)!=false){
					$deleteQuery = Shopware()->Db()->query("
					DELETE FROM s_core_licences WHERE module IN ('sCORE','sCOMMUNITY')
					");
					if (preg_match("/sCORE/",$license)){
						$product = "sCORE";
					}else {
						$product = "sCOMMUNITY";
					}
					// Insert Licence
					$insertLicense = Shopware()->Db()->query("
					INSERT INTO s_core_licences (module,hash)
					VALUES (
					?,?
					)
					",array($product,$license));
					// Update productkey
					$updateKey = Shopware()->Db()->query("
					UPDATE s_core_config SET value = ? WHERE name = 'sACCOUNTID'
					",array($key));
					echo json_encode(array("success"=>true,"error"=>false));
				
			}else {
				echo json_encode(array("success"=>true,"error"=>true));
			}
		}
		
		if (!empty($sql)){
			$licenses = array();
			$req = "/[A-F0-9]{5}-[A-F0-9]{5}-[A-F0-9]{5}-[A-F0-9]{5}-[A-F0-9]{5}-[A-F0-9]{5}-#(s[A-Z][A-Z0-9]+)#/";
			if(preg_match_all($req,$sql,$licenses))
			{
				$licenses = array_combine($licenses[1],$licenses[0]);
			}
			foreach ($licenses as $key => $value)
			{
				$sql = "DELETE FROM s_core_licences WHERE module=?";
				Shopware()->Db()->query($sql,array($key));
				$sql = "
					INSERT INTO `s_core_licences` (`datum`, `module`, `hash`)
					VALUES(NOW(), ?, ?);
				";
				Shopware()->Db()->query($sql,array($key,$value));
			}
		}
		
			
	}
	
	private function checkLicense($domain,$license){
		$license = urlencode($license);
		$url = "http://account.shopware.de/core/checkKey.php?domain=$domain&licence=$license";

	    $client = new Zend_Http_Client($url);
	    $response = $client->request()->getBody();
		return $response == 1 ? true : false;
	}
	
}