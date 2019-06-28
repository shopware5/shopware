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

class Shopware_Plugins_Core_PaymentMethods_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.1';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Payment Methods';
    }

    public function getInfo()
    {
        return [
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'name' => $this->getLabel(),
            'description' => 'Shopware Payment Methods handling. This plugin is required to handle payment methods, and should not be deactivated.',
        ];
    }

    public function getCapabilities()
    {
        return [
            'install' => false,
            'update' => false,
            'enable' => false,
            'secureUninstall' => false,
        ];
    }

    /**
     * Standard plugin install method to register all required components.
     *
     * @return bool success
     */
    public function install()
    {
        $this->subscribeEvents();

        return true;
    }

    /**
     * Standard plugin update method to register all required components.
     *
     * @param string $version
     *
     * @return bool success
     */
    public function update($version)
    {
        return true;
    }

    /**
     * This method registers shopware's generic payment method handler
     * and the debit payment method handler
     *
     * @param Enlight_Event_EventArgs $args
     *
     * @return array
     */
    public function addPaymentClass(\Enlight_Event_EventArgs $args)
    {
        $dirs = $args->getReturn();

        $this->Application()->Loader()->registerNamespace('ShopwarePlugin\PaymentMethods\Components', __DIR__ . '/Components/');

        $dirs['debit'] = 'ShopwarePlugin\PaymentMethods\Components\DebitPaymentMethod';
        $dirs['sepa'] = 'ShopwarePlugin\PaymentMethods\Components\SepaPaymentMethod';
        $dirs['default'] = 'ShopwarePlugin\PaymentMethods\Components\GenericPaymentMethod';

        return $dirs;
    }

    /**
     * Add View path to Smarty
     */
    public function addPaths(Enlight_Event_EventArgs $arguments)
    {
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/responsive/',
            'payment',
            Enlight_Template_Manager::POSITION_APPEND
        );
    }

    /**
     * Called when the BackendOrderPostDispatch Event is triggered
     */
    public function onBackendOrderPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        //if the controller action name equals "load" we have to load all application components
        if ($args->getRequest()->getActionName() === 'load') {
            $view->addTemplateDir($this->Path() . 'Views/emotion/');
            $view->extendsTemplate(
                'backend/order/payment_methods/controller/detail.js'
            );
            $view->extendsTemplate(
                'backend/order/payment_methods/view/detail/payment_methods.js'
            );
        }
    }

    /**
     * Called when the BackendCustomerPostDispatch Event is triggered
     */
    public function onBackendCustomerPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        // If the controller action name equals "load" we have to load all application components
        if ($args->getRequest()->getActionName() === 'load') {
            $view->addTemplateDir($this->Path() . 'Views/emotion/');

            $view->extendsTemplate(
                'backend/customer/payment_methods/controller/detail.js'
            );
            $view->extendsTemplate(
                'backend/customer/payment_methods/view/detail/payment_methods.js'
            );
        }
    }

    /**
     * Registers all necessary events and hooks.
     */
    private function subscribeEvents()
    {
        $this->subscribeEvent(
            'Shopware_Modules_Admin_InitiatePaymentClass_AddClass',
            'addPaymentClass'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatchSecure',
            'addPaths'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order',
            'onBackendOrderPostDispatch'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Customer',
            'onBackendCustomerPostDispatch'
        );
    }
}
