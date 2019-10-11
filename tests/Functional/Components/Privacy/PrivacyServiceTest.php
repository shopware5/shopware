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

namespace Shopware\tests\Functional\Components\Privacy;

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

    /** @var Connection */
    private $connection;

    /** @var PrivacyService */
    private $privacyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = Shopware()->Container()->get('dbal_connection');

        $this->privacyService = Shopware()->Container()->get('shopware.components.privacy.privacy_service');
    }

    /**
     * Creates a user without an order, who should be deleted
     */
    public function testCleanupUserWithoutOrdersShouldBeDeleted()
    {
        $userId = $this->createCustomer(3);

        $this->privacyService->cleanupGuestUsers(3);

        static::assertEmpty($this->getUserFromDb($userId));
    }

    /**
     * Creates a user without an order, who should not be deleted because of month
     */
    public function testCleanupUserWithoutOrdersShouldNotBeDeleted()
    {
        $userId = $this->createCustomer(3);

        $this->privacyService->cleanupGuestUsers(4);

        static::assertNotEmpty($this->getUserFromDb($userId));
    }

    /**
     * Creates a user with a canceled order, who should be deleted
     */
    public function testCleanupUserWithCanceledOrderShouldBeDeleted()
    {
        $userId = $this->createCustomer(3);

        $this->createOrder($userId, -1, 3);

        $this->privacyService->cleanupGuestUsers(3);

        static::assertEmpty($this->getUserFromDb($userId));
    }

    /**
     * Creates a user with canceled and valid orders, who should not be deleted
     */
    public function testCleanupUserWithCanceledAndValidOrdersShouldNotBeDeleted()
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
    public function testCleanupCanceledOrdersShouldBeDeleted()
    {
        $userId = $this->createCustomer(3);

        $this->createOrder($userId, -1, 3);
        $this->createOrder($userId, -1, 4);

        $this->privacyService->cleanupCanceledOrders(3);

        static::assertEmpty($this->getOrdersByUserIdFromDb($userId));
    }

    /**
     * Creates canceled baskets, which should be deleted
     */
    public function testCleanupCanceledBasketsShouldBeDeleted()
    {
        $userId = $this->createCustomer(3);

        $this->createBasket($userId, 3);
        $this->createBasket($userId, 4);

        $this->privacyService->cleanupCanceledOrders(3);

        static::assertEmpty($this->getBasketsByUserIdFromDb($userId));
    }

    /**
     * Reads a user from the database
     *
     * @param string $id
     *
     * @return array
     */
    private function getUserFromDb($id)
    {
        return $this->connection->query('SELECT id FROM s_user WHERE id = ' . $id)->fetch();
    }

    /**
     * Reads orders of a user from the database
     *
     * @param string $userId
     *
     * @return array
     */
    private function getOrdersByUserIdFromDb($userId)
    {
        return $this->connection->query('SELECT id FROM s_order WHERE userID = "' . $userId . '"')->fetchAll();
    }

    /**
     * Reads baskets of a user from the database
     *
     * @param string $userId
     *
     * @return array
     */
    private function getBasketsByUserIdFromDb($userId)
    {
        return $this->connection->query('SELECT id FROM s_order_basket WHERE userID = "' . $userId . '"')->fetchAll();
    }

    /**
     * Creates a customer in the database who is registered since $sinceMonth month
     *
     * @param int $sinceMonth how many month the user should already be registered
     *
     * @return int id of the customer
     */
    private function createCustomer($sinceMonth)
    {
        $sqlDate = $this->connection->fetchColumn('SELECT NOW() - INTERVAL ' . $sinceMonth . ' MONTH');
        $date = (new \DateTime($sqlDate))->format('Y-m-d');

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
     * Creates a order in the database
     *
     * @param int $userId     user id of the order
     * @param int $status     status of the order
     * @param int $sinceMonth
     *
     * @return int id of the order
     */
    private function createOrder($userId, $status, $sinceMonth)
    {
        $sqlDate = $this->connection->fetchColumn('SELECT NOW() - INTERVAL ' . $sinceMonth . ' MONTH');
        $date = (new \DateTime($sqlDate))->format('Y-m-d');

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
     * @param int $userId     user id of the basket
     * @param int $sinceMonth
     *
     * @return int id of the basket
     */
    private function createBasket($sinceMonth, $userId)
    {
        $sqlDate = $this->connection->fetchColumn('SELECT NOW() - INTERVAL ' . $sinceMonth . ' MONTH');
        $date = (new \DateTime($sqlDate))->format('Y-m-d');

        $this->connection->insert('s_order_basket', [
            'userID' => $userId,
            'datum' => $date,
        ]);

        return $this->connection->lastInsertId();
    }
}
