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

namespace Shopware\Components\BasketSignature;

use Doctrine\DBAL\Connection;

class BasketPersister
{
    const DBAL_TABLE = 's_order_basket_signatures';

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * saves signed basket
     *
     * @param string $signature
     * @param array  $basket
     *
     * @throws \Exception
     */
    public function persist($signature, $basket)
    {
        $this->connection->transactional(
            function () use ($signature, $basket) {
                $createdAt = new \DateTime();

                $this->delete($signature);

                $this->connection->insert(self::DBAL_TABLE, [
                    'signature' => $signature,
                    'basket' => json_encode($basket),
                    'created_at' => $createdAt->format('Y-m-d'),
                ]);
            }
        );
    }

    /**
     * loads a signed basket by the given signature
     *
     * @param string $signature
     *
     * @return array
     */
    public function load($signature)
    {
        $basket = $this->connection->fetchColumn(
            'SELECT basket FROM ' . self::DBAL_TABLE . ' WHERE signature = :signature',
            [':signature' => $signature]
        );

        return json_decode($basket, true);
    }

    /**
     * deletes a signed basket by the given signature
     *
     * @param string $signature
     */
    public function delete($signature)
    {
        $this->connection->executeQuery(
            'DELETE FROM ' . self::DBAL_TABLE . ' WHERE signature = :signature',
            [':signature' => $signature]
        );
    }
}
