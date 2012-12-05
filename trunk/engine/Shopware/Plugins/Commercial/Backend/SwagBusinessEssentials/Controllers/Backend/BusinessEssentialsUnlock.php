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
 * @package    Shopware_Plugins_Backend_SwagBusinessEssentials
 * @subpackage Result
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     $Author$
 */
class Shopware_Controllers_Backend_BusinessEssentialsUnlock extends Enlight_Controller_Action
{

	/**
	 * This is a commercial part of business essentials
	 * Therefore we check if licence is available
	 * @throws Enlight_Exception
	 * @return void
	 */
	final public function init(){
		
		$this->View()->addTemplateDir(dirname(__FILE__).'/../../Views/'); 
	}
	/**
	 * Load basic template to include extjs
	 * @return void
	 */
	public function indexAction(){
		$this->View()->loadTemplate("backend/b2bessentials/Unlock.tpl");
	}

	/**
	 * Load skeleton (json) that define window properties
	 * @return void
	 */
	public function skeletonAction(){
		$this->View()->loadTemplate("backend/b2bessentials/UnlockSkeleton.tpl");
	}

	/**
	 * Load all users that have the column validation active (need to unlock)
	 * @return json formatted array
	 */
	public function getUsersRequestedForUnlockAction(){

		$this->View()->setTemplate();
		// todo@all Add Licence check
		$getUsersToUnlock = Shopware()->Db()->fetchAll("
		SELECT s.id,s.email,s.customergroup,s.validation,scc.description AS toCustomergroup, DATE_FORMAT(s.firstlogin,'%d.%m.%Y') AS firstlogin,
		su.company,su.customernumber,CONCAT(su.firstname,' ',su.lastname) AS contact,su.street,CONCAT(su.zipcode,' ',su.city) AS city
		FROM
		 s_user s
		 INNER JOIN
		 s_user_billingaddress su ON s.id = su.userID
		 INNER JOIN s_core_customergroups scc ON scc.groupkey = s.validation
		 WHERE
		 s.validation != ''
		 ORDER BY
		 s.id DESC
		");
		foreach ($getUsersToUnlock as &$user){
			$user["toCustomergroup"] = utf8_encode($user["toCustomergroup"]);
			$user["company"] = utf8_encode($user["company"]);
			$user["contact"] = utf8_encode($user["contact"]);
			$user["city"] = utf8_encode($user["city"]);
		}
		echo Zend_Json::encode(array("total"=>count($getUsersToUnlock),"data"=>$getUsersToUnlock));
	}


	/**
	 * Get count of customers that request to move to other customer group
	 * @return string with numeric value of number of customers to unlock
	 */
	public function getNumOfCustomerToUnlockAction(){
		$this->View()->setTemplate();

		echo Shopware()->Db()->fetchOne("
		SELECT COUNT(id) FROM s_user WHERE validation != ''
		");
	}

	/**
	 * Load email & user data to unlock / deny a certain user to a new customer group
	 * @return json formatted array
	 */
	public function loadDataAction(){
		$this->View()->setTemplate();
		// todo@all Add Licence check
		$customerGroup = $this->Request()->validation;
		$userId = $this->Request()->userID;
		if (empty($customerGroup) || empty($userId)){
			throw new Enlight_Exception("Params customerGroup or userId missed");
		}

		// This method supports both - allow or deny of customer group moving - therefore we need to know, which template should be used
		$allowOrDeny = $this->Request()->allow == "true" ? "emailtemplateallow" : "emailtemplatedeny";

		// Get Customergroup Configuration
		$getCustomerGroupConfiguration = Shopware()->Db()->fetchRow("
		SELECT * FROM s_core_plugins_b2b_cgsettings WHERE customergroup = ?
		",array($customerGroup));
		// Get Mail-Template (allow or deny group)
		$getMailTemplate = Shopware()->Db()->fetchRow("
		SELECT * FROM s_core_config_mails WHERE name = ?
		",array($getCustomerGroupConfiguration[$allowOrDeny]));

		// Catch error - no mail template configured -
		if (empty($getMailTemplate["id"])){
			$getMailTemplate["subject"] = "Error: No email template configured for this customergroup.";
			$getMailTemplate["content"] = "Please switch to business essentials module and define mail-templates for this customergroup.";
		}

		// Fix encoding problems
		$getMailTemplate["subject"] = utf8_encode($getMailTemplate["subject"]);
		// Use html - template if available
		$getMailTemplate["content"] = nl2br(utf8_encode(!empty($getMailTemplate["contenthtml"]) ? $getMailTemplate["contenthtml"] : $getMailTemplate["content"]));
		
		$getMailTemplate["fromname"] = utf8_encode($getMailTemplate["fromname"]);

		// Fetch user-data
		$userData = Shopware()->Db()->fetchRow("
		SELECT s.email,s.paymentID,s.language,s.subshopID,s.validation,sb.* FROM s_user s, s_user_billingaddress sb
		WHERE s.id = sb.userID AND s.id = ?
		",array($userId));

		// Check if user is existing
		if (empty($userData["email"])){
			throw new Enlight_Exception("Failure while fetching user-data");
		}
		
		// Assign user-data to template, so we could use smarty-variables here
			// Get Smarty instance
			$templateEngine = Shopware()->Template();
			$templateData = $templateEngine->createData();
			// Assign user-data from array
			foreach ($userData as $key => $value) $templateData->assign($key,utf8_encode($value));
			// Parse template
			$getMailTemplate["content"] = $templateEngine->fetch('string:'.$getMailTemplate["content"],$templateData);	// Mail Content
			$getMailTemplate["subject"] = $templateEngine->fetch('string:'.$getMailTemplate["subject"],$templateData); // Mail Subject

		// Return data to extjs form
		$data = array("email"=>$userData["email"],"content"=>$getMailTemplate["content"],"subject"=>$getMailTemplate["subject"],
		"frommail"=>$getMailTemplate["frommail"],"fromname"=>$getMailTemplate["fromname"]
		);
		
		echo Zend_Json::encode(array("success"=>true,"data"=>$data));
	}

	/**
	 * Send confirmation email to customer if group state has confirmed or declined
	 * Change customer group in database s_user.customergroup
	 * @return
	 */
	public function allowDenyUserAction(){
		$this->View()->setTemplate();
		// todo@all Add Licence check
		$changeUserGroup = $this->Request()->allow == "true" ? true : false;
		
		$newCustomerGroup = $this->Request()->validation;

		$recipient = $this->Request()->email;
		$fromName = $this->Request()->fromname;
		$fromMail = $this->Request()->frommail;
		$subject = $this->Request()->subject;
		$mailContent = $this->Request()->content;
		$userId = $this->Request()->userId;

		if (empty($userId) || empty($recipient) || empty($newCustomerGroup)){
			echo Zend_Json::encode(array("success"=>false,'msg'=>"One ore more needed parameters missing!"));
			return;
		}
		
		// Send eMail to customer
		$mail = clone Shopware()->Mail();
		$mail->IsHTML(true);
		$mail->From     = $fromMail;
		$mail->FromName = utf8_decode($fromName);
		$mail->Subject  = utf8_decode($subject);
		$mail->Body     = utf8_decode($mailContent);
		$mail->ClearAddresses();
		$mail->AddAddress($recipient, "");
		
		if (!$mail->Send()){
			echo Zend_Json::encode(array("success"=>false,'msg'=>"Could not send email. Please check mail configuration!"));
			return;
		}

		// Change user customer group
		if ($changeUserGroup == true){
			if (!Shopware()->Db()->query("
			UPDATE s_user SET customergroup = ?, validation = '' WHERE id = ?
			",array($newCustomerGroup,$userId))){
				echo Zend_Json::encode(array("success"=>false,'msg'=>"Change of customergroup failed for some reason"));
				return;
			}
		}else {
			Shopware()->Db()->query("
			UPDATE s_user SET validation = '' WHERE id = ?
			",array($userId));
		}
		echo Zend_Json::encode(array("success"=>true));
	}
}