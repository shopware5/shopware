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

/**
 * Replacement class for legacy core/paymentmeans/debit.php class.
 *
 * Used to handle debit payment
 */
class DebitPaymentMethod extends GenericPaymentMethod
{
    /**
     * {@inheritdoc}
     */
    public function validate($paymentData)
    {
        $sErrorFlag = [];
        $fields = [
            'sDebitAccount',
            'sDebitBankcode',
            'sDebitBankName',
            'sDebitBankHolder',
        ];

        foreach ($fields as $field) {
            $value = $paymentData[$field] ?: '';
            $value = trim($value);

            if (empty($value)) {
                $sErrorFlag[$field] = true;
            }
        }

        if (count($sErrorFlag)) {
            $sErrorMessages[] = Shopware()->Snippets()->getNamespace('frontend/account/internalMessages')
                ->get('ErrorFillIn', 'Please fill in all red fields');

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

        $paymentMean = Shopware()->Models()->getRepository('\Shopware\Models\Payment\Payment')->
            getActivePaymentsQuery(['name' => 'debit'])->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $data = [
            'account_number' => $request->getParam('sDebitAccount'),
            'bank_code' => $request->getParam('sDebitBankcode'),
            'bankname' => $request->getParam('sDebitBankName'),
            'account_holder' => $request->getParam('sDebitBankHolder'),
        ];

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
            ->getCurrentPaymentDataQueryBuilder($userId, 'debit')->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if (isset($paymentData)) {
            $arrayData = [
                'sDebitAccount' => $paymentData['accountNumber'],
                'sDebitBankcode' => $paymentData['bankCode'],
                'sDebitBankName' => $paymentData['bankName'],
                'sDebitBankHolder' => $paymentData['accountHolder'],
            ];

            return $arrayData;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createPaymentInstance($orderId, $userId, $paymentId)
    {
        $orderAmount = Shopware()->Models()->createQueryBuilder()
            ->select('orders.invoiceAmount')
            ->from('Shopware\Models\Order\Order', 'orders')
            ->where('orders.id = ?1')
            ->setParameter(1, $orderId)
            ->getQuery()
            ->getSingleScalarResult();

        $addressData = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')
            ->find($userId)->getDefaultBillingAddress();

        $debitData = $this->getCurrentPaymentDataAsArray($userId);

        $date = new \DateTime();
        $data = [
            'payment_mean_id' => $paymentId,
            'order_id' => $orderId,
            'user_id' => $userId,
            'firstname' => $addressData->getFirstname(),
            'lastname' => $addressData->getLastname(),
            'address' => $addressData->getStreet(),
            'zipcode' => $addressData->getZipcode(),
            'city' => $addressData->getCity(),
            'account_number' => $debitData['sDebitAccount'],
            'bank_code' => $debitData['sDebitBankcode'],
            'bank_name' => $debitData['sDebitBankName'],
            'account_holder' => $debitData['sDebitBankHolder'],
            'amount' => $orderAmount,
            'created_at' => $date->format('Y-m-d'),
        ];

        Shopware()->Db()->insert('s_core_payment_instance', $data);

        return true;
    }
}
