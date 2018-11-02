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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Controllers_Backend_WidgetsTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Database connection which used for each database operation in this class.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    private $userId;
    private $addressId;
    private $statisticsId;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp()
    {
        parent::setUp();

        $this->db = Shopware()->Db();

        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
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

        $response = $this->dispatch('backend/widgets/getVisitors?page=1&start=0&limit=25');

        $jsonBody = json_decode($response->getBody(), true);

        //check if success
        $this->assertArrayHasKey('success', $jsonBody);
        //check if has data
        $this->assertArrayHasKey('data', $jsonBody);
        //check if data contains customers
        $this->assertArrayHasKey('customers', $jsonBody['data']);

        //first customer should be the one we added, ass there isn't any other process adding any s_statistics_currentusers
        $this->assertEquals($this->userId, $jsonBody['data']['customers'][0]['userID']);
        $this->assertEquals($addressData['company'], $jsonBody['data']['customers'][0]['customer']);

        /*
         * cleanup
         */
        $this->db->delete('s_statistics_currentusers', ['id = ?' => $this->statisticsId]);
        $this->db->delete('s_user_addresses', ['id = ?' => $this->addressId]);
        $this->db->delete('s_user', ['id = ?' => $this->userId]);
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

        $response = $this->dispatch('backend/widgets/getVisitors?page=1&start=0&limit=25');

        $jsonBody = json_decode($response->getBody(), true);

        //check if success
        $this->assertArrayHasKey('success', $jsonBody);
        //check if has data
        $this->assertArrayHasKey('data', $jsonBody);
        //check if data contains customers
        $this->assertArrayHasKey('customers', $jsonBody['data']);

        //first customer should be the one we added, ass there isn't any other process adding any s_statistics_currentusers
        $this->assertEquals($this->userId, $jsonBody['data']['customers'][0]['userID']);
        $this->assertEquals($addressData['firstname'] . ' ' . $addressData['lastname'],
            $jsonBody['data']['customers'][0]['customer']);

        /*
         * cleanup
         */
        $this->db->delete('s_statistics_currentusers', ['id = ?' => $this->statisticsId]);
        $this->db->delete('s_user_addresses', ['id = ?' => $this->addressId]);
        $this->db->delete('s_user', ['id = ?' => $this->userId]);
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

        $response = $this->dispatch('backend/widgets/getVisitors?page=1&start=0&limit=25');

        $jsonBody = json_decode($response->getBody(), true);

        //check if success
        $this->assertArrayHasKey('success', $jsonBody);
        //check if has data
        $this->assertArrayHasKey('data', $jsonBody);
        //check if data contains customers
        $this->assertArrayHasKey('customers', $jsonBody['data']);

        //first customer should be the one we added, ass there isn't any other process adding any s_statistics_currentusers
        $this->assertEquals($this->userId, $jsonBody['data']['customers'][0]['userID']);
        $this->assertEquals($addressData['firstname'] . ' ' . $addressData['lastname'], $jsonBody['data']['customers'][0]['customer']);

        /*
         * cleanup
         */
        $this->db->delete('s_statistics_currentusers', ['id = ?' => $this->statisticsId]);
        $this->db->delete('s_user_addresses', ['id = ?' => $this->addressId]);
        $this->db->delete('s_user', ['id = ?' => $this->userId]);
    }

    private function prepareTestGetVisitors($addressData)
    {
        $this->db->insert('s_user', [
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

        $this->userId = $this->db->lastInsertId('s_user');

        $addressData = array_merge(['user_id' => $this->userId], $addressData);
        $this->db->insert('s_user_addresses', $addressData);

        $this->addressId = $this->db->lastInsertId('s_user_addresses');

        /*
         * set default_billing_address_id
         */
        $this->db->update('s_user', [
            'default_billing_address_id' => $this->addressId,
        ], ['id = ?' => $this->userId]);

        $this->db->insert('s_statistics_currentusers', [
            'remoteaddr' => '127.0.0.1',
            'page' => '/',
            'time' => new Zend_Db_Expr('NOW()'),
            'userID' => $this->userId,
            'deviceType' => 'Test',
        ]);
        $this->statisticsId = $this->db->lastInsertId('s_statistics_currentusers');
    }
}
