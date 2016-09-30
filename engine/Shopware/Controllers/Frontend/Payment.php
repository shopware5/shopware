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

use Shopware\Components\BasketSignature\Basket;
use Shopware\Components\BasketSignature\BasketPersister;
use Shopware\Components\BasketSignature\BasketSignatureGeneratorInterface;

/**
 * Shopware Payment Controller
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
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
        } else {
            return null;
        }
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
     * @param int $paymentStatusId
     * @param bool $sendStatusMail
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
        $orderNumber = Shopware()->Db()->fetchOne($sql, array(
                $transactionId,
                $paymentUniqueId,
                Shopware()->Session()->sUserId
            ));

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
     * Loads the persisted basket identified by the given signature.
     * Persisted basket will be removed from storage after loading.
     * Converted ArrayObject for shopware session is already created and stored in session for following checkout processes.
     *
     * @param string $signature
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
     * @param string $signature
     * @param ArrayObject $basket
     * @throws RuntimeException if signature does not match with provided basket
     */
    protected function verifyBasketSignature($signature, ArrayObject $basket)
    {
        /** @var BasketSignatureGeneratorInterface $generator */
        $generator = $this->get('basket_signature_generator');

        $data = $basket->getArrayCopy();

        $newSignature = $generator->generateSignature(
            Basket::createFromSBasket($data['sBasket']),
            $this->get('session')->get('sUserId')
        );

        if ($newSignature !== $signature) {
            throw new RuntimeException('The given signature is not equal to the generated signature of the saved basket');
        }
    }

    /**
     * @param int $paymentId
     * @param string $signature
     * @param string $orderNumber
     * @param string $transactionNumber
     */
    protected function sendSignatureIsInvalidNotificationMail($paymentId, $signature, $orderNumber, $transactionNumber)
    {
        /** @var Enlight_Components_Snippet_Namespace $snippets */
        $snippets = $this->get('snippets')->getNamespace('frontend/payment/error_mail');

        $subject = $snippets->get('InvalidBasketSignatureSubject');

        /** @var \Doctrine\DBAL\Connection $dbalConnection */
        $dbalConnection = $this->get('dbal_connection');
        $query = $dbalConnection->createQueryBuilder();

        $query->select('payment.name, payment.description')
            ->from('s_core_paymentmeans', 'payment')
            ->where('payment.id = :paymentId')
            ->setParameter('paymentId', $paymentId);

        $payment = $query->execute()->fetch();

        $content = [];
        $content[] = $snippets->get('InvalidBasketSignatureContent');
        $content[] = sprintf($snippets->get('InvalidBasketSignaturePaymentMethod'), $payment['name'], $paymentId, $payment['description']);
        $content[] = $snippets->get('InvalidBasketSignature') . $signature;
        $content[] = $snippets->get('InvalidBasketSignatureOrderNumber'). $orderNumber;
        $content[] = $snippets->get('InvalidBasketSignatureTransactionNumber'). $transactionNumber;

        $content = implode("<br>", $content);

        /** @var Enlight_Components_Mail $mail */
        $mail = $this->get('mail');
        $mail->addTo($this->get('config')->get('mail'));
        $mail->setSubject($subject);
        $mail->setBodyHtml($content);
        $mail->send();
    }

    /**
     * Saves the payment status an sends and possibly sends a status email.
     *
     * @param string $transactionId
     * @param string $paymentUniqueId
     * @param int $paymentStatusId
     * @param bool $sendStatusMail
     * @return void
     */
    public function savePaymentStatus($transactionId, $paymentUniqueId, $paymentStatusId, $sendStatusMail = false)
    {
        $sql = '
            SELECT id FROM s_order
            WHERE transactionID=? AND temporaryID=?
            AND status!=-1
        ';
        $orderId = Shopware()->Db()->fetchOne($sql, array(
                $transactionId,
                $paymentUniqueId
            ));
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
        } else {
            return $basket['AmountNetNumeric'];
        }
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
        } else {
            return str_replace(',', '.', $basket['sShippingcosts']);
        }
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
        } else {
            return null;
        }
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
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public function getOrderNumber()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sOrderNumber'])) {
            return Shopware()->Session()->sOrderVariables['sOrderNumber'];
        } else {
            return null;
        }
    }
}
