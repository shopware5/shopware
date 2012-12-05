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
 * @package    Shopware_Plugins
 * @subpackage TicketSystem
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     M.Schmaeing
 */
class Shopware_Plugins_Core_SwagTicketSystem_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

    /**
     * Install function of the plugin bootstrap.
     * Registers all necessary components and dependencies.
     *
     * @public
     * @throws Exception
     * @return bool
     */
    public function install()
    {
        // Check if shopware version matches
        if (!$this->assertVersionGreaterThen('4.0.3')) {
            throw new Exception("This plugin requires Shopware 4.0.3 or a later version");
        }

        // Check license
        $this->checkLicense(true);

        $this->createForm();
        $this->createFormTranslation();
        $this->createEvents();
        $this->createMenu();
        $this->createDataBaseData();
        $this->importSnippets();

        //import the standard data
        $this->importDump('s_ticket_support_mails', 'submission.sql');
        $this->importDump('s_ticket_support_status', 'status.sql');
        $this->importDump('s_ticket_support_types', 'type.sql');

        $this->addACLResource();

        return true;
    }

    /**
     * Default uninstall method
     * Deletes the ACL resource
     *
     * @return bool
     */
    public function uninstall()
    {
       $this->deleteACLResource();
        return parent::uninstall();
    }


    /**
     * Creates the plugin form
     */
    protected function createForm()
    {
        $form = $this->Form();

        $form->setElement('boolean', 'sendCustomerNotification', array(
            'label' => 'Ticketbestätigung an den Kunden',
            'value' => false
        ));

        $form->setElement('boolean', 'sendShopOperatorNotification', array(
            'label' => 'Benachrichtigung bei neuen / beantworteten Tickets',
            'value' => false
        ));

        $form->setElement('text', 'ticketAccountFormId', array(
            'label' => 'Mein-Konto - Formular ID',
            'required' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
    }

    /**
     * creates a translation for the plugin form
     */
    public function createFormTranslation()
    {
        $form = $this->Form();
        $translations = array(
            'en_GB' => array(
                'sendCustomerNotification' => 'Send ticket confirmation to the customer',
                'sendShopOperatorNotification' => 'Notification of new / unanswered tickets',
                'ticketAccountFormId' => 'Account - Ticket form id',
            )
        );
        $shopRepository = Shopware()->Models()->getRepository('\Shopware\Models\Shop\Locale');
        foreach($translations as $locale => $snippets) {
            $localeModel = $shopRepository->findOneBy(array(
                'locale' => $locale
            ));
            foreach($snippets as $element => $snippet) {
                if($localeModel === null){
                    continue;
                }
                $elementModel = $form->getElement($element);
                if($elementModel === null) {
                    continue;
                }
                $translationModel = new \Shopware\Models\Config\ElementTranslation();
                $translationModel->setLabel($snippet);
                $translationModel->setLocale($localeModel);
                $elementModel->addTranslation($translationModel);
            }
        }
    }


    /**
     * Creates and subscribe the events and hooks.
     */
    protected function createEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Ticket',
            'onGetTicketController'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Ticket',
            'onGetFrontendControllerTicketPath'
        );

        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_TicketSystem',
            'onInitResourceTicketSystem'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatch'
        );
    }

    /**
     * Creates the menu structure
     */
    protected function createMenu()
    {
        $this->createMenuItem(array(
            'label' => 'Ticket-System',
            'controller' => 'Ticket',
            'class' => 'sprite-ticket--pencil',
            'action' => 'Index',
            'active' => 1,
            'parent' => $this->Menu()->findOneBy('label', 'Kunden')
        ));
    }

    /**
     * Creates the menu structure
     */
    protected function createDataBaseData()
    {
        //create table s_ticket_support
        $sql= "CREATE TABLE IF NOT EXISTS `s_ticket_support` (
              `id` int(10) NOT NULL AUTO_INCREMENT,
              `uniqueID` varchar(32) NOT NULL,
              `userID` int(10) NOT NULL,
              `employeeID` int(5) NOT NULL,
              `ticket_typeID` int(10) NOT NULL,
              `statusID` int(5) NOT NULL DEFAULT '1',
              `email` varchar(255) NOT NULL,
              `subject` varchar(255) NOT NULL,
              `message` text NOT NULL,
              `receipt` datetime NOT NULL,
              `last_contact` datetime NOT NULL,
              `additional` text NOT NULL,
              `isocode` varchar(3) NOT NULL DEFAULT 'de',
              `shop_id` int(11) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              KEY `shop_id` (`shop_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        Shopware()->Db()->exec($sql);

        //create table s_ticket_support_history
        $sql= "CREATE TABLE IF NOT EXISTS `s_ticket_support_history` (
              `id` int(10) NOT NULL AUTO_INCREMENT,
              `ticketID` int(10) NOT NULL,
              `swUser` varchar(100) NOT NULL,
              `subject` varchar(255) NOT NULL,
              `message` text NOT NULL,
              `receipt` datetime NOT NULL,
              `support_type` enum('manage','direct') NOT NULL,
              `receiver` varchar(200) NOT NULL,
              `direction` varchar(3) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        Shopware()->Db()->exec($sql);

        //create table s_ticket_support_mails
        $sql= "CREATE TABLE IF NOT EXISTS `s_ticket_support_mails` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `description` varchar(255) NOT NULL,
              `frommail` varchar(255) NOT NULL,
              `fromname` varchar(255) NOT NULL,
              `subject` varchar(255) NOT NULL,
              `content` text NOT NULL,
              `contentHTML` text NOT NULL,
              `ishtml` int(11) NOT NULL,
              `attachment` varchar(255) NOT NULL,
              `sys_dependent` tinyint(1) NOT NULL DEFAULT '0',
              `isocode` varchar(3) NOT NULL,
              `shop_id` int(11) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              KEY `shop_id` (`shop_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        Shopware()->Db()->exec($sql);

        //create table s_ticket_support_status
        $sql= "CREATE TABLE IF NOT EXISTS `s_ticket_support_status` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `description` varchar(50) NOT NULL,
              `responsible` tinyint(4) NOT NULL,
              `closed` tinyint(4) NOT NULL,
              `color` varchar(7) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        Shopware()->Db()->exec($sql);

        //create table s_ticket_support_types
        $sql= "CREATE TABLE IF NOT EXISTS `s_ticket_support_types` (
              `id` int(10) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `gridcolor` varchar(7) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        Shopware()->Db()->exec($sql);
    }

    /**
     * imports the standard data if not already available
     */
    protected function importDump($dataBaseTable, $dumpFileName) {
        $dataBaseTable = Shopware()->Db()->quoteTableAs($dataBaseTable);
        $sql = "SELECT id FROM ". $dataBaseTable;
        $foundId = Shopware()->Db()->fetchOne($sql, array());
        if(empty($foundId)) {
            $sql = file_get_contents($this->Path() .'/dumps/'.$dumpFileName);
            Shopware()->Db()->query($sql, array($dataBaseTable));
        }
    }

    /**
     * imports the standard data if not already available
     */
    protected function importSnippets() {
        //Add missing Snippet
        $sql = "
        INSERT IGNORE INTO `s_core_snippets` (
            `id` ,
            `namespace` ,
            `shopID` ,
            `localeID` ,
            `name` ,
            `value` ,
            `created` ,
            `updated`
        )
        VALUES (
            NULL ,
            'frontend/ticket/detail',
            '1',
            '1', 'TicketDetailInfoAnswerSubject',
            'Antwort',
            '2010-01-01 00:00:00',
            '2010-09-28 11:54:19'
        ),
        (
            NULL ,
            'frontend/ticket/detail',
            '1',
            '2', 'TicketDetailInfoAnswerSubject',
            'Answer',
            '2010-01-01 00:00:00',
            '2010-09-28 11:54:19'
        )";
        Shopware()->Db()->exec($sql, array());
    }

    /**
     * add the acl resource
     */
    protected function addACLResource() {
        $sql = "
            INSERT IGNORE INTO s_core_acl_resources (name) VALUES ('ticket');
            INSERT IGNORE INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'ticket'), 'create');
            INSERT IGNORE INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'ticket'), 'read');
            INSERT IGNORE INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'ticket'), 'update');
            INSERT IGNORE INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'ticket'), 'delete');
            INSERT IGNORE INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'ticket'), 'configure');
            UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'ticket') WHERE name = 'ticket';
        ";
        Shopware()->Db()->query($sql, array());
    }

    /**
     * deletes the acl resource
     */
    protected function deleteACLResource() {
        $sql = "DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'ticket');
                DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'ticket');
                DELETE FROM s_core_acl_resources WHERE name = 'ticket';";
        Shopware()->Db()->query($sql, array());
    }

    /**
     * Add our custom-models
     */
    public function afterInit(){
        $this->registerCustomModels();
    }

    /**
     * The onGetTicketController method is responsible to resolve the path to the ticket controller
     * of the ticket system.
     *
     * @public
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetTicketController(Enlight_Event_EventArgs $args)
    {
        // Check license
        $this->checkLicense(true);
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/', 'ticket'
        );
        return $this->Path(). 'Controllers/Backend/Ticket.php';
    }

    /**
     * The onGetFrontendControllerTicketPath method is responsible to resolve the path to the ticket controller
     * of the ticket system.
     *
     * @public
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetFrontendControllerTicketPath(Enlight_Event_EventArgs $args)
    {
        return $this->Path(). 'Controllers/Frontend/Ticket.php';
    }

    /**
     * Creates and returns the ticket system component for an event.
     *
     * @param Enlight_Event_EventArgs $args
     * @return \Shopware_Components_TicketSystem
     */
    public function onInitResourceTicketSystem(Enlight_Event_EventArgs $args)
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );

        $ticketSystem = new Shopware_Components_TicketSystem();
        Shopware()->Bootstrap()->registerResource('TicketSystem', $ticketSystem);
        return $ticketSystem;
    }

    /**
     * Checks the licence for the ticket-system and loads the template
     *
     * @param Enlight_Event_EventArgs $args
     * @return \Shopware_Components_TicketSystem
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();

        if(!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend'
            || $request->getControllerName() != 'account'
            && $request->getControllerName() != 'ticket'
            ) {
            return;
        }

        // Check license
        if($this->checkLicense(true)) {
            $view->sTicketLicensed = true;
        }


        Shopware()->Template()->addTemplateDir($this->Path() . 'Views/', 'ticket', Enlight_Template_Manager::POSITION_PREPEND);

    }


    /**
     * check licence
     *
     * @param   bool $throwException
     * @throws  Exception
     * @return  bool
     */
    final public function checkLicense($throwException = true)
    {
        static $r, $m = 'SwagTicketSystem';
        if(!isset($r)) {
            $s = base64_decode('qSos/dkDyXyIBrazPf7NoH64iLs=');
            $c = base64_decode('D3gG0sZCKqMyztOVXgpcAQwu8Ro=');
            $r = sha1(uniqid('', true), true);
            /** @var $l Shopware_Components_License */
            $l = $this->Application()->License();
            $i = $l->getLicense($m, $r);
            $t = $l->getCoreLicense();
            $u = strlen($t) === 20 ? sha1($t . $s . $t, true) : 0;
            $r = $i === sha1($c. $u . $r, true);
        }
        if(!$r && $throwException) {
            throw new Exception('License check for module "' . $m . '" has failed.');
        }
        return $r;
    }

    /**
     * Returns the label for the plugin info
     * @return array
     */
    public function getLabel()
    {
        return 'Ticket-System';
    }

    /**
     * Returns the version of plugin as string.
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     *
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel()
        );
    }

}