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

namespace Shopware\Recovery\Common\Service;

/**
 * Reads the generated unique Id from the generator and stores it into the database.
 */
class UniqueIdPersister
{
    /**
     * @var UniqueIdGenerator
     */
    private $uniqueIdGenerator;

    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(UniqueIdGenerator $uniqueIdGenerator, \PDO $connection)
    {
        $this->uniqueIdGenerator = $uniqueIdGenerator;
        $this->connection = $connection;
    }

    public function store()
    {
        $sql = <<<'sql'
INSERT INTO s_core_config_values (element_id, shop_id, value) VALUES (
(SELECT id FROM s_core_config_elements WHERE name LIKE 'trackingUniqueId' LIMIT 1), 1, :value
) ON DUPLICATE KEY UPDATE value=:value
sql;

        $serializedUniqueId = serialize($this->uniqueIdGenerator->getUniqueId());

        $statement = $this->connection->prepare($sql);
        $statement->bindParam('value', $serializedUniqueId);
        $statement->execute();
    }
}
