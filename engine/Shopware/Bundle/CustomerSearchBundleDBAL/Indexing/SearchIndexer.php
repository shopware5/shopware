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

class SearchIndexer implements SearchIndexerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CustomerProviderInterface
     */
    private $provider;

    public function __construct(Connection $connection, CustomerProviderInterface $provider)
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
            $insert = $this->createInsertQuery();
            $customers = $this->provider->get($ids);
            foreach ($customers as $customer) {
                $insert->execute($this->buildData($customer));
            }
        });
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without replacement
     */
    public function clearIndex()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $this->connection->executeUpdate('DELETE FROM s_customer_search_index');
    }

    public function cleanupIndex()
    {
        $this->connection->executeUpdate('
            DELETE search_index FROM s_customer_search_index search_index
            LEFT JOIN s_user customer
                ON customer.id = search_index.id
            WHERE customer.id IS NULL
        ');
    }

    /**
     * @return array
     */
    private function buildData(AnalyzedCustomer $customer)
    {
        $data = [
            'id' => $customer->getId(),
            'email' => $customer->getEmail(),
            'active' => $customer->getActive(),
            'accountmode' => $customer->getAccountMode(),
            'firstlogin' => $this->formatDate($customer->getFirstLogin()),
            'newsletter' => $customer->isNewsletter(),
            'shop_id' => $customer->getShopId(),
            'default_billing_address_id' => $customer->getDefaultBillingAddressId(),
            'title' => $customer->getTitle(),
            'salutation' => $customer->getSalutation(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'birthday' => $this->formatDate($customer->getBirthday()),
            'customernumber' => $customer->getNumber(),
            'customer_group_id' => $customer->getCustomerGroup() ? $customer->getCustomerGroup()->getId() : null,
            'customer_group_name' => $customer->getCustomerGroup() ? $customer->getCustomerGroup()->getName() : '',
            'payment_id' => $customer->getPaymentId(),
            'company' => $customer->getBillingAddress() ? $customer->getBillingAddress()->getCompany() : '',
            'department' => $customer->getBillingAddress() ? $customer->getBillingAddress()->getDepartment() : '',
            'street' => $customer->getBillingAddress() ? $customer->getBillingAddress()->getStreet() : '',
            'zipcode' => $customer->getBillingAddress() ? $customer->getBillingAddress()->getZipcode() : '',
            'city' => $customer->getBillingAddress() ? $customer->getBillingAddress()->getCity() : '',
            'phone' => $customer->getBillingAddress() ? $customer->getBillingAddress()->getPhone() : '',
            'additional_address_line1' => $customer->getBillingAddress() ? $customer->getBillingAddress()->getAdditionalAddressLine1() : '',
            'additional_address_line2' => $customer->getBillingAddress() ? $customer->getBillingAddress()->getAdditionalAddressLine2() : '',
            'country_id' => $customer->getBillingAddress() ? $customer->getBillingAddress()->getCountryId() : null,
            'country_name' => $customer->getBillingAddress() ? $customer->getBillingAddress()->getCountry()->getName() : '',
            'state_id' => $customer->getBillingAddress() ? $customer->getBillingAddress()->getStateId() : '',
            'age' => $customer->getAge(),
            'count_orders' => (int) $customer->getOrderInformation()->getOrderCount(),
            'product_avg' => (float) $customer->getOrderInformation()->getAvgProductPrice(),
            'invoice_amount_sum' => (float) $customer->getOrderInformation()->getTotalAmount(),
            'invoice_amount_avg' => (float) $customer->getOrderInformation()->getAvgAmount(),
            'invoice_amount_min' => (float) $customer->getOrderInformation()->getMinAmount(),
            'invoice_amount_max' => (float) $customer->getOrderInformation()->getMaxAmount(),
            'first_order_time' => $this->formatDate($customer->getOrderInformation()->getFirstOrderTime()),
            'last_order_time' => $this->formatDate($customer->getOrderInformation()->getLastOrderTime()),
            'has_canceled_orders' => $customer->getOrderInformation()->hasCanceledOrders(),
            'ordered_at_weekdays' => $this->implodeUnique($customer->getOrderInformation()->getWeekdays()),
            'ordered_in_shops' => $this->implodeUnique($customer->getOrderInformation()->getShops()),
            'ordered_on_devices' => $this->implodeUnique($customer->getOrderInformation()->getDevices()),
            'ordered_with_deliveries' => $this->implodeUnique($customer->getOrderInformation()->getDispatches()),
            'ordered_with_payments' => $this->implodeUnique($customer->getOrderInformation()->getPayments()),
            'ordered_products' => $this->implodeUnique($customer->getOrderInformation()->getProducts()),
            'ordered_products_of_categories' => $this->implodeUnique($customer->getOrderInformation()->getCategories()),
            'ordered_products_of_manufacturer' => $this->implodeUnique($customer->getOrderInformation()->getManufacturers()),
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
                shop_id,
                default_billing_address_id,
                title,
                salutation,
                firstname,
                lastname,
                birthday,
                customernumber,
                customer_group_id,
                customer_group_name,
                payment_id,
                company,
                department,
                street,
                zipcode,
                city,
                phone,
                additional_address_line1,
                additional_address_line2,
                country_id,
                country_name,
                state_id,
                age,
                count_orders ,
                invoice_amount_sum,
                invoice_amount_avg,
                invoice_amount_min,
                invoice_amount_max,
                first_order_time,
                last_order_time,
                has_canceled_orders,
                ordered_at_weekdays,
                ordered_in_shops,
                ordered_on_devices,
                ordered_with_deliveries,
                ordered_with_payments,
                product_avg,
                ordered_products,
                ordered_products_of_categories,
                ordered_products_of_manufacturer,
                index_time
            ) VALUES (
                :id,
                :email,
                :active,
                :accountmode,
                :firstlogin,
                :newsletter,
                :shop_id,
                :default_billing_address_id,
                :title,
                :salutation,
                :firstname,
                :lastname,
                :birthday,
                :customernumber,
                :customer_group_id,
                :customer_group_name,
                :payment_id,
                :company,
                :department,
                :street,
                :zipcode,
                :city,
                :phone,
                :additional_address_line1,
                :additional_address_line2,
                :country_id,
                :country_name,
                :state_id,
                :age,
                :count_orders,
                :invoice_amount_sum,
                :invoice_amount_avg,
                :invoice_amount_min,
                :invoice_amount_max,
                :first_order_time,
                :last_order_time,
                :has_canceled_orders,
                :ordered_at_weekdays,
                :ordered_in_shops,
                :ordered_on_devices,
                :ordered_with_deliveries,
                :ordered_with_payments,
                :product_avg,
                :ordered_products,
                :ordered_products_of_categories,
                :ordered_products_of_manufacturer,
                NOW()
            )
      ');
    }

    private function implodeUnique($array)
    {
        if (empty($array)) {
            return null;
        }

        return '|' . implode('|', array_keys(array_flip($array))) . '|';
    }

    /**
     * @param string $format
     *
     * @return string|null
     */
    private function formatDate(\DateTimeInterface $date = null, $format = 'Y-m-d H:i:s')
    {
        if ($date === null) {
            return null;
        }

        return $date->format($format);
    }
}
