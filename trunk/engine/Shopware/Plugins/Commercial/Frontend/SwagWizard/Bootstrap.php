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
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

class Shopware_Plugins_Frontend_SwagWizard_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Returns the current version of the plugin.
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * Get (nice) name for plugin manager list
     * @return string
     */
    public function getLabel()
    {
        return 'Produktberater (SwagWizard)';
    }

    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install()
    {
        $this->checkLicense(true);
        $this->subscribeEvents();
        $this->createMenu();
        $this->createDatabaseTables();

        return array(
            'success' => true,
            'invalidateCache' => array(
                'backend',
                'frontend'
            )
        );
    }

    /**
     * Install plugin method
     *
     * @return bool
     */
    public function uninstall()
    {
        return array(
            'success' => true,
            'invalidateCache' => array(
                'backend',
                'frontend'
            )
        );
    }

    /**
     * @param  bool      $throwException
     * @throws Exception
     * @return bool
     */
    public function checkLicense($throwException = true)
    {
        static $r, $m = 'SwagWizard';
        if (!isset($r)) {
            $s = base64_decode('I8U26SOO+WQeMT+8AU2BhQbZPvo=');
            $c = base64_decode('CWvXCihhgEHJLJWmE4lNhJPmc5Y=');
            $r = sha1(uniqid('', true), true);
            /** @var $l Shopware_Components_License */
            $l = $this->Application()->License();
            $i = $l->getLicense($m, $r);
            $t = $l->getCoreLicense();
            $u = strlen($t) === 20 ? sha1($t . $s . $t, true) : 0;
            $r = $i === sha1($c. $u . $r, true);
        }
        if (!$r && $throwException) {
            throw new Exception('License check for module "' . $m . '" has failed.');
        }

        return $r;
    }

    /**
     * This function creates the menu entry to allow access on the backend module.
     * @return void
     */
    private function createMenu()
    {
        $this->createMenuItem(array(
            'label' => 'Wizard',
            'class' => 'sprite-navigation',
            'onclick' => 'openAction(\'wizard\');',
            'active' => 1,
            'parent' => $this->Menu()->findOneBy('label', 'Artikel')
        ));
    }

    /**
     * Registers all necessary events and hooks.
     */
    private function subscribeEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Wizard',
            'onGetFrontendControllerPath'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Wizard',
            'onGetBackendControllerPath'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Listing',
            'onPostDispatch'
        );

        $this->subscribeEvent(
            'Shopware_Modules_Admin_Login_Successful',
            'onLoginSuccessful'
        );
    }

    /**
     * This function creates all database tables of the bonus system.
     * @return void
     */
    private function createDatabaseTables()
    {
        $querys = array(
            'CREATE TABLE IF NOT EXISTS `s_plugin_wizard` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `description` text,
              `shopID` int(11) unsigned DEFAULT NULL,
              `image` varchar(255) DEFAULT NULL,
              `sidebar` int(1) unsigned NOT NULL,
              `block` varchar(255) DEFAULT NULL,
              `active` int(1) unsigned NOT NULL,
              `hide_empty` int(1) unsigned NOT NULL,
              `preview` int(1) unsigned NOT NULL,
              `max_quantity` int(11) unsigned DEFAULT NULL,
              `show_other` int(1) unsigned NOT NULL,
              `listing` int(1) unsigned NOT NULL,
              PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        ,
            'CREATE TABLE IF NOT EXISTS `s_plugin_wizard_articles` (
              `wizardID` int(11) unsigned NOT NULL,
              `articleID` int(11) unsigned NOT NULL,
              PRIMARY KEY (`wizardID`,`articleID`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        ,
            'CREATE TABLE IF NOT EXISTS `s_plugin_wizard_categories` (
              `wizardID` int(11) unsigned NOT NULL,
              `categoryID` int(11) unsigned NOT NULL,
              PRIMARY KEY (`wizardID`,`categoryID`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        ,
            'CREATE TABLE IF NOT EXISTS `s_plugin_wizard_filters` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `wizardID` int(11) unsigned NOT NULL,
              `typeID` int(11) unsigned NOT NULL,
              `name` varchar(255) NOT NULL,
              `description` text NOT NULL,
              `position` int(11) NOT NULL,
              `active` int(1) unsigned NOT NULL,
              `range_from` decimal(10,2) unsigned DEFAULT NULL,
              `range_to` decimal(10,2) unsigned DEFAULT NULL,
              `steps` decimal(10,2) unsigned DEFAULT NULL,
              `storeID` int(11) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        ,
            'CREATE TABLE IF NOT EXISTS `s_plugin_wizard_relations` (
              `filterID` int(11) unsigned NOT NULL,
              `articleID` int(11) unsigned NOT NULL,
              `valueID` int(11) unsigned NOT NULL,
              `score` int(11) DEFAULT NULL,
              PRIMARY KEY (`filterID`,`articleID`,`valueID`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        ,
            'CREATE TABLE IF NOT EXISTS `s_plugin_wizard_values` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `filterID` int(11) unsigned NOT NULL,
              `key` varchar(255) NOT NULL,
              `value` varchar(255) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `filterID` (`filterID`,`key`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        ,
            'CREATE TABLE IF NOT EXISTS `s_plugin_wizard_requests` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `wizardID` int(11) unsigned NOT NULL,
              `filterID` int(11) unsigned DEFAULT NULL,
              `sessionID` varchar(255) NOT NULL,
              `userID` int(11) DEFAULT NULL,
              `added` datetime NOT NULL,
              `changed` datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `wizardID` (`wizardID`,`sessionID`),
              KEY `added` (`added`),
              KEY `changed` (`changed`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        ,
            '
            CREATE TABLE IF NOT EXISTS `s_plugin_wizard_results` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `wizardID` int(11) NOT NULL,
              `articleID` int(1) unsigned NOT NULL,
              `requestID` int(11) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `articleID` (`articleID`,`requestID`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        );

        foreach ($querys as $query) {
            Shopware()->Db()->exec($query);
        }
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        $view = $args->getSubject()->View();
        $view->addTemplateDir(dirname(__FILE__) . '/Views/');
        $view->extendsTemplate('frontend/wizard/sidebar.tpl');

        $sql = '
            SELECT w.*
            FROM `s_plugin_wizard` w
            INNER JOIN s_plugin_wizard_categories wc
            ON wc.`wizardID`=w.id
            AND wc.`categoryID`=?
            WHERE (`shopID`=? OR `shopID` IS NULL)
            AND `sidebar`=1
            AND `active`=1
        ';

        $rows = Shopware()->Db()->fetchAssoc($sql, array(
            $view->sCategoryContent['id'],
            Shopware()->Shop()->getId()
        ));

        $wizards = array();
        foreach ($rows as $row) {
            if (!empty($row['image'])) {
                $row['image'] = 'media/image/' . $row['image'];
            }
            if (isset($wizards[$row['block']])) {
                $wizards[$row['block']][] = $row;
            } else {
                $wizards[$row['block']] = array($row);
            }
        }
        $view->Wizards = $wizards;
    }

    /**
     * Event listener method
     *
     * @param  Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetBackendControllerPath(Enlight_Event_EventArgs $args)
    {
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/',
            'swag_wizard'
        );

        return $this->Path() . 'Controllers/Backend/Wizard.php';
    }

    /**
     * Event listener method
     *
     * @param  Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetFrontendControllerPath(Enlight_Event_EventArgs $args)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/',
            'swag_wizard'
        );

        return $this->Path() . 'Controllers/Frontend/Wizard.php';
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onLoginSuccessful(Enlight_Event_EventArgs $args)
    {
        $user = $args->getUser();
        $sql = '
            UPDATE `s_plugin_wizard_requests`
            SET `userID`=?
            WHERE `changed` >= DATE_SUB(NOW(), INTERVAL 1 DAY)
            AND `userID` IS NULL
            AND `sessionID`=?
        ';

        Shopware()->Db()->query($sql, array(
            $user['id'],
            Shopware()->SessionID()
        ));
    }
}
