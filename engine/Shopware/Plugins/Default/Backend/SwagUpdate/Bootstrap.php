<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

class Shopware_Plugins_Backend_SwagUpdate_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * Returns the human readable name of the plugin
     *
     * @return string
     */
    public function getLabel()
    {
        return 'Shopware Update';
    }

    /**
     * @return array
     */
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

        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_SwagUpdateUpdateCheck',
            'onInitUpdateCheck'
        );

        $this->installForm($this->Form());

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
        $loader = $this->get('loader');
        $loader->registerNamespace(
            'ShopwarePlugins\\SwagUpdate',
            __DIR__ . '/'
        );
    }

    /**
     * When index backend module was loaded, add our snippet- and template-directory
     * Also extend the template
     *
     * @param \Enlight_Controller_ActionEventArgs $args
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
        $this->get('template')->addTemplateDir(
            __DIR__ . '/Views/', 'swag_update'
        );

        return __DIR__ . '/Controllers/Backend/SwagUpdate.php';
    }

    /**
     * Returns an instance of the UpdateCheck component
     */
    public function onInitUpdateCheck()
    {
        return new \ShopwarePlugins\SwagUpdate\Components\UpdateCheck(
            $this->Config()->get('update-api-endpoint'),
            $this->Config()->get('update-channel'),
            $this->Config()->get('update-verify-signature'),
            Shopware()->Container()->get('shopware.openssl_verificator'),
            Shopware()->Container()->get('shopware.release')
        );
    }

    /**
     * @param \Shopware\Models\Config\Form $form
     */
    protected function installForm(Shopware\Models\Config\Form $form)
    {
        $form->setElement('select', 'update-channel', [
            'label' => 'Channel',
            'value' => 'stable',
            'store' => [
                ['stable', 'stable'],
                ['beta',   'beta'],
                ['rc',     'rc'],
                ['dev',    'dev'],
            ], ]
        );

        $form->setElement('text', 'update-api-endpoint', [
            'label' => 'API Endpoint',
            'required' => true,
            'value' => 'http://update-api.shopware.com/v1/',
            'hidden' => true,
        ]);

        $form->setElement('text', 'update-fake-version', [
            'label' => 'Fake Version',
            'hidden' => true,
        ]);

        $form->setElement('text', 'update-code', [
            'label' => 'Code',
            'value' => '',
        ]);

        $form->setElement('boolean', 'update-verify-signature', [
            'label' => 'Verify Signature',
            'hidden' => true,
            'value' => true,
        ]);

        $form->setElement('text', 'update-feedback-api-endpoint', [
            'label' => 'Feedback API Endpoint',
            'required' => true,
            'value' => 'http://feedback.update-api.shopware.com/v1/',
            'hidden' => true,
        ]);

        $form->setElement('boolean', 'update-send-feedback', [
            'label' => 'Send feedback',
            'value' => true,
        ]);

        $form->setElement('text', 'update-unique-id', [
            'label' => 'Unique identifier',
            'value' => '', // value will be populated on first access
            'hidden' => true,
        ]);

        $this->addFormTranslations(
            ['de_DE' => [
                'update-code' => [
                    'label' => 'Aktionscode',
                ],
                'update-send-feedback' => [
                    'label' => 'Feedback senden',
                ],
                'update-channel' => [
                    'label' => 'Update Kanal',
                ],
            ]]
        );
    }
}
