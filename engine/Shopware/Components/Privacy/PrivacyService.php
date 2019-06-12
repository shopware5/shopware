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

namespace Shopware\Components\Privacy;

use Doctrine\DBAL\Connection;

class PrivacyService implements PrivacyServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanupGuestUsers($months)
    {
        $threshold = (new \DateTime())
            ->modify(sprintf('-%d months', $months))
            ->format('Y-m-d H:i:s');

        $query = <<<'SQL'
            DELETE u FROM `s_user` u
            LEFT JOIN `s_order` o ON o.`userID` = u.`id` AND o.`status` <> -1
            WHERE u.`accountmode` = 1
              AND u.`firstlogin` < :threshold
              AND o.`id` IS NULL
SQL;
        $this->connection->executeUpdate($query, ['threshold' => $threshold]);
    }

    /**
     * {@inheritdoc}
     */
    public function cleanupCanceledOrders($months)
    {
        $threshold = (new \DateTime())
            ->modify(sprintf('-%d months', $months))
            ->format('Y-m-d H:i:s');

        $query = <<<'SQL'
            DELETE o FROM `s_order` o
            WHERE o.`status` = -1 
              AND o.`ordertime` < :threshold 
SQL;
        $this->connection->executeUpdate($query, ['threshold' => $threshold]);

        $query = <<<'SQL'
            DELETE b FROM `s_order_basket` b
            WHERE b.`datum` < :threshold 
SQL;
        $this->connection->executeUpdate($query, ['threshold' => $threshold]);
    }
}
