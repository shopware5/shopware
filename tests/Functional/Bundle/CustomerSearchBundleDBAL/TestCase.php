<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL;

use DateTime;
use Doctrine\DBAL\Connection;
use Enlight_Components_Test_TestCase;
use Exception;
use Shopware\Bundle\AttributeBundle\Service\DataPersisterInterface;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchInterface;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchResult;
use Shopware\Bundle\CustomerSearchBundleDBAL\Indexing\SearchIndexerInterface;
use Shopware\Bundle\SearchBundle\Criteria;

class TestCase extends Enlight_Components_Test_TestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var bool
     */
    protected $debug = false;

    protected function setUp(): void
    {
        $this->connection = Shopware()->Container()->get(Connection::class);
        if (!$this->debug) {
            $this->connection->beginTransaction();
        }

        try {
            $this->connection->insert('s_core_shops', ['id' => 1000, 'name' => 'customer_search']);
        } catch (Exception $e) {
        }

        $this->connection->delete('s_user', ['subshopID' => 1000]);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        if (!$this->debug) {
            $this->connection->rollBack();
        }
        parent::tearDown();
    }

    /**
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
        $ids = [];
        foreach ($customers as $customer) {
            $ids[] = $this->createCustomer($customer);
        }

        $search = Shopware()->Container()->get(CustomerNumberSearchInterface::class);

        $indexer = Shopware()->Container()->get(SearchIndexerInterface::class);
        $indexer->populate($ids);

        $result = $search->search($criteria);

        foreach ($expectedNumbers as $number) {
            static::assertTrue(\in_array($number, $result->getNumbers()), 'Customer number: ' . $number . ' not found');
        }
        foreach ($result->getNumbers() as $number) {
            static::assertTrue(\in_array($number, $expectedNumbers), 'Customer number: ' . $number . ' not expected');
        }

        return $result;
    }

    protected function createCustomer(array $customer)
    {
        $this->connection->delete('s_user', [
            'customernumber' => $customer['number'],
        ]);

        if (!\array_key_exists('addresses', $customer)) {
            $customer['addresses'] = [['country_id' => 2]];
        }

        $customerData = array_merge([
            'firstname' => 'example',
            'lastname' => 'example',
            'customergroup' => 'EK',
            'customernumber' => $customer['number'],
            'accountmode' => 0,
            'paymentpreset' => 1,
            'subshopID' => $customer['subshopID'] ?? 1000,
            'failedlogins' => 0,
        ], $customer);
        unset($customerData['addresses'], $customerData['orders'], $customerData['newsletter']);
        $customerId = $this->insert('s_user', $customerData);

        if (\array_key_exists('addresses', $customer)) {
            foreach ($customer['addresses'] as $address) {
                $address['user_id'] = $customerId;
                $addressId = $this->insert('s_user_addresses', $address);

                $this->connection->update(
                    's_user',
                    [
                        'default_billing_address_id' => $addressId,
                        'default_shipping_address_id' => $addressId,
                    ],
                    ['id' => $customerId]
                );
            }
        }

        if (\array_key_exists('orders', $customer)) {
            foreach ($customer['orders'] as $order) {
                $order = array_merge([
                    'ordertime' => (new DateTime())->format('Y-m-d H:i:s'),
                    'language' => 1,
                    'subshopID' => 1,
                    'currencyFactor' => 1,
                ], $order);

                if (!\array_key_exists('details', $order)) {
                    $order['details'] = [
                        ['ordernumber' => 'SW10235', 'modus' => 0],
                    ];
                }
                $order['userID'] = $customerId;
                $orderId = $this->insert('s_order', $order);

                if (\array_key_exists('details', $order)) {
                    foreach ($order['details'] as $detail) {
                        $detail['orderID'] = $orderId;
                        $this->insert('s_order_details', $detail);
                    }
                }
            }
        }

        if (\array_key_exists('newsletter', $customer)) {
            foreach ($customer['newsletter'] as $newsletter) {
                $newsletter = array_merge([
                    'customer' => 0,
                    'groupID' => 1,
                    'lastmailing' => 0,
                    'lastread' => 0,
                ], $newsletter);
                $this->insert('s_campaigns_mailaddresses', $newsletter);
            }
        }

        if (\array_key_exists('attribute', $customer)) {
            $persister = Shopware()->Container()->get(DataPersisterInterface::class);
            $persister->persist($customer['attribute'], 's_user_attributes', $customerId);
        }

        return $customerId;
    }

    private function insert(string $table, array $data): int
    {
        $data = $this->filterData($data, $table);
        $this->connection->insert($table, $data);

        return (int) $this->connection->lastInsertId($table);
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
