<?php

namespace Shopware\Bundle\CustomerSearchBundle\CustomerStream;

use Doctrine\DBAL\Connection;

class SearchIndexer
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
     * @param int[] $ids
     */
    public function populate(array $ids)
    {
        $insert = $this->createInsertQuery();

        $customers = $this->provider->get($ids);

        foreach ($customers as $customer) {
            $insert->execute($this->buildData($customer));
        }
    }

    public function clearIndex()
    {
        $this->connection->executeUpdate("DELETE FROM s_customer_search_index");
    }

    private function createInsertQuery()
    {
        return $this->connection->prepare(
            "INSERT INTO s_customer_search_index (
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
                payment,
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
                :payment,
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
                :product_avg,
                :products,
                :categories,
                :manufacturers,
                :interests)
      ");
    }


    private function implodeUnique($array)
    {
        if (empty($array)) {
            return null;
        }

        return '||' . implode('||', array_keys(array_flip($array))) . '||';
    }

    /**
     * @param $customer
     * @return array
     */
    protected function buildData($customer)
    {
        $data = [
            'id' => $customer['id'],
            'email' => $customer['email'],
            'active' => $customer['active'],
            'accountmode' => $customer['accountmode'],
            'firstlogin' => $customer['firstlogin'],
            'newsletter' => $customer['newsletter'],
            'shopId' => $customer['shopId'],
            'default_billing_address_id' => $customer['default_billing_address_id'],
            'title' => $customer['title'],
            'salutation' => $customer['salutation'],
            'firstname' => $customer['firstname'],
            'lastname' => $customer['lastname'],
            'birthday' => $customer['birthday'],
            'customernumber' => $customer['customernumber'],
            'customerGroupId' => $customer['customerGroupId'],
            'customerGroup' => $customer['customerGroup'],
            'paymentId' => $customer['paymentId'],
            'payment' => $customer['payment'],
            'shop' => $customer['shop'],
            'company' => $customer['company'],
            'department' => $customer['department'],
            'street' => $customer['street'],
            'zipcode' => $customer['zipcode'],
            'city' => $customer['city'],
            'phone' => $customer['phone'],
            'additional_address_line1' => $customer['additional_address_line1'],
            'additional_address_line2' => $customer['additional_address_line2'],
            'countryId' => $customer['countryId'],
            'country' => $customer['country'],
            'stateId' => $customer['stateId'],
            'state' => $customer['state'],
            'age' => $customer['age'],
            'count_orders' => $customer['aggregation']['count_orders'],
            'product_avg' => $customer['aggregation']['product_avg'],
            'invoice_amount_sum' => $customer['aggregation']['invoice_amount_sum'],
            'invoice_amount_avg' => $customer['aggregation']['invoice_amount_avg'],
            'invoice_amount_min' => $customer['aggregation']['invoice_amount_min'],
            'invoice_amount_max' => $customer['aggregation']['invoice_amount_max'],
            'first_order_time' => $customer['aggregation']['first_order_time'],
            'last_order_time' => $customer['aggregation']['last_order_time'],
            'products' => $this->implodeUnique(array_column($customer['interests'], 'articleordernumber')),
            'categories' => $this->implodeUnique(array_column($customer['interests'], 'categoryId')),
            'manufacturers' => $this->implodeUnique(array_column($customer['interests'], 'manufacturerId')),
            'interests' => json_encode(array_slice($customer['interests'], 0, 10))
        ];

        return $data;
    }
}
