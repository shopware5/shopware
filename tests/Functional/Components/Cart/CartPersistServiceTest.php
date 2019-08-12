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

namespace Shopware\Tests\Functional\Components\Cart;

use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace as Session;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Cart\CartPersistServiceInterface;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class CartPersistServiceTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var CartPersistServiceInterface
     */
    private $service;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var Session
     */
    private $session;

    public function setUp(): void
    {
        $this->service = Shopware()->Container()->get(CartPersistServiceInterface::class);
        $this->session = Shopware()->Session();
        $this->db = Shopware()->Container()->get('dbal_connection');
        $this->db->exec('DELETE FROM s_order_basket');
    }

    public function testEmptyCartPersist(): void
    {
        $this->service->prepare();
        $this->service->persist();

        static::assertEquals(0, $this->db->fetchColumn('SELECT COUNT(*) FROM s_order_basket'));
    }

    public function testWithFilledCart(): void
    {
        $this->db->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => 2,
                'sessionID' => $this->session->get('sessionId'),
            ]
        );

        $this->service->prepare();
        $this->service->persist();

        static::assertEquals(2, $this->db->fetchColumn('SELECT COUNT(*) FROM s_order_basket'));
    }

    public function testWithItemsAfterPrepareCart(): void
    {
        $this->service->prepare();

        $this->db->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => 2,
                'sessionID' => $this->session->get('sessionId'),
            ]
        );

        $this->service->persist();

        static::assertEquals(1, $this->db->fetchColumn('SELECT COUNT(*) FROM s_order_basket'));
    }

    public function testWithItemsWithoutPersist(): void
    {
        $this->service->prepare();

        $this->db->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => 2,
                'sessionID' => $this->session->get('sessionId'),
            ]
        );

        static::assertEquals(1, $this->db->fetchColumn('SELECT COUNT(*) FROM s_order_basket'));
    }

    public function testItemsPersistedItemsEquals(): void
    {
        $this->db->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => 2,
                'sessionID' => $this->session->get('sessionId'),
            ]
        );

        $lastInsertedId = $this->db->lastInsertId();
        $item = $this->db->fetchAssoc('SELECT * FROM s_order_basket WHERE id = ?', [$lastInsertedId]);
        unset($item['id'], $item['sessionID']);

        $this->service->prepare();
        $this->service->persist();

        static::assertEquals(2, $this->db->fetchColumn('SELECT COUNT(*) FROM s_order_basket'));

        $newItem = $this->db->fetchAssoc('SELECT * FROM s_order_basket ORDER BY id DESC');
        unset($newItem['id'], $newItem['sessionID']);

        static::assertEquals($newItem, $item);
    }
}
