<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Closure;
use DateInterval;
use DateTime;
use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase;
use Enlight_Plugin_Namespace;
use Enlight_View;
use Generator;
use PHPUnit\Framework\Constraint\Constraint;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Controllers_Backend_Widgets;
use Shopware_Plugins_Backend_Auth_Bootstrap;
use Zend_Db_Expr;

class WidgetsTest extends Enlight_Components_Test_Controller_TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

    private Connection $connection;

    private int $userId;

    private Shopware_Plugins_Backend_Auth_Bootstrap $authPlugin;

    public function setUp(): void
    {
        parent::setUp();

        $pluginManager = $this->getContainer()->get('plugin_manager');
        $backendPlugins = $pluginManager->get('Backend');
        static::assertInstanceOf(Enlight_Plugin_Namespace::class, $backendPlugins);
        $authPlugin = $backendPlugins->get('Auth');
        static::assertInstanceOf(Shopware_Plugins_Backend_Auth_Bootstrap::class, $authPlugin);
        $this->authPlugin = $authPlugin;

        $this->authPlugin->setNoAuth();
        $this->authPlugin->setNoAcl();

        $this->connection = $this->getContainer()->get(Connection::class);

        $this->connection->executeStatement('DELETE FROM s_statistics_visitors');
        $this->connection->executeStatement('DELETE FROM s_order');
        $this->connection->executeStatement('DELETE FROM s_user');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->authPlugin->setNoAuth(false);
        $this->authPlugin->setNoAcl(false);
    }

    public function testConversionIsEmpty(): void
    {
        $this->dispatch('backend/widgets/getTurnOverVisitors');

        $response = $this->View()->getAssign();

        static::assertTrue($response['success']);
        static::assertEquals('0.00', $response['conversion']);
    }

    public function testConversionIsCalculatedFromBeginningOfDay(): void
    {
        $date = new DateTime();
        $date->sub(new DateInterval('P7DT1M'));

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

    public function testConversionStillWorks(): void
    {
        $date = new DateTime();
        $date->sub(new DateInterval('P6DT59M'));

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

    public function testIfNoConversionAfterEightDays(): void
    {
        $date = new DateTime();
        $date->sub(new DateInterval('P8D'));

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
    public function testGetVisitorsWithCompanyAction(): void
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
        // Check if response has data
        static::assertArrayHasKey('data', $response);
        // Check if data contains customers
        static::assertArrayHasKey('customers', $response['data']);

        // First customer should be the one we added, ass there isn't any other process adding any s_statistics_currentusers
        static::assertEquals($this->userId, $response['data']['customers'][0]['userID']);
        static::assertEquals($addressData['company'], $response['data']['customers'][0]['customer']);
    }

    public function testGetVisitorsWithoutCompanyAction(): void
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
        // Check if response has data
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

    public function testGetVisitorsWithEmptyCompanyAction(): void
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
        // Check if response has data
        static::assertArrayHasKey('data', $response);
        // Check if data contains customers
        static::assertArrayHasKey('customers', $response['data']);

        // First customer should be the one we added, ass there isn't any other process adding any s_statistics_currentusers
        static::assertEquals($this->userId, $response['data']['customers'][0]['userID']);
        static::assertEquals($addressData['firstname'] . ' ' . $addressData['lastname'], $response['data']['customers'][0]['customer']);
    }

    /**
     * @dataProvider backendAuthProvider
     */
    public function testGetNoticeChecksBackendAuth(object $auth, Enlight_View $view): void
    {
        $controller = $this->createController($view);

        $_SESSION['ShopwareBackend']['Auth'] = $auth;

        $controller->getNoticeAction();

        unset($_SESSION);
    }

    /**
     * @dataProvider backendAuthProvider
     */
    public function testSaveNoticeChecksBackendAuth(object $auth, Enlight_View $view): void
    {
        $this->Request()->setParam('notice', 'bf0b9d61-8f55-4f2c-818b-9d0891178df8');

        $controller = $this->createController($view);
        $controller->setRequest($this->Request());

        $_SESSION['ShopwareBackend']['Auth'] = $auth;

        $controller->saveNoticeAction();

        unset($_SESSION);
    }

    /**
     * @return Generator<string, array>
     */
    public function backendAuthProvider(): Generator
    {
        yield 'invalid auth' => [
            (object) [],
            $this->getViewMockCheckingForAssign([
                static::callback($this->getResponseSuccessValidator(false)),
                static::anything(),
                static::anything(),
                static::anything(),
            ]),
        ];

        yield 'valid auth' => [
            (object) ['id' => 1],
            $this->getViewMockCheckingForAssign([
                static::callback($this->getResponseSuccessValidator(true)),
                static::anything(),
                static::anything(),
                static::anything(),
            ]),
        ];
    }

    /**
     * @param array<Constraint> $arguments
     */
    private function getViewMockCheckingForAssign(array $arguments): Enlight_View
    {
        $view = $this->createMock(Enlight_View::class);
        $view->expects(static::once())
            ->method('assign')
            ->with(...$arguments);

        return $view;
    }

    /**
     * @return Closure(array<string, mixed>): bool
     */
    private function getResponseSuccessValidator(bool $expectSuccess): callable
    {
        return static function (array $response) use ($expectSuccess): bool {
            return $response['success'] === $expectSuccess;
        };
    }

    private function prepareTestGetVisitors(array $addressData): void
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

        $this->userId = (int) $this->connection->lastInsertId('s_user');

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
            'time' => new Zend_Db_Expr('NOW()'),
            'userID' => $this->userId,
            'deviceType' => 'Test',
        ]);
    }

    private function createController(Enlight_View $view): Shopware_Controllers_Backend_Widgets
    {
        $controller = new Shopware_Controllers_Backend_Widgets();
        $controller->setContainer($this->getContainer());
        $controller->setView($view);

        return $controller;
    }
}
