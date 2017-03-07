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

namespace Shopware\Bundle\CartBundle\Domain\Cart;

use Doctrine\DBAL\Connection;

class CartPersister implements CartPersisterInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $token): Cart
    {
        $content = $this->connection->fetchColumn(
            'SELECT content FROM s_cart WHERE `token` = :token',
            [':token' => $token]
        );

        if ($content === false) {
            throw new \RuntimeException('Cart token not found');
        }

        return Cart::unserialize($content);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Cart $cart): void
    {
        $this->connection->executeQuery(
            'INSERT INTO `s_cart` (`token`, `name`, `content`) 
             VALUES (:token, :name, :content)
             ON DUPLICATE KEY UPDATE `name` = :name, `content` = :content',
            [
                ':token' => $cart->getToken(),
                ':name' => $cart->getName(),
                ':content' => $cart->serialize(),
            ]
        );
    }
}
