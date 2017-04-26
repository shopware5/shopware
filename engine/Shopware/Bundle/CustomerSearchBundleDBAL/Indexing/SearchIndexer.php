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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\Indexing;

use Doctrine\DBAL\Connection;
use Shopware\Components\CustomerStream\InterestsStruct;

class SearchIndexer implements SearchIndexerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CustomerProvider
     */
    private $provider;

    /**
     * @param Connection       $connection
     * @param CustomerProvider $provider
     */
    public function __construct(Connection $connection, CustomerProvider $provider)
    {
        $this->connection = $connection;
        $this->provider = $provider;
    }

    /**
     * @param int[] $ids
     */
    public function populate(array $ids)
    {
        $this->connection->transactional(function () use ($ids) {
            $this->connection->executeUpdate(
                'DELETE FROM s_customer_search_index WHERE id IN (:ids)',
                [':ids' => $ids],
                [':ids' => Connection::PARAM_INT_ARRAY]
            );

            $insert = $this->createInsertQuery();

            $customers = $this->provider->get($ids);

            foreach ($customers as $customer) {
                $insert->execute($this->buildData($customer));
            }
        });
    }

    public function clearIndex()
    {
        $this->connection->executeUpdate('DELETE FROM s_customer_search_index');
    }

    /**
     * @param $customer
     *
     * @return array
     */
    protected function buildData(AnalyzedCustomer $customer)
    {
        $data = [
            'id' => $customer->getId(),
            'email' => $customer->getEmail(),
            'active' => $customer->getActive(),
            'accountmode' => $customer->getAccountMode(),
            'firstlogin' => $this->formatDate($customer->getFirstLogin()),
            'newsletter' => $customer->isNewsletter(),
            'shopId' => $customer->getShopId(),
            'default_billing_address_id' => $customer->getDefaultBillingAddressId(),
            'title' => $customer->getTitle(),
            'salutation' => $customer->getSalutation(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'birthday' => $this->formatDate($customer->getBirthday()),
            'customernumber' => $customer->getNumber(),
            'customerGroupId' => $customer->getCustomerGroup()->getId(),
            'customerGroup' => $customer->getCustomerGroup()->getName(),
            'paymentId' => $customer->getPaymentId(),
            'shop' => $customer->getShopId(),
            'company' => $customer->getBillingAddress()->getCompany(),
            'department' => $customer->getBillingAddress()->getDepartment(),
            'street' => $customer->getBillingAddress()->getStreet(),
            'zipcode' => $customer->getBillingAddress()->getZipcode(),
            'city' => $customer->getBillingAddress()->getCity(),
            'phone' => $customer->getBillingAddress()->getPhone(),
            'additional_address_line1' => $customer->getBillingAddress()->getAdditionalAddressLine1(),
            'additional_address_line2' => $customer->getBillingAddress()->getAdditionalAddressLine2(),
            'countryId' => $customer->getBillingAddress()->getCountryId(),
            'country' => $customer->getBillingAddress()->getCountry() ? $customer->getBillingAddress()->getCountry()->getName() : '',
            'stateId' => $customer->getBillingAddress()->getStateId(),
            'state' => $customer->getBillingAddress()->getState() ? $customer->getBillingAddress()->getState()->getName() : null,
            'age' => $customer->getAge(),
            'count_orders' => $customer->getOrderInformation()->getOrderCount(),
            'product_avg' => $customer->getOrderInformation()->getAvgProductPrice(),
            'invoice_amount_sum' => $customer->getOrderInformation()->getTotalAmount(),
            'invoice_amount_avg' => $customer->getOrderInformation()->getAvgAmount(),
            'invoice_amount_min' => $customer->getOrderInformation()->getMinAmount(),
            'invoice_amount_max' => $customer->getOrderInformation()->getMaxAmount(),
            'first_order_time' => $this->formatDate($customer->getOrderInformation()->getFirstOrderTime()),
            'last_order_time' => $this->formatDate($customer->getOrderInformation()->getLastOrderTime()),
            'has_canceled_orders' => $customer->getOrderInformation()->hasCanceledOrders(),
            'weekdays' => $this->implodeUnique($customer->getOrderInformation()->getWeekdays()),
            'shops' => $this->implodeUnique($customer->getOrderInformation()->getShops()),
            'devices' => $this->implodeUnique($customer->getOrderInformation()->getDevices()),
            'deliveries' => $this->implodeUnique($customer->getOrderInformation()->getDispatches()),
            'payments' => $this->implodeUnique($customer->getOrderInformation()->getPayments()),
            'products' => $this->implodeUnique(
                array_map(function (InterestsStruct $interest) {
                    return $interest->getProductNumber();
                }, $customer->getInterests())
            ),
            'categories' => $this->getCategories($customer->getInterests()),
            'manufacturers' => $this->implodeUnique(
                array_map(function (InterestsStruct $interest) {
                    return $interest->getManufacturerId();
                }, $customer->getInterests())
            ),
            'interests' => json_encode(array_slice($customer->getInterests(), 0, 5)),
        ];

        return $data;
    }

    private function createInsertQuery()
    {
        return $this->connection->prepare(
            'INSERT INTO s_customer_search_index (
                id,
                email,
                active,
                accountmode,
                firstlogin,
                newsletter,
                shopId,
                default_billing_address_id,
                title,
                salutation,
                firstname,
                lastname,
                birthday,
                customernumber,
                customerGroupId,
                customerGroup,
                paymentId,
                shop,
                company,
                department,
                street,
                zipcode,
                city,
                phone,
                additional_address_line1,
                additional_address_line2,
                countryId,
                country,
                stateId,
                state,
                age,
                count_orders ,
                invoice_amount_sum,
                invoice_amount_avg,
                invoice_amount_min,
                invoice_amount_max,
                first_order_time,
                last_order_time,
                has_canceled_orders,
                weekdays,
                shops,
                devices,
                deliveries,
                payments,
                product_avg,
                products,
                categories,
                manufacturers,
                interests
            ) VALUES (
                :id,
                :email,
                :active,
                :accountmode,
                :firstlogin,
                :newsletter,
                :shopId,
                :default_billing_address_id,
                :title,
                :salutation,
                :firstname,
                :lastname,
                :birthday,
                :customernumber,
                :customerGroupId,
                :customerGroup,
                :paymentId,
                :shop,
                :company,
                :department,
                :street,
                :zipcode,
                :city,
                :phone,
                :additional_address_line1,
                :additional_address_line2,
                :countryId,
                :country,
                :stateId,
                :state,
                :age,
                :count_orders,
                :invoice_amount_sum,
                :invoice_amount_avg,
                :invoice_amount_min,
                :invoice_amount_max,
                :first_order_time,
                :last_order_time,
                :has_canceled_orders,
                :weekdays,
                :shops,
                :devices,
                :deliveries,
                :payments,
                :product_avg,
                :products,
                :categories,
                :manufacturers,
                :interests
            )
      ');
    }

    private function implodeUnique($array)
    {
        if (empty($array)) {
            return null;
        }

        return '||' . implode('||', array_keys(array_flip($array))) . '||';
    }

    /**
     * @param \DateTime|null $date
     * @param string         $format
     *
     * @return null|string
     */
    private function formatDate(\DateTime $date = null, $format = 'Y-m-d H:i:s')
    {
        if ($date === null) {
            return null;
        }

        return $date->format($format);
    }

    /**
     * @param InterestsStruct[] $interests
     * @return null|string
     */
    private function getCategories($interests)
    {
        $categories = [];
        foreach ($interests as $interest) {
            $categories = array_merge($categories, [$interest->getCategoryId()], $interest->getCategoryPath());
        }

        return $this->implodeUnique($categories);
    }
}
