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
namespace Shopware\Bundle\AttributeBundle\Service;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\AttributeBundle\Service
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
interface CrudServiceInterface
{
    /**
     * @param string $table
     * @param string $column
     * @param bool   $updateDependingTables
     *
     * @throws \Exception
     */
    public function delete($table, $column, $updateDependingTables = false);

    /**
     * @param string                $table
     * @param string                $columnName
     * @param string                $unifiedType
     * @param array                 $data
     * @param null                  $newColumnName
     * @param bool                  $updateDependingTables
     * @param null|string|int|float $defaultValue
     *
     * @throws \Exception
     */
    public function update(
        $table,
        $columnName,
        $unifiedType,
        array $data = [],
        $newColumnName = null,
        $updateDependingTables = false,
        $defaultValue = null
    );

    /**
     * @param string $table
     * @param string $columnName
     *
     * @return ConfigurationStruct|null
     */
    public function get($table, $columnName);

    /**
     * @param string $table
     *
     * @return ConfigurationStruct[]
     */
    public function getList($table);
}
