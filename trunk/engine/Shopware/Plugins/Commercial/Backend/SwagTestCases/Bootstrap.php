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
 * @subpackage Bundle
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 */

/**
 *
 */
class Shopware_Plugins_Backend_SwagTestCases_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Returns the current version of the bundle plugin.
     * @return string
     */
    public function getVersion()
    {
        return "1.0.0";
    }

    /**
     * Install function of the plugin bootstrap.
     * Registers all necessary components and dependencies.
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvents();

        $this->createMenu();

        return array('success' => true, 'invalidateCache' => array('backend'));
    }

    private function createMenu()
    {
        $this->createMenuItem(array(
               'label' => 'Test Cases',
               'controller' => 'SwagTestCases',
               'class' => 'sprite-bug',
               'action' => 'Index',
               'active' => 1,
               'parent' => $this->Menu()->findOneBy('label', 'Einstellungen')
          ));
    }

    private function subscribeEvents()
    {
        //event listener for the backend controller of the bundle module.
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_SwagTestCases',
            'onGetBackendController'
        );
    }

    public function onGetBackendController(Enlight_Event_EventArgs $arguments)
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );
        return $this->Path(). 'Controllers/Backend/SwagTestCases.php';
    }

}
