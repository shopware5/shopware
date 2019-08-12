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

namespace Shopware\Bundle\PluginInstallerBundle\Service\UniqueIdGenerator;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\PluginInstallerBundle\Service\UniqueIdGeneratorInterface;
use Shopware\Components\Random;

/**
 * A simple class for storing a generated unique Id in the database.
 */
class UniqueIdGenerator implements UniqueIdGeneratorInterface
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
     * @return string
     */
    public function getUniqueId()
    {
        if ($uniqueId = $this->readUniqueIdFromDb()) {
            return $uniqueId;
        }

        $uniqueId = Random::getAlphanumericString(32);

        $this->storeUniqueIdInDb($uniqueId);

        return $uniqueId;
    }

    /**
     * @return string|null
     */
    private function readUniqueIdFromDb()
    {
        $sql = <<<'sql'
SELECT s_core_config_values.value FROM s_core_config_values
INNER JOIN s_core_config_elements 
    ON s_core_config_values.element_id = s_core_config_elements.id 
    AND s_core_config_elements.name LIKE 'trackingUniqueId'
WHERE s_core_config_values.shop_id = 1
sql;
        $uniqueId = $this->connection->fetchColumn($sql);

        if ($uniqueId !== false && is_string($uniqueId)) {
            return unserialize($uniqueId, ['allowed_classes' => false]);
        }

        return null;
    }

    /**
     * @param string $uniqueId
     */
    private function storeUniqueIdInDb($uniqueId)
    {
        $sql = <<<'sql'
INSERT INTO s_core_config_values (element_id, shop_id, value) VALUES (
(SELECT id FROM s_core_config_elements WHERE name LIKE 'trackingUniqueId' LIMIT 1), 1, :value
)
sql;

        $this->connection->executeUpdate(
            $sql, [
                'value' => serialize($uniqueId),
            ]
        );
    }
}
