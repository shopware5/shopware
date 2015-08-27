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

/**
 * Shopware Payment Plugin
 * todo@hl Remove
 */
class Shopware_Plugins_Frontend_Payment_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install()
    {
        $sql = '
            ALTER TABLE `s_core_paymentmeans` ADD `action` VARCHAR( 255 ) NULL ,
            ADD `pluginID` INT( 11 ) UNSIGNED NULL;
        ';
        try {
            Shopware()->Db()->exec($sql);
        } catch (Exception $e) {
        }

        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_Payments',
            'onInitResourcePayments'
        );
        return true;
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     * @return \Shopware_Models_PaymentManager
     */
    public static function onInitResourcePayments(Enlight_Event_EventArgs $args)
    {
        $resource = new Shopware_Models_PaymentManager();
        return $resource;
    }
}
