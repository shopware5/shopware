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

namespace Shopware\Components\Theme;

use Doctrine\DBAL\Connection;

class DBALTimestampPersistor implements TimestampPersistor
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
    public function getCurrentTimestamp($shopId)
    {
        $sql = <<<'sql'
SELECT s_core_config_values.value FROM s_core_config_values
INNER JOIN s_core_config_elements 
    ON s_core_config_values.element_id = s_core_config_elements.id 
    AND s_core_config_elements.`name` LIKE 'assetTimestamp'
WHERE s_core_config_values.shop_id = :shopId
sql;

        $timestamp = $this->connection->fetchColumn($sql, ['shopId' => $shopId]);

        if ($timestamp !== false && ($timestamp = unserialize($timestamp, ['allowed_classes' => false])) !== false) {
            return $timestamp;
        }

        $timestamp = time();
        $this->updateTimestamp($shopId, $timestamp);

        return (string) $timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($shopId, $timestamp)
    {
        $sql = <<<'sql'
INSERT INTO s_core_config_values (`element_id`, `shop_id`, `value`) VALUES (
    (SELECT id FROM s_core_config_elements WHERE `name` LIKE 'assetTimestamp' LIMIT 1),
    :shopId, 
    :value
) ON DUPLICATE KEY UPDATE s_core_config_values.value = :value;
sql;

        $this->connection->executeUpdate(
            $sql,
            [
                'value' => serialize($timestamp),
                'shopId' => $shopId,
            ]
        );
    }
}
