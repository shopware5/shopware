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

namespace ShopwarePlugin\PaymentMethods\Components;

/**
 * Abstract class BasePaymentMethod
 * All PaymentMethod implementations should extend this class.
 * Current methods are legacy, more may be added in the future.
 */
abstract class BasePaymentMethod
{
    /**
     * Validates the input received from the client
     *
     * @param array $paymentData
     *
     * @return array List of fields containing errors
     */
    abstract public function validate($paymentData);

    /**
     * Called when the customer edits his payment data.
     * Creates/updates the payment information for the current
     * method.
     *
     * @param int                                 $userId  The user id
     * @param \Enlight_Controller_Request_Request $request The Request object
     */
    abstract public function savePaymentData($userId, \Enlight_Controller_Request_Request $request);

    /**
     * Fetches the customer's current payment data for this
     * payment method as array
     *
     * @param int $userId The user id
     *
     * @return array|null
     */
    abstract public function getCurrentPaymentDataAsArray($userId);

    /**
     * Creates the Payment Instance for the given order
     * based on the current Payment Method policy.
     *
     * @param int $orderId   The Order Id associated with the current payment
     * @param int $userId    The User/Customer Id associated with the current payment
     * @param int $paymentId The Payment Method Id associated with the current payment
     *
     * @return true|null
     */
    abstract public function createPaymentInstance($orderId, $userId, $paymentId);
}
