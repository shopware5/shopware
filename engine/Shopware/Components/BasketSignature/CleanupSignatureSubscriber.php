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

namespace Shopware\Components\BasketSignature;

use DateInterval;
use DateTime;
use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;

class CleanupSignatureSubscriber implements SubscriberInterface
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
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_CleanupSignatures' => 'cleanup',
        ];
    }

    public function cleanup()
    {
        $date = (new DateTime())
            ->sub(new DateInterval('P10D'))
            ->format('Y-m-d');

        $this->connection->executeUpdate(
            'DELETE FROM ' . BasketPersister::DBAL_TABLE . ' WHERE created_at < :createdAt',
            [':createdAt' => $date]
        );

        return true;
    }
}
