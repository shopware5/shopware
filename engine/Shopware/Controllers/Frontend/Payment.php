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
 * Shopware Payment Controller
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
abstract class Shopware_Controllers_Frontend_Payment extends Enlight_Controller_Action
{
    /**
     * Returns the current payment short name.
     *
     * @return string
     */
    public function getPaymentShortName()
    {
        if (($user = $this->getUser()) !== null
                && !empty($user['additional']['payment']['name'])) {
            return $user['additional']['payment']['name'];
        }

        return null;
    }

    /**
     * Returns the current currency short name.
     *
     * @return string
     */
    public function getCurrencyShortName()
    {
        return Shopware()->Currency()->getShortName();
    }

    /**
     * Creates a unique payment id and returns it then.
     *
     * @return string
     */
    public function createPaymentUniqueId()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * Stores the final order and does some more actions accordingly.
     *
     * @param string $transactionId
     * @param string $paymentUniqueId
     * @param int    $paymentStatusId
     * @param bool   $sendStatusMail
     *
     * @return int
     */
    public function saveOrder($transactionId, $paymentUniqueId, $paymentStatusId = null, $sendStatusMail = false)
    {
        if (empty($transactionId) || empty($paymentUniqueId)) {
            return false;
        }

        $sql = '
            SELECT ordernumber FROM s_order
            WHERE transactionID=? AND temporaryID=?
            AND status!=-1 AND userID=?
        ';
        $orderNumber = Shopware()->Db()->fetchOne($sql, [
                $transactionId,
                $paymentUniqueId,
                Shopware()->Session()->sUserId,
            ]);

        if (empty($orderNumber)) {
            $user = $this->getUser();
            $basket = $this->getBasket();

            $order = Shopware()->Modules()->Order();
            $order->sUserData = $user;
            $order->sComment = Shopware()->Session()->sComment;
            $order->sBasketData = $basket;
            $order->sAmount = $basket['sAmount'];
            $order->sAmountWithTax = !empty($basket['AmountWithTaxNumeric']) ? $basket['AmountWithTaxNumeric'] : $basket['AmountNumeric'];
            $order->sAmountNet = $basket['AmountNetNumeric'];
            $order->sShippingcosts = $basket['sShippingcosts'];
            $order->sShippingcostsNumeric = $basket['sShippingcostsWithTax'];
            $order->sShippingcostsNumericNet = $basket['sShippingcostsNet'];
            $order->bookingId = $transactionId;
            $order->dispatchId = Shopware()->Session()->sDispatch;
            $order->sNet = empty($user['additional']['charge_vat']);
            $order->uniqueID = $paymentUniqueId;
            $order->deviceType = $this->Request()->getDeviceType();
            $orderNumber = $order->sSaveOrder();
        }

        if (!empty($orderNumber) && !empty($paymentStatusId)) {
            $this->savePaymentStatus($transactionId, $paymentUniqueId, $paymentStatusId, $sendStatusMail);
        }

        return $orderNumber;
    }

    /**
     * Saves the payment status an sends and possibly sends a status email.
     *
     * @param string $transactionId
     * @param string $paymentUniqueId
     * @param int    $paymentStatusId
     * @param bool   $sendStatusMail
     */
    public function savePaymentStatus($transactionId, $paymentUniqueId, $paymentStatusId, $sendStatusMail = false)
    {
        $sql = '
            SELECT id FROM s_order
            WHERE transactionID=? AND temporaryID=?
            AND status!=-1
        ';
        $orderId = Shopware()->Db()->fetchOne($sql, [
                $transactionId,
                $paymentUniqueId,
            ]);
        $order = Shopware()->Modules()->Order();
        $order->setPaymentStatus($orderId, $paymentStatusId, $sendStatusMail);
    }

    /**
     * Return the full amount to pay.
     *
     * @return float
     */
    public function getAmount()
    {
        $user = $this->getUser();
        $basket = $this->getBasket();
        if (!empty($user['additional']['charge_vat'])) {
            return empty($basket['AmountWithTaxNumeric']) ? $basket['AmountNumeric'] : $basket['AmountWithTaxNumeric'];
        }

        return $basket['AmountNetNumeric'];
    }

    /**
     * Returns shipment amount as float
     *
     * @return float
     */
    public function getShipment()
    {
        $user = $this->getUser();
        $basket = $this->getBasket();
        if (!empty($user['additional']['charge_vat'])) {
            return $basket['sShippingcostsWithTax'];
        }

        return str_replace(',', '.', $basket['sShippingcosts']);
    }

    /**
     * Returns the full user data as array.
     *
     * @return array
     */
    public function getUser()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            return Shopware()->Session()->sOrderVariables['sUserData'];
        }

        return null;
    }

    /**
     * Returns the full basket data as array.
     *
     * @return array
     */
    public function getBasket()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sBasket'])) {
            return Shopware()->Session()->sOrderVariables['sBasket'];
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getOrderNumber()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sOrderNumber'])) {
            return Shopware()->Session()->sOrderVariables['sOrderNumber'];
        }

        return null;
    }
}
