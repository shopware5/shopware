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
 * @package    Shopware_Core
 * @subpackage Class
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     $Author$
 */

/**
 * @Deprecated: will be removed in the near future. Please refer to PaymentMethods plugin for more information and future-proof examples.
 *
 * Deprecated payment class for debit procedures
 *
 * todo@all: Documentation
 */
class sPaymentMean{
	var $sSYSTEM;

    /**
     * DEPRECATED
     * @return array|bool
     */
	function sInit(){

		if (!$this->sSYSTEM->_POST["sDebitAccount"]){
			$sErrorFlag["sDebitAccount"] = true;
		}
		if (!$this->sSYSTEM->_POST["sDebitBankcode"]){
			$sErrorFlag["sDebitBankcode"] = true;
		}
		if (!$this->sSYSTEM->_POST["sDebitBankName"]){
			$sErrorFlag["sDebitBankName"] = true;
		}
		if (empty($this->sSYSTEM->_POST["sDebitBankHolder"])&&isset($this->sSYSTEM->_POST["sDebitBankHolder"])){
			$sErrorFlag["sDebitBankHolder"] = true;
		}
		
		
		$checkColumns = $this->sSYSTEM->sDB_CONNECTION->GetAll("SHOW COLUMNS FROM s_user_debit");
		$foundColumn = false;
		foreach ($checkColumns as $column){
			if ($column["Field"]=="bankholder"){
				$foundColumn = true;
			}
		}
		if (empty($foundColumn)){
			
			$this->sSYSTEM->sDB_CONNECTION->Execute("ALTER TABLE `s_user_debit` ADD `bankholder` VARCHAR( 255 ) NOT NULL ;");
		}
		
		
		if (count($sErrorFlag)) $error = true;
		
		if ($error){
			$sErrorMessages[] = Shopware()->Snippets()->getNamespace('frontend/account/internalMessages')->get('ErrorFillIn','Please fill in all red fields');
			
			return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
		}else {
			return true;
		}
		
		return array();
	}

    /**
     * DEPRECATED
     * @return mixed
     */
	function sUpdate(){
		if (empty($this->sSYSTEM->_SESSION["sUserId"])) return;
		
		if (count($this->getData())){
			$data = array(
				$this->sSYSTEM->_POST["sDebitAccount"],
				$this->sSYSTEM->_POST["sDebitBankcode"],
				$this->sSYSTEM->_POST["sDebitBankName"],
				$this->sSYSTEM->_POST["sDebitBankHolder"],
				$this->sSYSTEM->_SESSION["sUserId"]
			);
			
			$update = $this->sSYSTEM->sDB_CONNECTION->Execute("
			UPDATE s_user_debit SET account=?, bankcode=?, bankname=?,bankholder=?
			WHERE userID=?",$data);
		}else {
			$data = array(
				$this->sSYSTEM->_SESSION["sUserId"],
				$this->sSYSTEM->_POST["sDebitAccount"],
				$this->sSYSTEM->_POST["sDebitBankcode"],
				$this->sSYSTEM->_POST["sDebitBankName"],
				$this->sSYSTEM->_POST["sDebitBankHolder"]
			);
			$update = $this->sSYSTEM->sDB_CONNECTION->Execute("
			INSERT INTO s_user_debit (userID, account, bankcode, bankname, bankholder)
			VALUES (?,?,?,?,?)
			",$data);
		}
	}

    /**
     * DEPRECATED
     * @param $userId
     * @return bool
     */
	function sInsert($userId){
		if (!$userId) return false;
		// Insert data
		$data = array(
				$userId,
				$this->sSYSTEM->_POST["sDebitAccount"],
				$this->sSYSTEM->_POST["sDebitBankcode"],
				$this->sSYSTEM->_POST["sDebitBankName"],
				$this->sSYSTEM->_POST["sDebitBankHolder"]
			);
		$update = $this->sSYSTEM->sDB_CONNECTION->Execute("
		INSERT INTO s_user_debit (userID, account, bankcode, bankname,bankholder)
		VALUES (?,?,?,?,?)
		",$data);
		return true;
	}

    /**
     * DEPRECATED
     * @return array
     */
	function getData(){
		if (empty($this->sSYSTEM->_SESSION["sUserId"])) return array();
		
		$getData = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT account AS sDebitAccount, bankcode AS sDebitBankcode, bankname AS sDebitBankName, bankholder AS sDebitBankHolder FROM s_user_debit WHERE
		userID=?",array($this->sSYSTEM->_SESSION["sUserId"]));
		
		return $getData;
	}
}
?>