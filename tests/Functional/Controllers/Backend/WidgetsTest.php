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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Doctrine\DBAL\Connection;

class WidgetsTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    private $userId;

    public function setUp(): void
    {
        parent::setUp();
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();

        Shopware()->Db()->exec('DELETE FROM s_statistics_visitors');
        Shopware()->Db()->exec('DELETE FROM s_order');
        Shopware()->Db()->exec('DELETE FROM s_user');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Shopware()->Plugins()->Backend()->Auth()->setNoAuth(false);
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl(false);

        $this->connection->rollBack();
    }

    public function testConversionIsEmpty()
    {
        $this->dispatch('backend/widgets/getTurnOverVisitors');

        $response = $this->View()->getAssign();

        static::assertTrue($response['success']);
        static::assertEquals('0.00', $response['conversion']);
    }

    public function testConversionIsCalucatedFromBeginningOfDay()
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P7DT1M'));

        $this->connection
            ->insert('s_statistics_visitors', [
                'shopID' => 1,
                'datum' => $date->format('Y-m-d'),
                'pageimpressions' => 1,
                'uniquevisits' => 1,
                'deviceType' => 'desktop',
            ]);

        $this->connection
            ->insert('s_order', [
                'ordernumber' => 999,
                'userID' => 97,
                'invoice_amount' => 977,
                'ordertime' => $date->format('Y-m-d H:i:s'),
                'status' => 8,
                'cleared' => 10,
                'paymentID' => 2,
            ]);

        $this->dispatch('backend/widgets/getTurnOverVisitors');

        $response = $this->View()->getAssign();

        static::assertTrue($response['success']);
        static::assertEquals('100.00', $response['conversion']);
    }

    public function testConversionStillWorks()
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P6DT59M'));

        $this->connection
            ->insert('s_statistics_visitors', [
                'shopID' => 1,
                'datum' => $date->format('Y-m-d'),
                'pageimpressions' => 1,
                'uniquevisits' => 1,
                'deviceType' => 'desktop',
            ]);

        $this->connection
            ->insert('s_order', [
                'ordernumber' => 999,
                'userID' => 97,
                'invoice_amount' => 977,
                'ordertime' => $date->format('Y-m-d H:i:s'),
                'status' => 8,
                'cleared' => 10,
                'paymentID' => 2,
            ]);

        $this->dispatch('backend/widgets/getTurnOverVisitors');

        $response = $this->View()->getAssign();

        static::assertTrue($response['success']);
        static::assertEquals('100.00', $response['conversion']);
    }

    public function testIfNoConversionAfterEightDays()
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P8D'));

        $this->connection
            ->insert('s_statistics_visitors', [
                'shopID' => 1,
                'datum' => $date->format('Y-m-d'),
                'pageimpressions' => 1,
                'uniquevisits' => 1,
                'deviceType' => 'desktop',
            ]);

        $this->connection
            ->insert('s_order', [
                'ordernumber' => 999,
                'userID' => 97,
                'invoice_amount' => 977,
                'ordertime' => $date->format('Y-m-d H:i:s'),
                'status' => 8,
                'cleared' => 10,
                'paymentID' => 2,
            ]);

        $this->dispatch('backend/widgets/getTurnOverVisitors');

        $response = $this->View()->getAssign();

        static::assertTrue($response['success']);
        static::assertEquals('0.00', $response['conversion']);
    }

    /**
     * test the getVisitorsAction
     */
    public function testGetVisitorsWithCompanyAction()
    {
        $addressData = [
            'company' => 'TestCompany',
            'salutation' => 'mr',
            'firstname' => 'Peter',
            'lastname' => 'Test',
            'street' => 'Teststreet 1',
            'country_id' => 2,
        ];

        $this->prepareTestGetVisitors($addressData);

        $this->dispatch('backend/widgets/getVisitors?page=1&start=0&limit=25');

        $response = $this->View()->getAssign();

        // Check if success
        static::assertArrayHasKey('success', $response);
        // Check if has data
        static::assertArrayHasKey('data', $response);
        // Check if data contains customers
        static::assertArrayHasKey('customers', $response['data']);

        // First customer should be the one we added, ass there isn't any other process adding any s_statistics_currentusers
        static::assertEquals($this->userId, $response['data']['customers'][0]['userID']);
        static::assertEquals($addressData['company'], $response['data']['customers'][0]['customer']);
    }

    public function testGetVisitorsWithoutCompanyAction()
    {
        $addressData = [
            'salutation' => 'mr',
            'firstname' => 'Peter',
            'lastname' => 'Test',
            'street' => 'Teststreet 1',
            'country_id' => 2,
        ];

        $this->prepareTestGetVisitors($addressData);

        $this->dispatch('backend/widgets/getVisitors?page=1&start=0&limit=25');

        $response = $this->View()->getAssign();

        // Check if success
        static::assertArrayHasKey('success', $response);
        // Check if has data
        static::assertArrayHasKey('data', $response);
        // Check if data contains customers
        static::assertArrayHasKey('customers', $response['data']);

        // First customer should be the one we added, ass there isn't any other process adding any s_statistics_currentusers
        static::assertEquals($this->userId, $response['data']['customers'][0]['userID']);
        static::assertEquals(
            $addressData['firstname'] . ' ' . $addressData['lastname'],
            $response['data']['customers'][0]['customer']
        );
    }

    public function testGetVisitorsWithEmptyCompanyAction()
    {
        $addressData = [
            'company' => '',
            'salutation' => 'mr',
            'firstname' => 'Peter',
            'lastname' => 'Test',
            'street' => 'Teststreet 1',
            'country_id' => 2,
        ];

        $this->prepareTestGetVisitors($addressData);

        $this->dispatch('backend/widgets/getVisitors?page=1&start=0&limit=25');

        $response = $this->View()->getAssign();

        // Check if success
        static::assertArrayHasKey('success', $response);
        // Check if has data
        static::assertArrayHasKey('data', $response);
        // Check if data contains customers
        static::assertArrayHasKey('customers', $response['data']);

        // First customer should be the one we added, ass there isn't any other process adding any s_statistics_currentusers
        static::assertEquals($this->userId, $response['data']['customers'][0]['userID']);
        static::assertEquals($addressData['firstname'] . ' ' . $addressData['lastname'], $response['data']['customers'][0]['customer']);
    }

    private function prepareTestGetVisitors($addressData)
    {
        $this->connection->insert('s_user', [
            'password' => '098f6bcd4621d373cade4e832627b4f6',
            'encoder' => 'md5',
            'email' => uniqid('test', true) . '@test.com',
            'accountmode' => 1,
            'active' => '1',
            'firstlogin' => '1990-01-01',
            'lastlogin' => '1990-01-01',
            'subshopID' => '1',
            'customergroup' => 'EK',
            'salutation' => 'mr',
            'firstname' => '',
            'lastname' => '',
            'birthday' => '1990-01-01',
        ]);

        $this->userId = $this->connection->lastInsertId('s_user');

        $addressData = array_merge(['user_id' => $this->userId], $addressData);
        $this->connection->insert('s_user_addresses', $addressData);

        $addressId = $this->connection->lastInsertId('s_user_addresses');

        /*
         * set default_billing_address_id
         */
        $this->connection->update('s_user', [
            'default_billing_address_id' => $addressId,
        ], ['id' => $this->userId]);

        $this->connection->insert('s_statistics_currentusers', [
            'remoteaddr' => '127.0.0.1',
            'page' => '/',
            'time' => new \Zend_Db_Expr('NOW()'),
            'userID' => $this->userId,
            'deviceType' => 'Test',
        ]);
    }
}
