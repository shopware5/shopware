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
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;

class ConfigWriter
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
     * @param string      $name
     * @param string|null $namespace
     * @param int         $shopId
     */
    public function get($name, $namespace = null, $shopId = 1)
    {
        $query = $this->getConfigValueByNameQuery($name, $namespace, $shopId);

        $result = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        if ($result['configured']) {
            return unserialize($result['configured'], ['allowed_classes' => false]);
        }

        return unserialize($result['value'], ['allowed_classes' => false]);
    }

    /**
     * @param string      $name
     * @param string|null $namespace
     * @param int         $shopId
     */
    public function save($name, $value, $namespace = null, $shopId = 1)
    {
        $value = serialize($value);

        $query = $this->getConfigValueByNameQuery($name, $namespace, $shopId);

        $result = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        if ($result['valueId']) {
            $this->update($value, $result['valueId']);

            return;
        }

        $this->insert($value, $shopId, $result['elementId']);
    }

    /**
     * @param string      $name
     * @param string|null $namespace
     * @param int         $shopId
     *
     * @return QueryBuilder
     */
    private function getConfigValueByNameQuery($name, $namespace = null, $shopId = 1)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'element.id as elementId',
            'element.value',
            'elementValues.id as valueId',
            'elementValues.value as configured',
        ]);

        $query->from('s_core_config_elements', 'element')
            ->leftJoin('element', 's_core_config_values', 'elementValues', 'elementValues.element_id = element.id AND elementValues.shop_id = :shopId')
            ->where('element.name = :name')
            ->setParameter(':shopId', $shopId)
            ->setParameter(':name', $name);

        if ($namespace) {
            $query->innerJoin('element', 's_core_config_forms', 'elementForm', 'elementForm.id = element.form_id AND elementForm.name = :namespace')
                ->setParameter(':namespace', $namespace);
        }

        return $query;
    }

    /**
     * @param string $value
     * @param int    $valueId
     *
     * @throws DBALException
     */
    private function update($value, $valueId)
    {
        $this->connection->executeUpdate(
            'UPDATE s_core_config_values SET value = :value WHERE id = :id',
            [':value' => $value, ':id' => $valueId]
        );
    }

    /**
     * @param string $value
     * @param int    $shopId
     * @param int    $elementId
     *
     * @throws DBALException
     */
    private function insert($value, $shopId, $elementId)
    {
        $this->connection->executeUpdate(
            'INSERT INTO s_core_config_values (element_id, shop_id, value) VALUES (:elementId, :shopId, :value)',
            [':elementId' => $elementId, ':value' => $value, 'shopId' => $shopId]
        );
    }
}
