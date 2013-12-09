<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 */

require_once(dirname(__FILE__) . '/Components/Store.php');
/**
 */
class Shopware_Plugins_Core_PluginManager_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

    /**
     * Returns an array with the capabilities of the plugin manager.
     * @return array
     */
    public function getCapabilities()
    {
        return array(
            'install' => false,
            'enable' => false,
            'update' => true
        );
    }

    /**
     * Install function of the plugin bootstrap.
     * Registers all necessary components and dependencies.
     * @return bool
     */
    public function install()
	{
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PluginManager',
            'onGetPluginController'
        );

        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_CommunityStore',
            'onInitCommunityStore'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Store',
            'onGetStoreController'
        );

        $this->createMenuItem(array(
            'label' => 'Plugin Manager',
            'controller' => 'PluginManager',
            'class' => 'sprite-application-block',
            'action' => 'Index',
            'active' => 1,
            'parent' => $this->Menu()->findOneBy('label', 'Einstellungen')
        ));
        return true;
    }


    /**
     * Returns an instance of out CommunityStore component
     *
     * @return CommunityStore
     */
    public function onInitCommunityStore()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );
        return new CommunityStore();
    }

    /**
     * The onGetPluginController function is responsible to resolve the path to the plugin controller
     * of the plugin manager.
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetPluginController(Enlight_Event_EventArgs $args)
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/', 'plugin_manager'
        );
        return $this->Path(). 'Controllers/Backend/PluginManager.php';
    }

    /**
     * The onGetStoreController function is responsible to resolve the path to the store controller
     * of the plugin manager.
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetStoreController(Enlight_Event_EventArgs $args)
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/', 'plugin_manager'
        );
        return $this->Path() . 'Controllers/Backend/Store.php';
    }
}
