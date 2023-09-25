<?php

/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

class Shopware_Plugins_Backend_SwagUpdate_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function getVersion()
    {
        return '1.0.0';
    }

    public function getLabel()
    {
        return 'Shopware Update';
    }

    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Index',
            'onBackendIndexPostDispatch'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_SwagUpdate',
            'onGetSwagUpdateControllerPath'
        );

        $this->createMenuItem([
            'label' => 'SwagUpdate',
            'controller' => 'SwagUpdate',
            'class' => 'sprite-arrow-continue-090',
            'action' => 'Index',
            'active' => 1,
            'parent' => $this->Menu()->findOneBy(['id' => 40]), // help menu
        ]);

        return ['success' => true, 'invalidateCache' => ['backend']];
    }

    /**
     * Register Plugin namespace in autoloader
     */
    public function afterInit()
    {
        /** @var Enlight_Loader $loader */
        $loader = $this->get(Enlight_Loader::class);
        $loader->registerNamespace(
            'ShopwarePlugins\\SwagUpdate',
            __DIR__ . '/'
        );
    }

    /**
     * When index backend module was loaded, add our snippet- and template-directory
     * Also extend the template
     *
     * @return void
     */
    public function onBackendIndexPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $args->getSubject()->View()->addTemplateDir(
            __DIR__ . '/Views/'
        );

        // if the controller action name equals "load" we inject our update check
        if ($args->getRequest()->getActionName() === 'load') {
            $args->getSubject()->View()->extendsTemplate(
                'backend/index/view/swag_update_menu.js'
            );
        }
    }

    /**
     * Returns to controller path to our SwagUpdate backend controller
     *
     * @return string
     */
    public function onGetSwagUpdateControllerPath(Enlight_Event_EventArgs $args)
    {
        $this->get(Enlight_Template_Manager::class)->addTemplateDir(
            __DIR__ . '/Views/',
            'swag_update'
        );

        return __DIR__ . '/Controllers/Backend/SwagUpdate.php';
    }
}
