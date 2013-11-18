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

namespace ShopwarePlugin\PaymentMethods\Components;

use Doctrine\ORM\AbstractQuery;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\PaymentData;
use Shopware\Models\Order\Order;
use Shopware\Models\Payment\PaymentInstance;

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
            $sErrorMessages[] = Shopware()->Snippets()->getNamespace('engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa')
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

        $lastPayment = $this->getData(AbstractQuery::HYDRATE_OBJECT);

        if (!$lastPayment) {
            $lastPayment = new PaymentData();
            $lastPayment->setCustomer(
                Shopware()->Models()->getRepository('\Shopware\Models\Customer\Customer')
                    ->find($userId)
            );
            $lastPayment->setPaymentMean(
                Shopware()->Models()->getRepository('\Shopware\Models\Payment\Payment')
                    ->findOneByName('Sepa')
            );
        }

        $lastPayment->setUseBillingData(Shopware()->Front()->Request()->getParam("sSepaUseBillingData")==='true');
        $lastPayment->setBankName(Shopware()->Front()->Request()->getParam("sSepaBankName"));
        $lastPayment->setIban(preg_replace('/\s+|\./', '', Shopware()->Front()->Request()->getParam("sSepaIban")));
        $lastPayment->setBic(Shopware()->Front()->Request()->getParam("sSepaBic"));

        Shopware()->Models()->persist($lastPayment);
        Shopware()->Models()->flush();
    }

    /**
     * @inheritdoc
     */
    public function getCurrentPaymentData()
    {
        $userId = Shopware()->Session()->sUserId;
        if (empty($userId)) {
            return;
        }

        return Shopware()->Models()->getRepository('\Shopware\Models\Customer\PaymentData')
            ->getCurrentPaymentDataQueryBuilder($userId, 'sepa')->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function createPaymentInstance($orderId, $userId, $paymentId)
    {
        $order = Shopware()->Models()->getRepository('Shopware\Models\Order\Order')->find($orderId);
        $user = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->find($userId);
        $paymentMean = Shopware()->Models()->getRepository('Shopware\Models\Payment\Payment')->find($paymentId);
        $paymentData = Shopware()->Models()->getRepository('Shopware\Models\Customer\PaymentData')
            ->getCurrentPaymentDataQueryBuilder($userId, 'sepa')->getQuery()->getOneOrNullResult();
        $addressData = $user->getBilling();

        $paymentInstance = new PaymentInstance();
        $paymentInstance->setOrder($order);
        $paymentInstance->setCustomer($user);
        $paymentInstance->setPaymentMean($paymentMean);

        $paymentInstance->setBankName($paymentData->getBankName());
        $paymentInstance->setBic($paymentData->getBic());
        $paymentInstance->setIban($paymentData->getIban());

        if ($paymentData->getUseBillingData()) {
            $paymentInstance->setFirstName($addressData->getFirstName());
            $paymentInstance->setLastName($addressData->getLastName());
            $paymentInstance->setAccountHolder($addressData->getFirstName() . ' ' . $addressData->getLastName());
            $paymentInstance->setAddress($addressData->getStreet() . ' ' . $addressData->getStreetNumber());
            $paymentInstance->setZipCode($addressData->getZipCode());
            $paymentInstance->setCity($addressData->getCity());
        }

        $paymentInstance->setAmount($order->getInvoiceAmount());

        Shopware()->Models()->persist($paymentInstance);
        Shopware()->Models()->flush();

        if (Shopware()->Config()->get('sepaSendEmail')) {
            $this->sendSepaEmail($order, $user, $paymentInstance);
        }

        return $paymentInstance;
    }

    private function sendSepaEmail(Order $order, Customer $user, PaymentInstance $paymentInstance)
    {
        require_once(Shopware()->OldPath() . "engine/Library/Mpdf/mpdf.php");

        $mail = Shopware()->TemplateMail()->createMail('sORDERSEPAAUTHORIZATION', array(
            'paymentInstance' => array(
                'firstName' => $paymentInstance->getFirstName(),
                'lastName' => $paymentInstance->getLastName(),
                'orderNumber' => $paymentInstance->getOrder()->getNumber(),
            )
        ));

        $mail->addTo($user->getEmail());

        Shopware()->Template()->assign('data', array(
            'orderNumber' => $paymentInstance->getOrder()->getNumber(),
            'accountHolder' => $paymentInstance->getAccountHolder(),
            'address' => $paymentInstance->getAddress(),
            'city' => $paymentInstance->getCity(),
            'zipCode' => $paymentInstance->getZipCode(),
            'bankName' => $paymentInstance->getBankName(),
            'iban' => $paymentInstance->getIban(),
            'bic' => $paymentInstance->getBic(),

        ));
        Shopware()->Template()->assign('config', array(
            'sepaCompany' => Shopware()->Config()->get('sepaCompany'),
            'sepaHeaderText' => Shopware()->Config()->get('sepaHeaderText'),
            'sepaSellerId' => Shopware()->Config()->get('sepaSellerId'),
        ));

        $data = Shopware()->Template()->fetch(__DIR__ . '/../Views/frontend/plugins/sepa/email.tpl');

        $mpdf = new \mPDF("utf-8", "A4", "", "");
        $mpdf->WriteHTML($data);
        $pdfFileContent = $mpdf->Output('', 'S');

        if (false === $pdfFileContent) {
            throw new \Enlight_Exception('Could not generate SEPA attachment file');
        }

        $attachmentName = 'SEPA_' . $order->getNumber();

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