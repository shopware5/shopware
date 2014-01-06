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

use Doctrine\ORM\AbstractQuery;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\PaymentData;
use Shopware\Models\Order\Order;
use Shopware\Models\Payment\PaymentInstance;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class SepaPaymentMethod
 * Used to handle SEPA payment
 *
 * @package ShopwarePlugin\PaymentMethods\Components
 */
class SepaPaymentMethod extends GenericPaymentMethod
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        $sErrorFlag = array();

        if (!Shopware()->Front()->Request()->getParam("sSepaIban") || strlen(trim(Shopware()->Front()->Request()->getParam("sSepaIban"))) === 0 ) {
            $sErrorFlag["sSepaIban"] = true;
        }
        if (Shopware()->Config()->sepaShowBic && Shopware()->Config()->sepaRequireBic && (!Shopware()->Front()->Request()->getParam("sSepaBic") || strlen(trim(Shopware()->Front()->Request()->getParam("sSepaBic"))) === 0 )) {
            $sErrorFlag["sSepaBic"] = true;
        }
        if (Shopware()->Config()->sepaShowBankName && Shopware()->Config()->sepaRequireBankName && (!Shopware()->Front()->Request()->getParam("sSepaBankName") || strlen(trim(Shopware()->Front()->Request()->getParam("sSepaBankName"))) === 0 )) {
            $sErrorFlag["sSepaBankName"] = true;
        }
        if (Shopware()->Front()->Request()->getParam("sSepaIban") && !$this->validateIBAN(Shopware()->Front()->Request()->getParam("sSepaIban"))) {
            $sErrorMessages[] = Shopware()->Snippets()->getNamespace('frontend/plugins/payment/sepa')
                ->get('ErrorIBAN', 'Invalid IBAN');
        }

        if (count($sErrorFlag)) {
            $sErrorMessages[] = Shopware()->Snippets()->getNamespace('frontend/account/internalMessages')->get('ErrorFillIn', 'Please fill in all red fields');
        }

        if (count($sErrorMessages)) {
            return array(
                "sErrorFlag" => $sErrorFlag,
                "sErrorMessages" => $sErrorMessages
            );
        } else {
            return array();
        }
    }

    private function validateIBAN($value)
    {
        if (null === $value || '' === $value) {
            return false;
        }

        $teststring = preg_replace('/\s+|\./', '', $value);

        if (strlen($teststring) < 4) {
            return false;
        }

        $teststring = substr($teststring, 4)
            . strval(ord($teststring{0}) - 55)
            . strval(ord($teststring{1}) - 55)
            . substr($teststring, 2, 2);

        $teststring = preg_replace_callback('/[A-Za-z]/', function ($letter) {
            return intval(ord(strtolower($letter[0])) - 87);
        }, $teststring);

        $rest = 0;
        $strlen = strlen($teststring);
        for ($pos = 0; $pos < $strlen; $pos += 7) {
            $part = strval($rest) . substr($teststring, $pos, 7);
            $rest = intval($part) % 97;
        }

        if ($rest != 1) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function savePaymentData()
    {
        $userId = Shopware()->Session()->sUserId;
        if (empty($userId)) {
            return;
        }

        $lastPayment = $this->getCurrentPaymentDataAsArray();

        $paymentMean = Shopware()->Models()->getRepository('\Shopware\Models\Payment\Payment')->
            getPaymentsQuery(array('name' => 'Sepa'))->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $data = array(
            (Shopware()->Front()->Request()->getParam("sSepaUseBillingData")==='true'?1:0),
            Shopware()->Front()->Request()->getParam("sSepaBankName"),
            preg_replace('/\s+|\./', '', Shopware()->Front()->Request()->getParam("sSepaIban")),
            Shopware()->Front()->Request()->getParam("sSepaBic"),
            $paymentMean['id'],
            $userId
        );

        if (!$lastPayment) {
            $date = new \DateTime();
            $data[] = $date->format('Y-m-d');
            Shopware()->Db()->query("
            INSERT INTO s_core_payment_data (use_billing_data, bankname, iban, bic, payment_mean_id, user_id, created_at)
            VALUES (?,?,?,?,?,?,?)",
                $data);
        } else {
            Shopware()->Db()->query("
            UPDATE s_core_payment_data SET use_billing_data = ?, bankname = ?, iban = ?, bic = ?
            WHERE payment_mean_id = ? AND user_id = ?",
                $data);
        }
    }

    /**
     * @inheritdoc
     */
    public function getCurrentPaymentDataAsArray()
    {
        $userId = Shopware()->Session()->sUserId;
        if (empty($userId)) {
            return;
        }

        $paymentData = Shopware()->Models()->getRepository('\Shopware\Models\Customer\PaymentData')
            ->getCurrentPaymentDataQueryBuilder($userId, 'sepa')->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if(isset($paymentData)) {
            $arrayData = array(
                "sSepaUseBillingData" => $paymentData['useBillingData'],
                "sSepaBankName" => $paymentData['bankName'],
                "sSepaIban" => $paymentData['iban'],
                "sSepaBic" =>  $paymentData['bic']
            );

            return $arrayData;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function createPaymentInstance($orderId, $userId, $paymentId)
    {
        $order = Shopware()->Models()->createQueryBuilder()
            ->select(array('orders.invoiceAmount', 'orders.number'))
            ->from('Shopware\Models\Order\Order', 'orders')
            ->where('orders.id = ?1')
            ->setParameter(1, $orderId)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $addressData = Shopware()->Models()->getRepository('Shopware\Models\Customer\Billing')->
            getUserBillingQuery($userId)->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        $paymentData = $this->getCurrentPaymentDataAsArray();

        $query = "INSERT INTO  s_core_payment_instance (
            payment_mean_id ,
            order_id ,
            user_id ,

            firstname ,
            lastname ,
            address ,
            zipcode ,
            city ,

            bank_name ,
            account_holder ,
            bic ,
            iban ,

            amount ,
            created_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $date = new \DateTime();
        $data = array(
            'payment_mean_id' => $paymentId,
            'order_id' => $orderId,
            'user_id' => $userId,

            'firstname' => $paymentData['sSepaUseBillingData']?$addressData['firstName']:null,
            'lastname' => $paymentData['sSepaUseBillingData']?$addressData['lastName']:null,
            'address' => $paymentData['sSepaUseBillingData']?($addressData['street'] . ' ' . $addressData['streetNumber']):null,
            'zipcode' => $paymentData['sSepaUseBillingData']?$addressData['zipCode']:null,
            'city' => $paymentData['sSepaUseBillingData']?$addressData['city']:null,

            'bank_name' => $paymentData['sSepaBankName'],
            'account_holder' => $paymentData['sSepaUseBillingData']?($addressData['firstName'] . ' ' . $addressData['lastName']):null,
            'bic' => $paymentData['sSepaBic'],
            'iban' => $paymentData['sSepaIban'],

            'amount' => $order['invoiceAmount'],
            'created_at' => $date->format('Y-m-d')
        );

        Shopware()->Db()->query($query, array_values($data));

        if (Shopware()->Config()->get('sepaSendEmail')) {
            $this->sendSepaEmail($order['number'], $userId, $data);
        }

        return true;


//        $order = Shopware()->Models()->getRepository('Shopware\Models\Order\Order')->find($orderId);
//        $user = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->find($userId);
//        $paymentMean = Shopware()->Models()->getRepository('Shopware\Models\Payment\Payment')->find($paymentId);
//        $paymentData = Shopware()->Models()->getRepository('Shopware\Models\Customer\PaymentData')
//            ->getCurrentPaymentDataQueryBuilder($userId, 'sepa')->getQuery()->getOneOrNullResult();
//        $addressData = $user->getBilling();
//
//        $paymentInstance = new PaymentInstance();
//        $paymentInstance->setOrder($order);
//        $paymentInstance->setCustomer($user);
//        $paymentInstance->setPaymentMean($paymentMean);
//
//        $paymentInstance->setBankName($paymentData->getBankName());
//        $paymentInstance->setBic($paymentData->getBic());
//        $paymentInstance->setIban($paymentData->getIban());
//
//        if ($paymentData->getUseBillingData()) {
//            $paymentInstance->setFirstName($addressData->getFirstName());
//            $paymentInstance->setLastName($addressData->getLastName());
//            $paymentInstance->setAccountHolder($addressData->getFirstName() . ' ' . $addressData->getLastName());
//            $paymentInstance->setAddress($addressData->getStreet() . ' ' . $addressData->getStreetNumber());
//            $paymentInstance->setZipCode($addressData->getZipCode());
//            $paymentInstance->setCity($addressData->getCity());
//        }
//
//        $paymentInstance->setAmount($order->getInvoiceAmount());
//
//        Shopware()->Models()->persist($paymentInstance);
//        Shopware()->Models()->flush();
//
//        if (Shopware()->Config()->get('sepaSendEmail')) {
//            $this->sendSepaEmail($order, $user, $paymentInstance);
//        }
//
//        return $paymentInstance;
    }

    private function sendSepaEmail($orderNumber, $userId, $data)
    {
        require_once(Shopware()->OldPath() . "engine/Library/Mpdf/mpdf.php");

        $mail = Shopware()->TemplateMail()->createMail('sORDERSEPAAUTHORIZATION', array(
            'paymentInstance' => array(
                'firstName' => $data['firstname'],
                'lastName' => $data['lastname'],
                'orderNumber' => $orderNumber
            )
        ));

        $customerEmail = Shopware()->Models()->createQueryBuilder()
            ->select('customer.email')
            ->from('Shopware\Models\Customer\Customer', 'customer')
            ->where('customer.id = ?1')
            ->setParameter(1, $userId)
            ->getQuery()
            ->getSingleScalarResult();

        $mail->addTo($customerEmail);

        Shopware()->Template()->assign('data', array(
            'orderNumber' => $orderNumber,
            'accountHolder' => $data['account_holder'],
            'address' => $data['address'],
            'city' => $data['city'],
            'zipCode' => $data['zipcode'],
            'bankName' => $data['bank_name'],
            'iban' => $data['iban'],
            'bic' => $data['bic']
        ));
        Shopware()->Template()->assign('config', array(
            'sepaCompany' => Shopware()->Config()->get('sepaCompany'),
            'sepaHeaderText' => Shopware()->Config()->get('sepaHeaderText'),
            'sepaSellerId' => Shopware()->Config()->get('sepaSellerId')
        ));

        $data = Shopware()->Template()->fetch(__DIR__ . '/../Views/frontend/plugins/sepa/email.tpl');

        $mpdf = new \mPDF("utf-8", "A4", "", "");
        $mpdf->WriteHTML($data);
        $pdfFileContent = $mpdf->Output('', 'S');

        if (false === $pdfFileContent) {
            throw new \Enlight_Exception('Could not generate SEPA attachment file');
        }

        $attachmentName = 'SEPA_' . $orderNumber;

        $mail->createAttachment(
            $pdfFileContent,
            'application/pdf',
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $attachmentName . ".pdf"
        );

        try {
            $mail->send();
        } catch (\Exception $e) {
            //TODO: Handle email sending failure
        }
    }
}
