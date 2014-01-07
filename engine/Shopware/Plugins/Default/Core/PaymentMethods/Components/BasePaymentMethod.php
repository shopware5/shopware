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

namespace ShopwarePlugin\PaymentMethods\Components;

/**
 * Abstract class BasePaymentMethod
 * All PaymentMethod implementations should extend this class.
 * Current methods are legacy, more may be added in the future.
 *
 * @package ShopwarePlugin\PaymentMethods\Components
 */
abstract class BasePaymentMethod
{

    /**
     * Validates the input received from the client
     *
     * @return array List of fields containing errors
     */
    abstract public function validate();

    /**
     * Called when the customer edits his payment data.
     * Creates/updates the payment information for the current
     * method.
     *
     * @return null
     */
    abstract public function savePaymentData();

    /**
     * Fetches the customer's current payment data for this
     * payment method
     *
     * @return \Shopware\Models\Customer\PaymentData|null
     */
    abstract public function getCurrentPaymentData();

    /**
     * Creates the Payment Instance for the given order
     * based on the current Payment Method policy.
     *
     * @param $orderId The Order Id associated with the current payment
     * @param $userId The User/Customer Id associated with the current payment
     * @param $paymentId The Payment Method Id associated with the current payment
     * @return \Shopware\Models\Payment\PaymentInstace|null
     */
    abstract public function createPaymentInstance($orderId, $userId, $paymentId);

    /**
     * Deprecated call
     *
     * Will be removed in the future, please use the other functions
     */
    public function sInit()
    {
        return $this->validate();
    }

    /**
     * Deprecated call
     *
     * Will be removed in the future, please use the other functions
     */
    public function sUpdate()
    {
        return $this->savePaymentData();
    }

    /**
     * Deprecated call
     *
     * Will be removed in the future, please use the other functions
     */
    public function getData()
    {
        return $this->getCurrentPaymentData();
    }
}
