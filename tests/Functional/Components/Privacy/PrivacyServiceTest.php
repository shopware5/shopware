<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Components\Privacy;

use DateTime;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Privacy\PrivacyService;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

/**
 * @group EmotionPreset
 */
class PrivacyServiceTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PrivacyService
     */
    private $privacyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = Shopware()->Container()->get(Connection::class);

        $this->privacyService = Shopware()->Container()->get('shopware.components.privacy.privacy_service');
    }

    /**
     * Creates a user without an order, who should be deleted
     */
    public function testCleanupUserWithoutOrdersShouldBeDeleted(): void
    {
        $userId = $this->createCustomer(3);

        $this->privacyService->cleanupGuestUsers(3);

        static::assertFalse($this->getUserFromDb($userId));
    }

    /**
     * Creates a user without an order, who should not be deleted because of month
     */
    public function testCleanupUserWithoutOrdersShouldNotBeDeleted(): void
    {
        $userId = $this->createCustomer(3);

        $this->privacyService->cleanupGuestUsers(4);

        static::assertNotEmpty($this->getUserFromDb($userId));
    }

    /**
     * Creates a user with a canceled order, who should be deleted
     */
    public function testCleanupUserWithCanceledOrderShouldBeDeleted(): void
    {
        $userId = $this->createCustomer(3);

        $this->createOrder($userId, -1, 3);

        $this->privacyService->cleanupGuestUsers(3);

        static::assertEmpty($this->getUserFromDb($userId));
    }

    /**
     * Creates a user with canceled and valid orders, who should not be deleted
     */
    public function testCleanupUserWithCanceledAndValidOrdersShouldNotBeDeleted(): void
    {
        $userId = $this->createCustomer(3);

        $this->createOrder($userId, -1, 3);
        $this->createOrder($userId, 17, 2);

        $this->privacyService->cleanupGuestUsers(3);

        static::assertNotEmpty($this->getUserFromDb($userId));
    }

    /**
     * Creates canceled orders, which should be deleted
     */
    public function testCleanupCanceledOrdersShouldBeDeleted(): void
    {
        $userId = $this->createCustomer(3);

        $this->createOrder($userId, -1, 3);
        $this->createOrder($userId, -1, 4);

        $this->privacyService->cleanupCanceledOrders(3);

        static::assertEmpty($this->getOrdersByCustomerIdFromDb($userId));
    }

    /**
     * Creates canceled baskets, which should be deleted
     */
    public function testCleanupCanceledBasketsShouldBeDeleted(): void
    {
        $userId = $this->createCustomer(3);

        $this->createBasket($userId, 3);
        $this->createBasket($userId, 4);

        $this->privacyService->cleanupCanceledOrders(3);

        static::assertEmpty($this->getBasketsByCustomerIdFromDb($userId));
    }

    /**
     * Reads a user from the database
     *
     * @return array<string, mixed>|false
     */
    private function getUserFromDb(int $id)
    {
        return $this->connection->executeQuery('SELECT id FROM s_user WHERE id = ' . $id)->fetchAssociative();
    }

    /**
     * Reads orders of a user from the database
     *
     * @return array<array<string, mixed>>
     */
    private function getOrdersByCustomerIdFromDb(int $customerId): array
    {
        return $this->connection->executeQuery('SELECT id FROM s_order WHERE userID = "' . $customerId . '"')->fetchAllAssociative();
    }

    /**
     * Reads baskets of a user from the database
     *
     * @return array<array<string, mixed>>
     */
    private function getBasketsByCustomerIdFromDb(int $customerId): array
    {
        return $this->connection->executeQuery('SELECT id FROM s_order_basket WHERE userID = "' . $customerId . '"')->fetchAllAssociative();
    }

    /**
     * Creates a customer in the database who is registered since $sinceMonth month
     *
     * @param int $sinceMonth how many month the user should already be registered
     *
     * @return int id of the customer
     */
    private function createCustomer(int $sinceMonth): int
    {
        $sqlDate = $this->connection->fetchOne('SELECT NOW() - INTERVAL ' . $sinceMonth . ' MONTH');
        $date = (new DateTime($sqlDate))->format('Y-m-d');

        $this->connection->insert('s_user', [
                'password' => '098f6bcd4621d373cade4e832627b4f6',
                'encoder' => 'md5',
                'email' => uniqid('test', true) . '@test.com',
                'accountmode' => 1,
                'active' => '1',
                'firstlogin' => $date,
                'lastlogin' => $date,
                'subshopID' => '1',
                'customergroup' => 'EK',
                'salutation' => 'mr',
                'firstname' => '',
                'lastname' => '',
                'birthday' => '1990-01-01',
            ]);

        return (int) $this->connection->lastInsertId();
    }

    /**
     * Creates an order in the database
     *
     * @param int $userId user id of the order
     * @param int $status status of the order
     *
     * @return int id of the order
     */
    private function createOrder(int $userId, int $status, int $sinceMonth): int
    {
        $sqlDate = $this->connection->fetchOne('SELECT NOW() - INTERVAL ' . $sinceMonth . ' MONTH');
        $date = (new DateTime($sqlDate))->format('Y-m-d');

        $this->connection->insert('s_order', [
            'userID' => $userId,
            'ordertime' => $date,
            'status' => $status,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    /**
     * Creates a basket in the database
     *
     * @param int $customerId user id of the basket
     *
     * @return int id of the basket
     */
    private function createBasket(int $sinceMonth, int $customerId): int
    {
        $sqlDate = $this->connection->fetchOne('SELECT NOW() - INTERVAL ' . $sinceMonth . ' MONTH');
        $date = (new DateTime($sqlDate))->format('Y-m-d');

        $this->connection->insert('s_order_basket', [
            'userID' => $customerId,
            'datum' => $date,
        ]);

        return (int) $this->connection->lastInsertId();
    }
}
