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

use Shopware\Bundle\CartBundle\CartKey;
use Shopware\Bundle\CartBundle\CheckoutKey;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilder;
use Shopware\Components\BasketSignature\BasketPersister;
use Shopware\Components\BasketSignature\BasketSignatureGeneratorInterface;
use Shopware\Components\Random;

abstract class Shopware_Controllers_Frontend_Payment extends Enlight_Controller_Action
{
    /**
     * Returns the current payment short name.
     *
     * @return string|null
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
        return Shopware()->Container()->get('currency')->getShortName();
    }

    /**
     * Creates a unique payment id and returns it then.
     *
     * @return string
     */
    public function createPaymentUniqueId()
    {
        return Random::getAlphanumericString(32);
    }

    /**
     * Stores the final order and does some more actions accordingly.
     *
     * @param string $transactionId
     * @param string $paymentUniqueId
     * @param int    $paymentStatusId
     * @param bool   $sendStatusMail
     *
     * @return string|false
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
                Shopware()->Session()->get('sUserId'),
            ]);

        if (empty($orderNumber)) {
            $user = $this->getUser();
            $basket = $this->getBasket();
            if (!\is_array($basket)) {
                throw new RuntimeException('Cart must not be empty at this point');
            }

            $order = Shopware()->Modules()->Order();
            $order->sUserData = $user;
            $order->sComment = Shopware()->Session()->get('sComment');
            $order->sBasketData = $basket;
            $order->sAmount = $basket[CheckoutKey::AMOUNT];
            $order->sAmountWithTax = !empty($basket[CartKey::AMOUNT_WITH_TAX_NUMERIC]) ? $basket[CartKey::AMOUNT_WITH_TAX_NUMERIC] : $basket[CartKey::AMOUNT_NUMERIC];
            $order->sAmountNet = $basket[CartKey::AMOUNT_NET_NUMERIC];
            $order->sShippingcosts = $basket[CheckoutKey::SHIPPING_COSTS];
            $order->sShippingcostsNumeric = $basket[CheckoutKey::SHIPPING_COSTS_WITH_TAX];
            $order->sShippingcostsNumericNet = $basket[CheckoutKey::SHIPPING_COSTS_NET];
            $order->bookingId = $transactionId;
            $order->dispatchId = Shopware()->Session()->get('sDispatch');
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
        $orderId = (int) Shopware()->Db()->fetchOne($sql, [
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
        if (!\is_array($basket)) {
            return 0.0;
        }
        if (!empty($user['additional']['charge_vat'])) {
            return empty($basket[CartKey::AMOUNT_WITH_TAX_NUMERIC]) ? $basket[CartKey::AMOUNT_NUMERIC] : $basket[CartKey::AMOUNT_WITH_TAX_NUMERIC];
        }

        return $basket[CartKey::AMOUNT_NET_NUMERIC];
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
        if (!\is_array($basket)) {
            return 0.0;
        }
        if (!empty($user['additional']['charge_vat'])) {
            return $basket[CheckoutKey::SHIPPING_COSTS_WITH_TAX];
        }

        return (float) str_replace(',', '.', $basket[CheckoutKey::SHIPPING_COSTS]);
    }

    /**
     * Returns the full user data as array.
     *
     * @return array|null
     */
    public function getUser()
    {
        if (!empty(Shopware()->Session()->get('sOrderVariables')['sUserData'])) {
            return Shopware()->Session()->get('sOrderVariables')['sUserData'];
        }

        return null;
    }

    /**
     * Returns the full basket data as array.
     *
     * @return array|null
     */
    public function getBasket()
    {
        if (!empty(Shopware()->Session()->get('sOrderVariables')['sBasket'])) {
            return Shopware()->Session()->get('sOrderVariables')['sBasket'];
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getOrderNumber()
    {
        if (!empty(Shopware()->Session()->get('sOrderVariables')['sOrderNumber'])) {
            return Shopware()->Session()->get('sOrderVariables')['sOrderNumber'];
        }

        return null;
    }

    /**
     * Used by payment plugins
     *
     * @return string
     */
    protected function persistBasket()
    {
        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->get('session');
        $basket = $session->offsetGet('sOrderVariables')->getArrayCopy();
        $customerId = $session->offsetGet('sUserId');

        /** @var BasketSignatureGeneratorInterface $signatureGenerator */
        $signatureGenerator = $this->get('basket_signature_generator');
        $signature = $signatureGenerator->generateSignature(
            $basket['sBasket'],
            $customerId
        );

        /** @var BasketPersister $persister */
        $persister = $this->get('basket_persister');
        $persister->persist($signature, $basket);

        return $signature;
    }

    /**
     * Used by payment plugins
     *
     * Loads the persisted basket identified by the given signature.
     * Persisted basket will be removed from storage after loading.
     * Converted ArrayObject for shopware session is already created and stored in session for following checkout processes.
     *
     * @param string $signature
     *
     * @return ArrayObject
     */
    protected function loadBasketFromSignature($signature)
    {
        /** @var BasketPersister $persister */
        $persister = $this->get('basket_persister');
        $data = $persister->load($signature);

        if (!$data) {
            throw new RuntimeException(sprintf('Basket for signature %s not found', $signature));
        }

        $persister->delete($signature);

        $basket = new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS);
        $this->get('session')->offsetSet('sOrderVariables', $basket);

        return $basket;
    }

    /**
     * Used by payment plugins
     *
     * @param string $signature
     *
     * @throws RuntimeException if signature does not match with provided basket
     */
    protected function verifyBasketSignature($signature, ArrayObject $basket)
    {
        /** @var BasketSignatureGeneratorInterface $generator */
        $generator = $this->get('basket_signature_generator');

        $data = $basket->getArrayCopy();

        $newSignature = $generator->generateSignature(
            $data['sBasket'],
            $this->get('session')->get('sUserId')
        );

        if ($newSignature !== $signature) {
            throw new RuntimeException('The given signature is not equal to the generated signature of the saved basket');
        }
    }

    /**
     * Used by payment plugins
     *
     * @param string $paymentName
     * @param string $orderNumber
     * @param string $transactionNumber
     */
    protected function sendSignatureIsInvalidNotificationMail($paymentName, $orderNumber, $transactionNumber)
    {
        $content = <<<'EOD'
An invalid basket signature occurred during a customers checkout. Please verify the order.
Following information may help you to identify the problem:<br>
Payment method: %s. <br>
Order number: %s.<br>
Payment transaction number: %s.
EOD;

        $content = sprintf($content, $paymentName, $orderNumber, $transactionNumber);

        try {
            /** @var Enlight_Components_Mail $mail */
            $mail = $this->get('mail');
            $mail->addTo($this->get(\Shopware_Components_Config::class)->get('mail'));
            $mail->setSubject('An invalid basket signature occured');
            $mail->setBodyHtml($content);
            $mail->setAssociation(LogEntryBuilder::ORDER_NUMBER_ASSOCIATION, $orderNumber);
            $mail->send();
        } catch (Exception $e) {
        }

        /** @var \Shopware\Components\Logger $logger */
        $logger = $this->get('corelogger');
        $logger->log('error', $content);
    }
}
