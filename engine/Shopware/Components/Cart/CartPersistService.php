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

namespace Shopware\Components\Cart;

use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace as Session;
use PDO;

class CartPersistService implements CartPersistServiceInterface
{
    private Connection $connection;

    private Session $session;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $cart = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $cartAttributes = [];

    public function __construct(Connection $connection, Session $session)
    {
        $this->connection = $connection;
        $this->session = $session;
    }

    public function prepare(): void
    {
        $qb = $this->connection->createQueryBuilder();
        $this->cart = $qb->from('s_order_basket', 'cart')
            ->select('cart.*')
            ->andWhere('cart.modus = 0')
            ->andWhere('cart.sessionId = :sessionId')
            ->setParameter('sessionId', $this->session->get('sessionId'))
            ->execute()
            ->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        if (empty($this->cart)) {
            return;
        }

        $ids = array_keys($this->cart);

        $qb = $this->connection->createQueryBuilder();
        $this->cartAttributes = $qb->from('s_order_basket_attributes', 'cartAttributes')
            ->addSelect('cartAttributes.basketID')
            ->addSelect('cartAttributes.*')
            ->andWhere(
                $qb->expr()->in('cartAttributes.basketID', $ids)
            )
            ->execute()
            ->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    public function persist(): void
    {
        foreach ($this->cart as $id => $item) {
            $this->connection->insert('s_order_basket', $item);
            $lastId = $this->connection->lastInsertId();

            if (isset($this->cartAttributes[$id])) {
                $attribute = $this->cartAttributes[$id];
                unset($attribute['id']);
                $attribute['basketID'] = $lastId;
                $this->connection->insert('s_order_basket_attributes', $attribute);
            }
        }

        $this->cart = [];
        $this->cartAttributes = [];
    }
}
