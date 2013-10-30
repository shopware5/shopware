<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

/**
 * @category  Shopware
 * @package   Shopware\Plugins\CorePaymentMethods
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Core_PaymentMethods_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0-dev';
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return array(
            'install' => false,
            'enable'  => false,
            'update'  => true
        );
    }

    /**
     * Standard plugin install method to register all required components.
     *
     * @return bool success
     */
    public function install()
    {
	    $this->subscribeEvents();
	    $this->addSnippets();

        return true;
    }

	/**
	 * Registers all necessary events and hooks.
	 */
	 private function subscribeEvents()
	 {
		$this->subscribeEvent(
			'Shopware_Modules_Admin_InitiatePaymentClass_AddDirs',
			'addPaymentClass'
		);

         $this->subscribeEvent(
             'Enlight_Controller_Action_PostDispatch',
             'addPaths'
         );
	 }

    /**
     * This method registers shopware's generic payment method handler
     * and the debit payment method handler
     *
     * @param Enlight_Event_EventArgs $args
     * @return array
     */

    public function addPaymentClass(\Enlight_Event_EventArgs $args)
    {
        $dirs = $args->getReturn();

        $this->Application()->Loader()->registerNamespace('ShopwarePlugin\ShopwarePaymentMethods\Components', __DIR__.'/Components/');

        $dirs['debit'] = 'ShopwarePlugin\ShopwarePaymentMethods\Components\DebitPaymentMethod';
        $dirs['default'] = 'ShopwarePlugin\ShopwarePaymentMethods\Components\GenericPaymentMethod';

        return $dirs;
    }

    /**
     * Add View path to Smarty
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return mixed
     */
    public function addPaths(Enlight_Event_EventArgs $arguments)
    {
        // Add templates folder
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/', 'payment', Enlight_Template_Manager::POSITION_APPEND
        );
    }

    private function addSnippets()
    {
        $sql= "
            INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`)
            SELECT NULL, 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/debit', `shopID`, `localeID`, `name`, `value`, '2013-11-01 00:00:00', '2013-11-01 00:00:00'
            FROM `s_core_snippets`
            WHERE `s_core_snippets`.`namespace` LIKE 'frontend/plugins/payment/debit';

            INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
            (NULL, 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa', 1, 1, 'PaymentDebitLabelIban', 'IBAN', '2013-11-01 00:00:00', '2013-11-01 00:00:00'),
            (NULL, 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa', 1, 2, 'PaymentDebitLabelIban', 'IBAN', '2013-11-01 00:00:00', '2013-11-01 00:00:00'),
            (NULL, 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa', 1, 1, 'PaymentDebitLabelBic', 'BIC', '2013-11-01 00:00:00', '2013-11-01 00:00:00'),
            (NULL, 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa', 1, 2, 'PaymentDebitLabelBic', 'BIC', '2013-11-01 00:00:00', '2013-11-01 00:00:00');

            INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`)
            SELECT NULL, 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa', `shopID`, `localeID`, `name`, `value`, '2013-11-01 00:00:00', '2013-11-01 00:00:00'
            FROM `s_core_snippets`
            WHERE `s_core_snippets`.`name` IN ('PaymentDebitLabelBankname', 'PaymentDebitLabelName', 'PaymentDebitInfoFields') AND `s_core_snippets`.`namespace` LIKE 'frontend/plugins/payment/debit';
        ";
        Shopware()->Db()->query($sql);
    }
}
