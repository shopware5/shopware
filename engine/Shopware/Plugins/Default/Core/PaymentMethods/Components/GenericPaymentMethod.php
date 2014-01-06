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
use Shopware\Models\Payment\PaymentInstance;

/**
 * Class GenericPaymentMethod
 * Used for all payment methods that require no specific logic
 *
 * @package ShopwarePlugin\PaymentMethods\Components
 */
class GenericPaymentMethod extends BasePaymentMethod
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function savePaymentData()
    {
        //nothing to do, no return expected
        return;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentPaymentDataAsArray()
    {
        //nothing to do, array expected
        return array();
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

        $addressData = Shopware()->Models()->getRepository('Shopware\Models\Customer\Billing')
            ->getUserBillingQuery($userId)->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $query = "INSERT INTO  s_core_payment_instance (
            payment_mean_id ,
            order_id ,
            user_id ,
            firstname ,
            lastname ,
            address ,
            zipcode ,
            city ,
            amount ,
            created_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $date = new \DateTime();
        $data = array(
            $paymentId,
            $orderId,
            $userId,
            $addressData['firstName'],
            $addressData['lastName'],
            $addressData['street'] . ' ' . $addressData['streetNumber'],
            $addressData['zipCode'],
            $addressData['city'],
            $orderAmount,
            $date->format('Y-m-d')
        );

        Shopware()->Db()->query($query, $data);

        return true;
    }
}
