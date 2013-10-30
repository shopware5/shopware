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
 * @Deprecated: Wrapper for legacy debit.php class. Will be refactored in the future.
 *
 * Class DebitPaymentMethod
 * Used to handle debit payment
 *
 * @package ShopwarePlugin\ShopwarePaymentMethods\Components
 */
class DebitPaymentMethod extends GenericPaymentMethod {
    private $legacyImplementation;

    public function __construct() {
        include_once(Shopware()->OldPath() . 'engine/core/class/paymentmeans/debit.php');
        $this->legacyImplementation = new \sPaymentMean();

        if (!$this->legacyImplementation){
            throw new \RuntimeException('PaymentMethod Plugin: trying to instantiate DebitPaymentMethod class for non-existing debit implementation.');
        } else {
            $this->legacyImplementation->sSYSTEM = &Shopware()->Modules()->Admin()->sSYSTEM;
        }
    }

    /**
     * __call won't work, as method_exists is being used in core classes,
     * so we need to wrap all the methods
     */
    public function sInit() {
        return $this->legacyImplementation->sInit();
    }

    public function sUpdate() {
        return $this->legacyImplementation->sUpdate();
    }

    function sInsert($userId) {
        return $this->legacyImplementation->sInsert($userId);
    }

    function getData() {
        return $this->legacyImplementation->getData();
    }
}