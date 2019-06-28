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

use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilder;

/**
 * Used to handle SEPA payment
 */
class SepaPaymentMethod extends GenericPaymentMethod
{
    /**
     * {@inheritdoc}
     */
    public function validate($paymentData)
    {
        $sErrorFlag = [];
        $sErrorMessages = [];

        if (!$paymentData['sSepaIban'] || strlen(trim($paymentData['sSepaIban'])) === 0) {
            $sErrorFlag['sSepaIban'] = true;
        }
        if (Shopware()->Config()->sepaShowBic && Shopware()->Config()->sepaRequireBic && (!$paymentData['sSepaBic'] || strlen(trim($paymentData['sSepaBic'])) === 0)) {
            $sErrorFlag['sSepaBic'] = true;
        }
        if (Shopware()->Config()->sepaShowBankName && Shopware()->Config()->sepaRequireBankName && (!$paymentData['sSepaBankName'] || strlen(trim($paymentData['sSepaBankName'])) === 0)) {
            $sErrorFlag['sSepaBankName'] = true;
        }

        $sErrorFlag = Shopware()->Container()->get('events')->filter('Sepa_Payment_Method_Validate_Data_Required', $sErrorFlag, [
            'subject' => $this,
            'paymentData' => $paymentData,
        ]);

        if (count($sErrorFlag)) {
            $sErrorMessages[] = Shopware()->Snippets()->getNamespace('frontend/account/internalMessages')->get('ErrorFillIn', 'Please fill in all red fields');
        }

        if ($paymentData['sSepaIban'] && !$this->validateIBAN($paymentData['sSepaIban'])) {
            $sErrorMessages[] = Shopware()->Snippets()->getNamespace('frontend/plugins/payment/sepa')->get('ErrorIBAN', 'Invalid IBAN');
            $sErrorFlag['sSepaIban'] = true;
        }

        if (count($sErrorMessages)) {
            return [
                'sErrorFlag' => $sErrorFlag,
                'sErrorMessages' => $sErrorMessages,
            ];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function savePaymentData($userId, \Enlight_Controller_Request_Request $request)
    {
        $lastPayment = $this->getCurrentPaymentDataAsArray($userId);

        $paymentMean = Shopware()->Models()->getRepository(\Shopware\Models\Payment\Payment::class)->
        getAllPaymentsQuery(['name' => 'Sepa'])->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $data = [
            'use_billing_data' => ($request->getParam('sSepaUseBillingData') === 'true' ? 1 : 0),
            'bankname' => $request->getParam('sSepaBankName'),
            'iban' => preg_replace('/\s+|\./', '', $request->getParam('sSepaIban')),
            'bic' => $request->getParam('sSepaBic'),
        ];

        $data = Shopware()->Container()->get('events')->filter('Sepa_Payment_Method_Save_Payment_Data', $data, [
            'subject' => $this,
            'params' => $request->getParams(),
        ]);

        if (!$lastPayment) {
            $date = new \DateTime();
            $data['created_at'] = $date->format('Y-m-d');
            $data['payment_mean_id'] = $paymentMean['id'];
            $data['user_id'] = $userId;
            Shopware()->Db()->insert('s_core_payment_data', $data);
        } else {
            $where = [
                'payment_mean_id = ?' => $paymentMean['id'],
                'user_id = ?' => $userId,
            ];

            Shopware()->Db()->update('s_core_payment_data', $data, $where);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPaymentDataAsArray($userId)
    {
        $paymentData = Shopware()->Models()->getRepository('\Shopware\Models\Customer\PaymentData')
            ->getCurrentPaymentDataQueryBuilder($userId, 'sepa')->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if (isset($paymentData)) {
            $arrayData = [
                'sSepaUseBillingData' => $paymentData['useBillingData'],
                'sSepaBankName' => $paymentData['bankName'],
                'sSepaIban' => $paymentData['iban'],
                'sSepaBic' => $paymentData['bic'],
            ];

            $arrayData = Shopware()->Container()->get('events')->filter('Sepa_Payment_Method_Current_Payment_Data_Array', $arrayData, [
                'subject' => $this,
                'paymentData' => $paymentData,
            ]);

            return $arrayData;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function createPaymentInstance($orderId, $userId, $paymentId)
    {
        $order = Shopware()->Models()->createQueryBuilder()
            ->select(['orders.invoiceAmount', 'orders.number'])
            ->from('Shopware\Models\Order\Order', 'orders')
            ->where('orders.id = ?1')
            ->setParameter(1, $orderId)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $addressData = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')
            ->find($userId)->getDefaultBillingAddress();
        $paymentData = $this->getCurrentPaymentDataAsArray($userId);

        $date = new \DateTime();
        $data = [
            'payment_mean_id' => $paymentId,
            'order_id' => $orderId,
            'user_id' => $userId,

            'firstname' => $paymentData['sSepaUseBillingData'] ? $addressData->getFirstname() : null,
            'lastname' => $paymentData['sSepaUseBillingData'] ? $addressData->getLastname() : null,
            'address' => $paymentData['sSepaUseBillingData'] ? $addressData->getStreet() : null,
            'zipcode' => $paymentData['sSepaUseBillingData'] ? $addressData->getZipcode() : null,
            'city' => $paymentData['sSepaUseBillingData'] ? $addressData->getCity() : null,

            'bank_name' => $paymentData['sSepaBankName'],
            'account_holder' => $paymentData['sSepaUseBillingData'] ? ($addressData->getFirstname() . ' ' . $addressData->getLastname()) : null,
            'bic' => $paymentData['sSepaBic'],
            'iban' => $paymentData['sSepaIban'],

            'amount' => $order['invoiceAmount'],
            'created_at' => $date->format('Y-m-d'),
        ];

        $data = Shopware()->Container()->get('events')->filter('Sepa_Payment_Method_Create_Payment_Instance_Data', $data, [
            'subject' => $this,
            'paymentData' => $paymentData,
        ]);

        Shopware()->Db()->insert('s_core_payment_instance', $data);

        if (Shopware()->Config()->get('sepaSendEmail')) {
            $this->sendSepaEmail($order['number'], $userId, $data);
        }

        return true;
    }

    private function validateIBAN($value)
    {
        if ($value === null || $value === '') {
            return false;
        }

        $teststring = preg_replace('/\s+|\./', '', $value);

        if (strlen($teststring) < 4) {
            return false;
        }

        $teststring = substr($teststring, 4)
            . (string) (ord($teststring[0]) - 55)
            . (string) (ord($teststring[1]) - 55)
            . substr($teststring, 2, 2);

        $teststring = preg_replace_callback('/[A-Za-z]/', function ($letter) {
            return (int) (ord(strtolower($letter[0])) - 87);
        }, $teststring);

        $rest = 0;
        $strlen = strlen($teststring);
        for ($pos = 0; $pos < $strlen; $pos += 7) {
            $part = (string) $rest . substr($teststring, $pos, 7);
            $rest = (int) $part % 97;
        }

        if ($rest != 1) {
            return false;
        }

        return true;
    }

    private function sendSepaEmail($orderNumber, $userId, $data)
    {
        $mail = Shopware()->TemplateMail()->createMail('sORDERSEPAAUTHORIZATION', [
            'paymentInstance' => [
                'firstName' => $data['firstname'],
                'lastName' => $data['lastname'],
                'orderNumber' => $orderNumber,
            ],
        ]);

        $customerEmail = Shopware()->Models()->createQueryBuilder()
            ->select('customer.email')
            ->from('Shopware\Models\Customer\Customer', 'customer')
            ->where('customer.id = ?1')
            ->setParameter(1, $userId)
            ->getQuery()
            ->getSingleScalarResult();

        $mail->addTo($customerEmail);

        Shopware()->Template()->assign('data', [
            'orderNumber' => $orderNumber,
            'accountHolder' => $data['account_holder'],
            'address' => $data['address'],
            'city' => $data['city'],
            'zipCode' => $data['zipcode'],
            'bankName' => $data['bank_name'],
            'iban' => $data['iban'],
            'bic' => $data['bic'],
        ]);
        Shopware()->Template()->assign('config', [
            'sepaCompany' => Shopware()->Config()->get('sepaCompany'),
            'sepaHeaderText' => Shopware()->Config()->get('sepaHeaderText'),
            'sepaSellerId' => Shopware()->Config()->get('sepaSellerId'),
        ]);

        Shopware()->Template()->addTemplateDir(__DIR__ . '/../Views/');
        $data = Shopware()->Template()->fetch('frontend/plugins/sepa/email.tpl');

        $mpdfConfig = Shopware()->Container()->getParameter('shopware.mpdf.defaultConfig');
        $mpdf = new \Mpdf\Mpdf($mpdfConfig);
        $mpdf->WriteHTML($data);
        $pdfFileContent = $mpdf->Output('', 'S');

        if ($pdfFileContent === false) {
            throw new \Enlight_Exception('Could not generate SEPA attachment file');
        }

        $attachmentName = 'SEPA_' . $orderNumber;

        $mail->createAttachment(
            $pdfFileContent,
            'application/pdf',
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $attachmentName . '.pdf'
        );

        $mail->setAssociation(LogEntryBuilder::ORDER_NUMBER_ASSOCIATION, $orderNumber);

        try {
            $mail->send();
        } catch (\Exception $e) {
            //TODO: Handle email sending failure
        }
    }
}
