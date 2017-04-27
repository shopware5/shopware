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

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CustomerSearchBundle\Condition\RegisteredInShopCondition;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchResult;
use Shopware\Bundle\CustomerSearchBundleDBAL\CustomerNumberSearch;
use Shopware\Bundle\SearchBundle\Criteria;

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
        } catch (\Exception $e) {
        }

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
     * @param array[]  $customers
     * @param string[] $expectedNumbers
     *
     * @return CustomerNumberSearchResult
     */
    public function search(
        Criteria $criteria,
        array $expectedNumbers,
        array $customers
    ) {
        $criteria->addCondition(new RegisteredInShopCondition([1000]));

        $ids = [];
        foreach ($customers as $customer) {
            $ids[] = $this->createCustomer($customer);
        }

        /** @var CustomerNumberSearch $search */
        $search = Shopware()->Container()->get('customer_search.dbal.number_search');

        /** @var \Shopware\Bundle\CustomerSearchBundleDBAL\Indexing\SearchIndexer $indexer */
        $indexer = Shopware()->Container()->get('customer_search.dbal.indexing.indexer');
        $indexer->clearIndex();
        $indexer->populate($ids);

        $result = $search->search($criteria);

        foreach ($expectedNumbers as $number) {
            $this->assertTrue(in_array($number, $result->getNumbers()), 'Customer number: ' . $number . ' not found');
        }
        foreach ($result->getNumbers() as $number) {
            $this->assertTrue(in_array($number, $expectedNumbers), 'Customer number: ' . $number . ' not expected');
        }

        return $result;
    }

    protected function createCustomer(array $customer)
    {
        $this->connection->delete('s_user', [
            'customernumber' => $customer['number'],
        ]);

        if (!array_key_exists('addresses', $customer)) {
            $customer['addresses'] = [['country_id' => 2]];
        }

        $userId = $this->insert('s_user', array_merge([
            'firstname' => 'example',
            'lastname' => 'example',
            'customergroup' => 'EK',
            'customernumber' => $customer['number'],
            'accountmode' => 0,
            'paymentpreset' => 1,
            'subshopID' => 1000,
            'failedlogins' => 0,
        ], $customer));

        if (array_key_exists('addresses', $customer)) {
            foreach ($customer['addresses'] as $address) {
                $address['user_id'] = $userId;
                $addressId = $this->insert('s_user_addresses', $address);

                $this->connection->update(
                    's_user',
                    [
                        'default_billing_address_id' => $addressId,
                        'default_shipping_address_id' => $addressId,
                    ],
                    ['id' => $userId]
                );
            }
        }

        if (array_key_exists('orders', $customer)) {
            foreach ($customer['orders'] as $order) {
                $order = array_merge([
                    'ordertime' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'language' => 1,
                ], $order);

                if (!array_key_exists('details', $order)) {
                    $order['details'] = [
                        ['ordernumber' => 'SW10235', 'modus' => 0],
                    ];
                }
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

        return $userId;
    }

    /**
     * @param string $table
     * @param array  $data
     *
     * @return string
     */
    private function insert($table, array $data)
    {
        $data = $this->filterData($data, $table);
        $this->connection->insert($table, $data);

        return $this->connection->lastInsertId($table);
    }

    /**
     * @param array  $data
     * @param string $table
     *
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
