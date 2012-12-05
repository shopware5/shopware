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
class Shopware_Controllers_Frontend_PrivateLogin extends Shopware_Controllers_Frontend_Account
{

	/**
	 * Add plugin frontend template folder to smarty scope
	 * @return void
	 */
	public function init(){
		parent::init();
		$this->View()->privateShoppingConfig = Shopware()->Session()->getPrivateShoppingConfig;
	}

	/**
	 * Load login template defined in private shopping config
	 * @return void
	 */
	public function indexAction($reset = true){
       if ($reset){
		if (!empty(Shopware()->Session()->getPrivateShoppingConfig["templatelogin"])){
			$this->View()->loadTemplate("b2bessentials/".Shopware()->Session()->getPrivateShoppingConfig["templatelogin"]);
		}else {
			$this->View()->loadTemplate("b2bessentials/pslogin.tpl");
		}
       }
	}

	/**
	 * Overwrite parent method to add index method to list of allowed controller methods without login
	 * Remove default template display of the controller
	 * @return void
	 */
	public function preDispatch()
	{
        $this->View()->addTemplateDir(dirname(dirname(dirname(__FILE__)))."/Views/frontend/");
		if(!in_array($this->Request()->getActionName(), array('login', 'logout', 'password', 'ajax_login', 'ajax_logout','index'))
			&& !$this->admin->sCheckUser())
		{
			$this->forward('login');
		}
		//$this->View()->setTemplate();
	}

	/**
	 * Doing parent login and redirect to indexAction of this controller
	 * @return void
	 */
	public function loginAction(){
        if (!empty(Shopware()->Session()->getPrivateShoppingConfig["templatelogin"])){
       			$this->View()->loadTemplate("b2bessentials/".Shopware()->Session()->getPrivateShoppingConfig["templatelogin"]);
       		}else {
       			$this->View()->loadTemplate("b2bessentials/pslogin.tpl");
       		}
		parent::loginAction();
		$isUserLoggedIn = Shopware()->Modules()->Admin()->sCheckUser();
		if ($isUserLoggedIn == false){
			// Goto login page to show error notices
			return $this->indexAction(false);
		}
		// Otherwise go to defined controller (Default index/index)
		$psConfig = Shopware()->Session()->getPrivateShoppingConfig;
		$controllerAction = explode("/",$psConfig["redirectlogin"]);

		return $this->redirect(array('controller' => $controllerAction[0], 'action' => $controllerAction[1]));
	}

	/**
	 * Support for password request action from original controller
	 * @return void
	 */
	public function passwordAction(){
		parent::passwordAction();
		$this->View()->addTemplateDir(dirname(dirname(dirname(__FILE__)))."/Views/frontend/");
		$this->View()->loadTemplate("frontend/account/password.tpl");
	}

	/**
	 * Modify the whole template as long as we are in private shopping mode
	 * (Hide most of default shop options!)
	 * @return void
	 */
	public function postDispatch(){
		$this->View()->extendsTemplate("b2bessentials/psblocks.tpl");
	}
}