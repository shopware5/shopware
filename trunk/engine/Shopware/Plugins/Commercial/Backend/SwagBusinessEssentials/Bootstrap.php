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
class Shopware_Plugins_Backend_SwagBusinessEssentials_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Plugin installation
     * Add events to register plugin backend modules
     * Add backend menu item
     * Create dispatch event on backend index to modify sidebar
     * Create database tables if not available
     * @throws Enlight_Exception
     * @return bool
     */
    public function install()
    {
        // Check if shopware version matches
        if (!$this->assertVersionGreaterThen('4.0.3')) {
            throw new Exception("This plugin requires Shopware 4.0.3 or a later version");
        }

        $this->checkLicense(true);

        Shopware()->Db()->query("
		DELETE FROM s_core_menu WHERE name = ?
		", array($this->getLabel()));

        $parent = $this->Menu()->findOneBy('label', 'Einstellungen');

        $item = $this->createMenuItem(array(
            'label' => $this->getName(),
            'onclick' => 'openAction(\'business_essentials\');',
            'class' => 'sprite-user-business',
            'active' => 1,
            'parent' => $parent,
            'position' => -1,
            'style' => 'background-position: 5px 5px;'
        ));

        $this->Menu()->addItem($item);

        $this->Menu()->save();

        /**
         * Register new Backend Controllers
         */
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_BusinessEssentials', 'onGetControllerPath');

        // Hook into unlock user widget
        $this->subscribeEvent('Enlight_Controller_Action_Backend_Widgets_RequestMerchantForm', 'onRequestMerchantFormAction');

        /*
           * Add custom template variables to frontend templates
           * Inject custom block inheritance
           */
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatch', 'onPostDispatchFrontend');

        /**
         * Private Shopping Login & Register Controller
         */
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_PrivateLogin', 'onGetControllerPathPrivateLogin');

        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_PrivateRegister', 'onGetControllerPathPrivateRegister');

        /**
         * Pre-Dispatch Event to change template depending on customergroup
         */
        $this->subscribeEvent('Enlight_Controller_Front_PreDispatch', 'onPreDispatchFrontend', 10);

        /**
         * Catch any frontend registration with sValidation parameter and do
         * validation if group is allowed to do own registration
         */
        $this->subscribeEvent('Shopware_Controllers_Frontend_Register_CustomerGroupRegister', 'onStartRegisterCheckGroup');

        /**
         * Catch any finished registration and set validation / group field
         * in s_user depending on configuration
         */
        $this->subscribeEvent('Shopware_Modules_Admin_SaveRegisterMainData_FilterSql', 'onFinishRegistrationFilterGroupField');

        /**
         * Catch any registration starts in frontend to inject custom
         * registration templates
         */

        $this->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Register', 'onStartRegisterController');
        // Deactivate shop redirects
        $this->subscribeEvent(
            'Enlight_Controller_Front_RouteStartup',
            'onRouteStartup',
            98
        );
        $this->installDatabaseTables();

        return true;
    }

    /**
     * @param   bool $throwException
     * @throws  Exception
     * @return  bool
     */
    public function checkLicense($throwException = true)
    {
        static $r, $m = 'SwagBusinessEssentials';
        if (!isset($r)) {
            $s = base64_decode('MnVD6MPsiTCC8e5mucZsJsgp/+g=');
            $c = base64_decode('kYxBgB4Jxm3dlsi0FHo+KPrJvss=');
            $r = sha1(uniqid('', true), true);
            /** @var $l Shopware_Components_License */
            $l = $this->Application()->License();
            $i = $l->getLicense($m, $r);
            $t = $l->getCoreLicense();
            $u = strlen($t) === 20 ? sha1($t . $s . $t, true) : 0;
            $r = $i === sha1($c . $u . $r, true);
        }
        if (!$r && $throwException) {
            throw new Exception('License check for module "' . $m . '" has failed.');
        }
        return $r;
    }

    public function onRequestMerchantFormAction(Enlight_Event_EventArgs $args)
    {
        $args->setProcessed(true);

        $subject = $args->getSubject();

        $customergroup = (string)$subject->Request()->getParam('customerGroup');
        $userId = (int)$subject->Request()->getParam('id');
        $mode = (string)$subject->Request()->getParam('mode');

        $selectCustomerGroup = Shopware()->Db()->fetchRow("
        SELECT emailtemplatedeny, emailtemplateallow  FROM  s_core_plugins_b2b_cgsettings
        WHERE customergroup = ?
        ", array($customergroup));

        if (!empty($selectCustomerGroup["emailtemplatedeny"]) && !empty($selectCustomerGroup["emailtemplateallow"])) {
            $tplMail = $mode == "allow" ? $selectCustomerGroup["emailtemplateallow"] : $selectCustomerGroup["emailtemplatedeny"];
        } else {
            if ($mode === 'allow') {
                $tplMail = 'sCUSTOMERGROUP%sACCEPTED';
            } else {
                $tplMail = 'sCUSTOMERGROUP%sREJECTED';
            }
            $tplMail = sprintf($tplMail, $customergroup);
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('mail'))
            ->from('Shopware\Models\Mail\Mail', 'mail')
            ->where('mail.name = ?1')
            ->setParameter(1, $tplMail);

        $mail = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        if (empty($mail)) {
            $subject->View()->assign(array('success' => false, 'message' => 'There is no mail for the specific customer group'));
            return false;
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('customer.email'))
            ->from('Shopware\Models\Customer\Customer', 'customer')
            ->where('customer.id = ?1')
            ->setParameter(1, $userId);

        $email = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        if (empty($email)) {
            $subject->View()->assign(array('success' => false, 'message' => 'There is no user for the specific user id'));
            return false;
        }

        $mail['toMail'] = $email['email'];
        $mail['content'] = nl2br($mail['content']);
        $mail['userId'] = $userId;
        $mail['status'] = ($mode === 'allow' ? 'accepted' : 'rejected');
        $subject->View()->assign(array('success' => true, 'data' => $mail));
    }

    public function getLabel()
    {
        return "Business Essentials";
    }

    /**
     * Install new database tables - these table are included in 3.5.5 by default -
     * @return void
     */
    public function installDatabaseTables()
    {
        Shopware()->Db()->query("
			CREATE TABLE IF NOT EXISTS `s_core_plugins_b2b_cgsettings` (
			  `customergroup` varchar(10) NOT NULL,
			  `allowregister` tinyint(1) NOT NULL,
			  `requireunlock` tinyint(1) NOT NULL,
			  `assigngroupbeforeunlock` varchar(10) NOT NULL,
			  `registertemplate` varchar(255) NOT NULL,
			  `emailtemplatedeny` varchar(255) NOT NULL,
			  `emailtemplateallow` varchar(255) NOT NULL,
			  PRIMARY KEY (`customergroup`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;
		");
        Shopware()->Db()->query("
			CREATE TABLE IF NOT EXISTS `s_core_plugins_b2b_tpl_config` (
			  `customergroup` varchar(255) NOT NULL,
			  `fieldkey` varchar(255) NOT NULL,
			  `fieldvalue` varchar(255) NOT NULL,
			  PRIMARY KEY (`customergroup`,`fieldkey`)
			 ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
		");
        Shopware()->Db()->query("
			CREATE TABLE IF NOT EXISTS `s_core_plugins_b2b_tpl_variables` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `variable` varchar(255) NOT NULL,
			  `description` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`)
			 ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
		");
        Shopware()->Db()->query("
			CREATE TABLE IF NOT EXISTS `s_core_plugins_b2b_private` (
			  `customergroup` varchar(25) NOT NULL,
			  `activatelogin` tinyint(1) NOT NULL,
			  `redirectlogin` varchar(255) NOT NULL,
			  `redirectregistration` varchar(255) NOT NULL,
			  `registerlink` tinyint(1) NOT NULL,
			  `registergroup` varchar(50) NOT NULL,
			  `unlockafterregister` tinyint(1) NOT NULL,
			  `templatelogin` varchar(50) NOT NULL,
			  `templateafterlogin` varchar(50) NOT NULL,
			  PRIMARY KEY (`customergroup`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;
		");
    }

    /**
     * Hook into Register-Controller and prepend unauthorized access to group
     * registration pages
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public function onStartRegisterController(Enlight_Event_EventArgs $args)
    {
        $this->checkLicense(true);

        $sValidation = $args->getRequest()->getParam('sValidation');

        // Do template replacement only if group specific registration and index Action
        if (!empty($sValidation) && $args->getRequest()->getActionName() == "index") {
            $getGroupRegistrationTemplate = Shopware()->Db()->fetchOne("
			SELECT registertemplate FROM s_core_plugins_b2b_cgsettings WHERE customergroup = ?
			", array($sValidation));
            if (!empty($getGroupRegistrationTemplate)) {
                $args->getSubject()->View()->loadTemplate("frontend/register/" . $getGroupRegistrationTemplate);
            }
        }
    }

    /**
     * Event triggered in sSaveRegisterMainData from sAdmin Object
     * Do SQL manipulation to set fields conform to configuration
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public function onFinishRegistrationFilterGroupField(Enlight_Event_EventArgs $args)
    {

        $getFilterData = $args->getReturn();
        $getBoundedParameters = $getFilterData[1];

        $sValidation = $getBoundedParameters[4]; // This is the group the user wants to be in

        if (empty($sValidation)) {
            return $getFilterData;
        }

        // Read configuration for this customer group
        $getGroupConfiguration = Shopware()->Db()->fetchRow("
		SELECT * FROM s_core_plugins_b2b_cgsettings WHERE customergroup = ?
		", array($sValidation));

        if (empty($getGroupConfiguration["customergroup"])) return $getFilterData;

        // Check if group should get unlocked manually
        if (empty($getGroupConfiguration["requireunlock"])) {
            $getBoundedParameters[7] = $sValidation; // Set group direct
            $getBoundedParameters[4] = ""; // Unset validation parameter
        } else {
            if (!empty($getGroupConfiguration["assigngroupbeforeunlock"])) {
                // Group to use after registration is configured (Default=EK)
                $getBoundedParameters[7] = $getGroupConfiguration["assigngroupbeforeunlock"];
            }
        }

        return array($getFilterData[0], $getBoundedParameters);
    }

    /**
     * Event triggered in personalAction / registerController
     * Check if given customer group is allowed to do own registration
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return nothing or in error case false
     */
    public function onStartRegisterCheckGroup(Enlight_Event_EventArgs $args)
    {
        $customerGroup = $args->getSubject()->Request()->getParam('sValidation');
        if (empty($customerGroup)) {
            return false;
        }
        $checkIfRegistrationIsAllowed = Shopware()->Db()->fetchOne("SELECT allowregister FROM s_core_plugins_b2b_cgsettings WHERE customergroup = ?", array($customerGroup));
        if (empty($checkIfRegistrationIsAllowed) && $customerGroup != "H") { // Allow H for compatibility reasons
            return false;
        }

    }

    /**
     * Listen on all frontend requests after dispatch
     * Passing template configuration depending on customer group to template
     * Load custom template to modify template
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public function onPostDispatchFrontend(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();

        // Load this code only in frontend
        if (!$request->isDispatched() || $response->isException()
          || $request->getModuleName() != 'frontend' || !$view->hasTemplate()) {
            return;
        }

        $shop = Shopware()->Shop();
        $customerGroup = Shopware()->Session()->sUserGroup;
        if (empty($customerGroup)) {
            $customerGroup = $shop->getCustomerGroup()->getKey();
        }

        // Load template configuration for this group
        $getTemplateConfiguration = Shopware()->Db()->fetchPairs("
		  SELECT `fieldkey`,`fieldvalue` FROM s_core_plugins_b2b_tpl_config WHERE customergroup = ?
		", array($customerGroup));

        foreach ($getTemplateConfiguration as $variable => $value) {
            $args->getSubject()->View()->assign($variable, $value);
        }
        /**
         * Pass Private Shopping config to template
         */
        $getPrivateShoppingConfig = Shopware()->Db()->fetchRow("
		  SELECT * FROM s_core_plugins_b2b_private WHERE customergroup = ?
		", array($customerGroup));

        $view->privateShoppingConfiguration = $getPrivateShoppingConfig;
        /**
         * Assign template configuration to view and load custom template to support block inheriting
         */
        $view->templateConfiguration = $getTemplateConfiguration;

        //$this->Application()->Template()->addTemplateDir(
        //    $this->Path() . 'Views/'
        //);
        //$args->getSubject()->View()->extendsTemplate("frontend/b2bessentials/templateconfig.tpl");
    }

    /**
     * Custom Controller for private shopping login page
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public function onGetControllerPathPrivateLogin(Enlight_Event_EventArgs $args)
    {
        $this->checkLicense(true);

        return dirname(__FILE__) . '/Controllers/Frontend/PrivateLogin.php';
    }

    /**
     * Custom Controller for private shopping login page
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public function onGetControllerPathPrivateRegister(Enlight_Event_EventArgs $args)
    {
        $this->checkLicense(true);

        return dirname(__FILE__) . '/Controllers/Frontend/PrivateRegister.php';
    }

    /**
     * Prevent shop from redirect to live-shop-url
     * Pass parameters from staging-config to shop-config
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onRouteStartup(Enlight_Controller_EventArgs $args)
    {
        $bootstrap = $this->Application()->Bootstrap();
        if (!$bootstrap->issetResource('Shop')) {
            return false;
        }

        $shop = Shopware()->Shop();
        $customerGroup = Shopware()->Session()->sUserGroup;
        if (empty($customerGroup)) {
            $customerGroup = $shop->getCustomerGroup()->getKey();
        }

        /**
         * Load Private Shopping Configuration depending on customer group
         */
        $getPrivateShoppingConfig = Shopware()->Db()->fetchRow("
            SELECT * FROM s_core_plugins_b2b_private WHERE customergroup = ?
        ", array($customerGroup));

        Shopware()->Session()->getPrivateShoppingConfig = $getPrivateShoppingConfig;

        // If any group specific template ist defined, load this instead of default template
        if (!empty($getPrivateShoppingConfig["templateafterlogin"]) && $getPrivateShoppingConfig["templateafterlogin"] != $shop->getTemplate()->getId()
            && Shopware()->Session()->sUserId != false
        ) {

            $shop = $this->Application()->Shop();
            $main = $shop->getMain();

            if ($main === null) {
                /** @var $repository Shopware\Models\Shop\Repository */
                $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
                $main = $repository->getActiveById($shop->getId());
            }

            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Template');
            $template = $repository->findOneBy(array('id' => $getPrivateShoppingConfig["templateafterlogin"]));
            if ($template !== null) {
                $shop->setTemplate($template);
            } else {
                $shop->setTemplate($main->getTemplate());
            }
        }
    }

    /**
     * Listen on all frontend requests before dispatch processing starting
     * Load private shopping configuration
     * Change Template if necessary / Redirect to private shopping login page if configured and user is unauthorized
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public function onPreDispatchFrontend(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        if (($request->getModuleName() && $request->getModuleName() != 'frontend') || $request->getControllerName() == 'error') {
            return;
        }

        $shop = Shopware()->Shop();
        $customerGroup = Shopware()->Session()->sUserGroup;
        if (empty($customerGroup)) {
            $customerGroup = $shop->getCustomerGroup()->getKey();
        }

        /**
         * Load Private Shopping Configuration depending on customer group
         */
        $getPrivateShoppingConfig = Shopware()->Db()->fetchRow("
		  SELECT * FROM s_core_plugins_b2b_private WHERE customergroup = ?
		", array($customerGroup));

        Shopware()->Session()->getPrivateShoppingConfig = $getPrivateShoppingConfig;


        /**
         * Ignoring all requests and show private shopping login page in case that this feature is enabled
         * and the controller is not called itself
         */

        if (
            !empty($getPrivateShoppingConfig["activatelogin"]) && // Do redirect only if configured
            strpos($args->getSubject()->Request()->action, "ajax") === false && // Allow ajax calls for registration
            ($args->getSubject()->Request()->getControllerName() != "PrivateLogin" && // Allow private shopping controllers
            ($args->getSubject()->Request()->getControllerName() != "PrivateRegister" || empty($getPrivateShoppingConfig["registerlink"])))
        ) {

            // Check if user is logged in
            if ((Shopware()->Session()->sUserId == false || // Customer is not logged in
                (Shopware()->Session()->sUserId != false && empty($getPrivateShoppingConfig["unlockafterregister"]))) // Customer is logged in but have to get unlocked
            ) {
                // User is unauthorized - redirect to login page
                $request->setControllerName('PrivateLogin');
            }
        }
    }

    /**
     * Placeholder method to support remove of default database tables
     * for this plugin (later)
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall();
    }


    /**
     * Backend Controller for business essentials backend module
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
        return dirname(__FILE__) . '/Controllers/Backend/BusinessEssentials.php';
    }

    /**
     * Backend Controller for business essentials unlock module
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetControllerPathUnlock(Enlight_Event_EventArgs $args)
    {
        return dirname(__FILE__) . '/Controllers/Backend/BusinessEssentialsUnlock.php';
    }

    /**
     * Get version tag of this plugin to display in manager
     * @return string
     */
    public function getVersion()
    {
        return "1.0.4";
    }
}
