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

namespace Shopware\Components;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\ConditionInterface;

class ProductStreamRepository
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var ReflectionHelper
     */
    private $reflector;

    /**
     * @param Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
        $this->reflector = new ReflectionHelper();
    }

    /**
     * @param int $productStreamId
     * @return ConditionInterface[]
     */
    public function getConditionsByProductStreamId($productStreamId)
    {
        $serializedConditions = $this->getConditions($productStreamId);
        $conditions = $this->unserializeConditions($serializedConditions);

        return $conditions;
    }



    /**
     * @param int $productStreamId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getConditions($productStreamId)
    {
        $row = $this->conn->fetchAssoc(
            "SELECT * FROM s_product_stream WHERE id = :productStreamId LIMIT 1",
            ['productStreamId' => $productStreamId]
        );

        $conditions = json_decode($row['conditions'], true);

        return $conditions;
    }

    /**
     * @param array $serializedConditions
     * @return ConditionInterface[]
     */
    public function unserializeConditions($serializedConditions)
    {
        $conditions = [];
        foreach ($serializedConditions as $className => $arguments) {
            $className = explode('|', $className);
            $className = $className[0];

            $conditions[] = $this->reflector->createInstanceFromNamedArguments($className, $arguments);
        }

        return $conditions;
    }
}
