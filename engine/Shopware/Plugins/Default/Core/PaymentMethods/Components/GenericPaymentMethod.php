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

namespace ShopwarePlugin\ShopwarePaymentMethods\Components;

/**
 * Class GenericPaymentMethod
 * Used for all payment methods that require no specific logic
 *
 * @package ShopwarePlugin\ShopwarePaymentMethods\Components
 */
class GenericPaymentMethod extends BasePaymentMethod {
    public function sInit() {
        return array();
    }

    public function sUpdate() {
        //nothing to do, no return expected
        return;
    }

    public function sInsert($userId) {
        //nothing to do, boolean expected
        return true;
    }
    public function getData() {
        //nothing to do, array expected
        return array();
    }
}