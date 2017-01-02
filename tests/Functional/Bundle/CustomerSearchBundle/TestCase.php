<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CustomerSearchBundle\Condition\RegisteredInShopCondition;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearch;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchResult;
use Shopware\Bundle\CustomerSearchBundle\Criteria;

class TestCase extends \Enlight_Components_Test_TestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var bool
     */
    protected $debug = false;

    protected function setUp()
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        if (!$this->debug) {
            $this->connection->beginTransaction();
        }

        try {
            $this->connection->insert('s_core_shops', ['id' => 1000, 'name' => 'customer_search']);
        } catch (\Exception $e) { }

        $this->connection->delete('s_user', ['subshopID' => 1000]);

        parent::setUp();
    }

    protected function tearDown()
    {
        if (!$this->debug) {
            $this->connection->rollBack();
        }
        parent::tearDown();
    }

    /**
     * @param Criteria $criteria
     * @param array[] $customers
     * @param string[] $expectedNumbers
     * @return CustomerNumberSearchResult
     */
    public function search(
        Criteria $criteria,
        array $expectedNumbers,
        array $customers
    ) {
        $criteria->addCondition(new RegisteredInShopCondition([1000]));

        foreach ($customers as $customer) {
            $this->createCustomer($customer);
        }

        /** @var CustomerNumberSearch $search */
        $search = Shopware()->Container()->get('shopware_customer_search.customer_number_search');

        $result = $search->search($criteria);

        foreach ($expectedNumbers as $number) {
            $this->assertTrue(in_array($number, $result->getNumbers()), "Customer number: " . $number . " not found");
        }
        foreach ($result->getNumbers() as $number) {
            $this->assertTrue(in_array($number, $expectedNumbers), "Customer number: " . $number . " not expected");
        }

        return $result;
    }

    protected function createCustomer(array $customer)
    {
        $this->connection->delete('s_user', [
            'customernumber' => $customer['number']
        ]);

        $userId = $this->insert('s_user', array_merge($customer, [
            'firstname' => 'example',
            'lastname' => 'example',
            'customernumber' => $customer['number'],
            'accountmode' => 0,
            'paymentpreset' => 1,
            'subshopID' => 1000,
            'failedlogins' => 0
        ]));

        if (array_key_exists('addresses', $customer)) {
            foreach ($customer['addresses'] as $address) {
                $address['user_id'] = $userId;
                $this->insert('s_user_addresses', $address);
            }
        }

        if (array_key_exists('orders', $customer)) {
            error_log(print_r($customer['number'], true) . "\n", 3, '/var/log/test.log');
            foreach ($customer['orders'] as $order) {

                $order['userID'] = $userId;
                $orderId = $this->insert('s_order', $order);

                if (array_key_exists('details', $order)) {
                    foreach ($order['details'] as $detail) {
                        $detail['orderID'] = $orderId;
                        $this->insert('s_order_details', $detail);
                    }
                }
            }
        }
    }


    /**
     * @param string $table
     * @param array $data
     * @return string
     */
    private function insert($table, array $data)
    {
        $data = $this->filterData($data, $table);
        $this->connection->insert($table, $data);
        return $this->connection->lastInsertId($table);
    }

    /**
     * @param array $data
     * @param string $table
     * @return array
     */
    private function filterData($data, $table)
    {
        $schemaManager = $this->connection->getSchemaManager();
        $columns = $schemaManager->listTableColumns($table);
        $whitelist = [];
        foreach ($columns as $column) {
            $whitelist[] = $column->getName();
        }
        return array_intersect_key($data, array_flip($whitelist));
    }
}