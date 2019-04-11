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

namespace Shopware\Components\Cart;

use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace as Session;
use sBasket as Cart;

class CartMigration implements CartMigrationInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CartMigrationCleanerInterface
     */
    private $cartMigrationCleaner;

    public function __construct(
        Session $session,
        Cart $cart,
        Connection $connection,
        CartMigrationCleanerInterface $cartMigrationCleaner
    ) {
        $this->session = $session;
        $this->cart = $cart;
        $this->connection = $connection;
        $this->cartMigrationCleaner = $cartMigrationCleaner;
    }

    public function migrate(): void
    {
        if (!$this->canBeMigrated()) {
            $this->cartMigrationCleaner->cleanUp();

            return;
        }

        $this->connection
            ->update(
                's_order_basket',
                [
                    'sessionID' => $this->session->get('sessionId'),
                ],
                [
                    'userID' => $this->session->get('sUserId'),
                    'modus' => 0,
                ]
            );

        $this->cart->sRefreshBasket();
    }

    private function canBeMigrated(): bool
    {
        return !$this->connection->createQueryBuilder()
            ->select('1')
            ->from('s_order_basket', 'cart')
            ->where('sessionId = :sessionId')
            ->setParameter('sessionId', $this->session->get('sessionId'))
            ->execute()
            ->fetchColumn();
    }
}
