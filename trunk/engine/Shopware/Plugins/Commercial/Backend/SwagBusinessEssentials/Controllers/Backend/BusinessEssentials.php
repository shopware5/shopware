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
class Shopware_Controllers_Backend_BusinessEssentials extends Enlight_Controller_Action
{
	/**
	 * Doing global licence check for this controller
	 * @return void
	 */
	final public function init(){
        $licenceCheck = Shopware()->Plugins()->Backend()->SwagBusinessEssentials()->checkLicense(false);
		$this->View()->sLicenceCheck = $licenceCheck;

		$this->View()->addTemplateDir(dirname(__FILE__).'/../../Views/');
	}
	/**
	 * Load index.tpl to display in module base window
	 * @return void
	 */
	public function indexAction(){
		$this->View()->loadTemplate("backend/b2bessentials/index.tpl");
	}

	/**
	 * Dynamically load additional js objects from smarty templates
	 * @return void
	 */
	public function loadAction(){
		$templateRoot = "backend/b2bessentials/";	// Defining template base

        $template = $this->Request()->file; // Before B2B (?) todo@all

        $template = str_replace("B2B/","",$template);

		$template = preg_replace("#[^A-Za-z]#i","",$template);
		$this->View()->loadTemplate($templateRoot.$template.".tpl");

		// Get all customergroups
		$this->View()->customerGroups = Shopware()->Db()->fetchAll("
		SELECT * FROM s_core_customergroups ORDER BY id ASC
		");

		$this->View()->customerGroupsWithRegistrationConfiguration = Shopware()->Db()->fetchAll("
		SELECT `groupkey`,`description` FROM s_core_customergroups INNER JOIN s_core_plugins_b2b_cgsettings ON s_core_plugins_b2b_cgsettings.customergroup = groupkey
		AND allowregister = 1
		");
		// Pass url basepath to template (using to load old backend modules like customergroup management)
		$this->View()->BaseUri = $this->Request()->getScheme()."://".Shopware()->Config()->BasePath;
	}

	/**
	 * Load json formatted window properties needed by shopware window manager
	 * @return void
	 */
	public function skeletonAction(){

		$this->View()->loadTemplate("backend/b2bessentials/skeleton.tpl");
	}

	/**
	 * Triggered by template configuration grid via ajax
	 * return json formatted array (matrix)
	 * @return void
	 */
	public function getTemplateConfigurationAction(){
		$this->View()->setTemplate();

		// Get all customergroups
		$customerGroups = Shopware()->Db()->fetchAll("
		SELECT * FROM s_core_customergroups ORDER BY id ASC
		");

		// Get all template variables
		$variables = Shopware()->Db()->query("
		SELECT * FROM s_core_plugins_b2b_tpl_variables
		");

		// Building cross-matrix
		$resultRows = array();
		foreach ($variables as $variable){
			$temp = array("function"=>$variable["variable"],"description"=>!empty($variable["description"]) ? $variable["description"]." Smarty: {\$".$variable["variable"]."}" : $variable["variable"],"id"=>$variable["id"]);
			foreach ($customerGroups as $customerGroup){
				$getConfigValue =  Shopware()->Db()->fetchOne("
				SELECT fieldvalue FROM s_core_plugins_b2b_tpl_config WHERE customergroup = ? AND fieldkey = ?
				",array($customerGroup["groupkey"],$variable["variable"]));
				$temp[$customerGroup["groupkey"]] = $getConfigValue == "1" ? true : false;
			}
			$resultRows[] = $temp;
		}
		echo Zend_Json::encode($resultRows);
	}

	/**
	 * Get all email templates from s_core_config_mails
	 * @return json formatted array
	 */
	public function getMailTemplatesAction(){
		$this->View()->setTemplate();
		$data = Shopware()->Db()->fetchAll("
		SELECT id,name,CONCAT(name,'-',subject) AS description FROM s_core_config_mails
		ORDER BY name ASC
		");
		echo Zend_Json::encode(array("data"=>$data,"total"=>count($data)));
	}

	/**
	 * Update boolean state of variable is set or not set for a certain customergroup
	 * @return void
	 */
	public function updateTemplateVariableConfigAction(){
		$this->View()->setTemplate();
		$variable = $this->Request()->variable;
		$customerGroup = $this->Request()->customergroup;
		$value = (!empty($this->Request()->value) && $this->Request()->value != "false") ? 1 : 0;

		if (!empty($variable) && !empty($customerGroup) && isset($value)){

			if (Shopware()->Db()->fetchOne("SELECT customergroup FROM s_core_plugins_b2b_tpl_config WHERE
			customergroup = ? AND fieldkey = ?
			",array($customerGroup,$variable))){
				Shopware()->Db()->query("
				UPDATE IGNORE s_core_plugins_b2b_tpl_config SET customergroup = ?, fieldkey = ?,
				fieldvalue = ?
				",array($customerGroup,$variable,$value));
			}else {
				Shopware()->Db()->query("
				INSERT INTO s_core_plugins_b2b_tpl_config (customergroup,fieldkey,fieldvalue) VALUES (?,?,?)
				",array($customerGroup,$variable,$value));
			}
			echo Zend_Json::encode(array("success"=>true));

		}else {
			echo Zend_Json::encode(array("success"=>false));
		}
	}

	/**
	 * Save customer group registration configuration
	 * Configure own registration pages for different customer groups
	 * @return json result array
	 */
	public function saveRegisterConfigAction(){
		$this->View()->setTemplate();
		$customerGroup = $this->Request()->customergroup;
		$allowRegister = $this->Request()->allowregister == "on" ? 1 : 0;
		$requireUnlock = $this->Request()->requireunlock == "on" ? 1 : 0;
		$assignGroupBeforeUnlock = $this->Request()->assigngroupbeforeunlock;
		$registerTemplate = $this->Request()->registertemplate;
		$emailTemplateAllow = $this->Request()->emailtemplateallow;
		$emailTemplateDeny = $this->Request()->emailtemplatedeny;

		if (!Shopware()->Db()->fetchOne("SELECT customergroup FROM s_core_plugins_b2b_cgsettings WHERE customergroup = ?",array($customerGroup))){
			// Insert row
			$sql = "
			INSERT INTO s_core_plugins_b2b_cgsettings (customergroup,allowregister,requireunlock,assigngroupbeforeunlock,registertemplate,emailtemplateallow,emailtemplatedeny)
			VALUES (?,?,?,?,?,?,?)
			";
			Shopware()->Db()->query($sql,array($customerGroup,$allowRegister,$requireUnlock,$assignGroupBeforeUnlock,$registerTemplate,$emailTemplateAllow,$emailTemplateDeny));

		}else {
			// Update row
			$sql = "
			UPDATE s_core_plugins_b2b_cgsettings
			SET allowregister = ?,
			requireunlock = ?,
			assigngroupbeforeunlock = ?,
			registertemplate = ?,
			emailtemplateallow = ?,
			emailtemplatedeny = ?
			WHERE customergroup = ?
			";
			Shopware()->Db()->query($sql,array($allowRegister,$requireUnlock,$assignGroupBeforeUnlock,$registerTemplate,$emailTemplateAllow,$emailTemplateDeny,$customerGroup));
		}
		echo Zend_Json::encode(array("success"=>true));
	}

	/**
	 * Load details for customer registration configuration form
	 * @return json array
	 */
	public function loadRegisterConfigAction(){
		$this->View()->setTemplate();
		$customerGroup = $this->Request()->customergroup;
		$fetchData = Shopware()->Db()->fetchRow("
		SELECT * FROM s_core_plugins_b2b_cgsettings WHERE customergroup = ?
		",array($customerGroup));
		echo Zend_Json::encode(array("success"=>true,"data"=>$fetchData));
	}

	/**
	 * Save details on private shopping configuration  mask
	 * @return json formatted array with success info
	 */
	public function savePrivateShoppingConfigAction(){
		$this->View()->setTemplate();

		$customerGroup = $this->Request()->customergroup;
		$activateLogin = $this->Request()->activatelogin == "on" ? 1 : 0;

		// Redirect locations
		$redirectLogin = $this->Request()->redirectlogin;
		$redirectRegistration = $this->Request()->redirectregistration;

		$registerLink = $this->Request()->registerlink == "on" ? 1 : 0;
		$unlockAfterRegister = $this->Request()->unlockafterregister == "on" ? 1 : 0;
		$templateLogin = $this->Request()->templatelogin;
		$registerGroup = $this->Request()->registergroup;

		if (empty($registerGroup)) $registerGroup = "";

		$templateAfterLogin = !empty($this->Request()->templateafterlogin) ? $this->Request()->templateafterlogin : "";


		if (!Shopware()->Db()->fetchOne("SELECT customergroup FROM s_core_plugins_b2b_private WHERE customergroup = ?",array($customerGroup))){
			// Insert row
			$sql = "
			INSERT INTO s_core_plugins_b2b_private (customergroup,activatelogin,redirectlogin,redirectregistration,registerlink,registergroup,unlockafterregister,templatelogin,templateafterlogin)
			VALUES (?,?,?,?,?,?,?,?,?)
			";
			Shopware()->Db()->query($sql,array($customerGroup,$activateLogin,$redirectLogin,$redirectRegistration,$registerLink,$registerGroup,$unlockAfterRegister,$templateLogin,$templateAfterLogin));

		}else {
			// Update row
			$sql = "
			UPDATE s_core_plugins_b2b_private
			SET activatelogin = ?,
			redirectlogin = ?,
			redirectregistration = ?,
			registerlink = ?,
			registergroup = ?,
			unlockafterregister = ?,
			templatelogin = ?,
			templateafterlogin = ?
			WHERE customergroup = ?
			";
			Shopware()->Db()->query($sql,array($activateLogin,$redirectLogin,$redirectRegistration,$registerLink,$registerGroup,$unlockAfterRegister,$templateLogin,$templateAfterLogin,$customerGroup));
		}
		echo Zend_Json::encode(array("success"=>true));
	}

	/**
	 * Load details for private shopping configuration mask
	 * @return json array
	 */
	public function loadPrivateShoppingConfigAction(){
		$this->View()->setTemplate();
		$customerGroup = $this->Request()->customergroup;
		$fetchData = Shopware()->Db()->fetchRow("
		SELECT * FROM s_core_plugins_b2b_private WHERE customergroup = ?
		",array($customerGroup));
		echo Zend_Json::encode(array("success"=>true,"data"=>$fetchData));
	}


	/**
	 * Load template to display in "article and categories for customergroups" module
	 * @return void
	 */
	public function loadArticlePermissionsInnerFrameAction(){
		$this->View()->loadTemplate("backend/b2bessentials/ArticlePermissionsInner.tpl");
	}

	/**
	 * Load template to display in "Group price configuration" module
	 * @return void
	 */
	public function loadGroupPricesInnerFrameAction(){
		$this->View()->loadTemplate("backend/b2bessentials/GroupPricesInner.tpl");
	}

	/**
	 * Load template to display in "Unlock Users" module
	 * @return void
	 */
	public function loadUnlockUsersInnerFrameAction(){
		$this->View()->loadTemplate("backend/b2bessentials/UnlockUsersInner.tpl");
	}

	/**
	 * Load all defined template variables from database
	 * @return json for extjs grid
	 */
	public function getTemplateVariablesAction(){
		$this->View()->setTemplate();
		$result = Shopware()->Db()->fetchAll("
		SELECT * FROM s_core_plugins_b2b_tpl_variables ORDER BY variable ASC
		");
		echo Zend_Json::encode(array("count"=>count($result),"data"=>$result));
	}

	/** Delete template variable from database - table s_core_plugins_b2b_tpl_variables
	 * @return json success notice
	 */
	public function deleteTemplateVariableAction(){
		$this->View()->setTemplate();

		$id = $this->Request()->id;

		Shopware()->Db()->query("
		DELETE FROM s_core_plugins_b2b_tpl_variables WHERE id = ?
		",array($id));

		echo Zend_Json::encode(array("success"=>true));
	}

	/**
	 * Insert or edit existing template variable
	 * @return json success notice
	 */
	public function insertTemplateVariableAction(){
		$this->View()->setTemplate();

		$id = $this->Request()->id;
		$field = $this->Request()->field;
		$value = $this->Request()->value;


		if (!empty($id)){
			Shopware()->Db()->query("
			UPDATE s_core_plugins_b2b_tpl_variables SET `$field` = ? WHERE
			id = ?
			",array($value,$id));
		}else {

			Shopware()->Db()->query("
			INSERT INTO s_core_plugins_b2b_tpl_variables (`$field`)
			VALUES (?)
			",array($value));
			$id = Shopware()->Db()->lastInsertId();
		}
		echo Zend_Json::encode(array("success"=>true,"id"=>$id));
	}

	/**
	 * Get a list of all templates that are available
	 * @return json formatted array
	 */
	public function getTemplatesAvailableAction(){
		$this->View()->setTemplate();

		$templates[] = array("name"=>"Ausgewähltes Shop-Template","value"=>"");

		$templates = array_merge($templates,Shopware()->Db()->fetchAll("SELECT id AS value, name FROM s_core_templates"));


		echo Zend_Json::encode(array("data"=>$templates,"total"=>count($templates)));

	}
}
