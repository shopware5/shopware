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
class Shopware_Controllers_Frontend_PrivateRegister extends Shopware_Controllers_Frontend_Register
{

	/**
	 * Add plugin frontend templates to smarty scope
	 * @return void
	 */
	public function init(){
		parent::init();

		$this->View()->privateShoppingConfig = Shopware()->Session()->getPrivateShoppingConfig;
	}

	/**
	 * Prepend default templates from parent controller to get displayed
	 * @return void
	 */
	public function preDispatch()
	{
		parent::preDispatch();

        $this->View()->addTemplateDir(dirname(dirname(dirname(__FILE__)))."/Views/frontend/");
		//$this->View()->setTemplate();
	}

	/**
	 * Modify the whole template as long as we are in private shopping mode
	 * (Hide most of default shop options!)
	 * @return void
	 */
	public function postDispatch(){
		$isUserLoggedIn = Shopware()->Modules()->Admin()->sCheckUser();
		// Check if user is logged in
		if (($isUserLoggedIn == false || 	// Customer is not logged in
			($isUserLoggedIn == true && empty(Shopware()->Session()->getPrivateShoppingConfig["unlockafterregister"]))) // Customer is logged in but have to get unlocked
		){
			$this->View()->extendsTemplate("b2bessentials/psblocks.tpl");
		}
	}

	/**
	 * Default index action - displaying registration formular
	 * @return void
	 */
	public function indexAction(){
        $this->View()->loadTemplate("frontend/register/index.tpl");
		parent::indexAction();

	}

	/**
	 * Hook original save register method and redirect to "own" confirmation page
	 * @return void
	 */
    public function saveRegisterAction(){
        parent::saveRegisterAction();
        if ($this->error = true){
            return $this->forward('index');
        }else {
            $controllerAction = Shopware()->Session()->getPrivateShoppingConfig["redirectregistration"];
            $controllerAction = explode("/",$controllerAction);

            return $this->redirect(array('controller' => $controllerAction[0], 'action' => $controllerAction[1]));
        }
    }

	/**
	 * Display register confirmation to inform customer about unlock process
	 * @return void
	 */
	public function registerConfirmedAction(){
		$this->View()->loadTemplate("b2bessentials/psregisterfinished.tpl");
	}
}