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

/**
 * Replacement class for legacy core/paymentmeans/debit.php class.
 *
 * Class DebitPaymentMethod
 * Used to handle debit payment
 *
 * @package ShopwarePlugin\PaymentMethods\Components
 */
class DebitPaymentMethod extends GenericPaymentMethod
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        if (!Shopware()->Front()->Request()->getParam("sDebitAccount")) {
            $sErrorFlag["sDebitAccount"] = true;
        }
        if (!Shopware()->Front()->Request()->getParam("sDebitBankcode")) {
            $sErrorFlag["sDebitBankcode"] = true;
        }
        if (!Shopware()->Front()->Request()->getParam("sDebitBankName")) {
            $sErrorFlag["sDebitBankName"] = true;
        }
        $bankHolder = Shopware()->Front()->Request()->getParam("sDebitBankHolder");
        if (empty($bankHolder) && isset($bankHolder)) {
            $sErrorFlag["sDebitBankHolder"] = true;
        }

        if (count($sErrorFlag)) {
            $sErrorMessages[] = Shopware()->Snippets()->getNamespace('frontend/account/internalMessages')
                ->get('ErrorFillIn','Please fill in all red fields');

            return array(
                "sErrorFlag" => $sErrorFlag,
                "sErrorMessages" => $sErrorMessages
            );
        } else {
            return true;
        }
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
            getPaymentsQuery(array('name' => 'debit'))->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $data = array(
            'account_number' => Shopware()->Front()->Request()->getParam("sDebitAccount"),
            'bank_code' => Shopware()->Front()->Request()->getParam("sDebitBankcode"),
            'bankname' => Shopware()->Front()->Request()->getParam("sDebitBankName"),
            'account_holder' => Shopware()->Front()->Request()->getParam("sDebitBankHolder")
        );

        if (!$lastPayment) {
            $date = new \DateTime();
            $data['created_at'] = $date->format('Y-m-d');
            $data['payment_mean_id'] = $paymentMean['id'];
            $data['user_id'] = $userId;
            Shopware()->Db()->insert("s_core_payment_data", $data);
        } else {
            $where = array(
                'payment_mean_id = ?' => $paymentMean['id'],
                'user_id = ?'  => $userId
            );

            Shopware()->Db()->update("s_core_payment_data", $data, $where);
        }

        /**
         * This section is legacy code form the old core debit.php class
         * It's still used to avoid BC break, but should be considered deprecated
         * and it will be removed in future releases
         *
         * It updates the s_user_debit (deprecated) table with the submited data
         */
        $data = array(
            Shopware()->Front()->Request()->getParam("sDebitAccount"),
            Shopware()->Front()->Request()->getParam("sDebitBankcode"),
            Shopware()->Front()->Request()->getParam("sDebitBankName"),
            Shopware()->Front()->Request()->getParam("sDebitBankHolder"),
            $userId
        );

        if ($this->getData()) {
            $sql = "UPDATE s_user_debit SET account=?, bankcode=?, bankname=?, bankholder=?
                WHERE userID = ?";
        } else {
            $sql = "INSERT INTO s_user_debit (account, bankcode, bankname, bankholder, userID)
                VALUES (?,?,?,?,?)";
        }

        Shopware()->Db()->query($sql, $data);
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
            ->getCurrentPaymentDataQueryBuilder($userId, 'debit')->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if (isset($paymentData)) {
            $arrayData = array(
                "sDebitAccount" => $paymentData['accountNumber'],
                "sDebitBankcode" => $paymentData['bankCode'],
                "sDebitBankName" => $paymentData['bankName'],
                "sDebitBankHolder" =>  $paymentData['accountHolder']
            );

            return $arrayData;
        }

        /**
         * This code is provided as a temporary "bridge" between old and new tables
         * It can be safely removed after s_user_debit is removed
         */
        $rawData = $this->getData();
        if (!$rawData) {
            return array();
        }

        $paymentMean = Shopware()->Models()->getRepository('\Shopware\Models\Payment\Payment')->
            getPaymentsQuery(array('name' => 'debit'))->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $date = new \DateTime();
        $data = array(
            'account_number' => $rawData["sDebitAccount"],
            'bank_code' => $rawData["sDebitBankcode"],
            'bankname' => $rawData["sDebitBankName"],
            'account_holder' => $rawData["sDebitBankHolder"],
            'payment_mean_id' => $paymentMean['id'],
            'user_id' => $userId,
            'created_at' => $date->format('Y-m-d')
        );

        Shopware()->Db()->insert("s_core_payment_data", $data);

        return $rawData;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $userId = Shopware()->Session()->sUserId;
        if (empty($userId)) {
            return;
        }

        $getData = Shopware()->Db()->fetchRow(
            "SELECT account AS sDebitAccount, bankcode AS sDebitBankcode, bankname AS sDebitBankName, bankholder AS sDebitBankHolder
              FROM s_user_debit
              WHERE userID = :user_id",
            array('user_id' => $userId)
        );

        return $getData;
    }

    /**
     * @inheritdoc
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

        $addressData = Shopware()->Models()->getRepository('Shopware\Models\Customer\Billing')->
            getUserBillingQuery($userId)->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        $debitData = $this->getCurrentPaymentDataAsArray();

        $date = new \DateTime();
        $data = array(
            'payment_mean_id' => $paymentId,
            'order_id' => $orderId,
            'user_id' => $userId,
            'firstname' => $addressData['firstName'],
            'lastname' => $addressData['lastName'],
            'address' => $addressData['street'] . ' ' . $addressData['streetNumber'],
            'zipcode' => $addressData['zipCode'],
            'city' => $addressData['city'],
            'account_number' => $debitData['sDebitAccount'],
            'bank_code' => $debitData['sDebitBankcode'],
            'bank_name' => $debitData['sDebitBankName'],
            'account_holder' => $debitData['sDebitBankHolder'],
            'amount' => $orderAmount,
            'created_at' => $date->format('Y-m-d')
        );

        Shopware()->Db()->insert("s_core_payment_instance", $data);

        return true;
    }
}
