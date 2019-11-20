<?php declare(strict_types=1);
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

interface TableMappingInterface
{
    /**
     * @param string $table
     * @param string $name
     *
     * @return bool
     */
    public function isIdentifierColumn($table, $name);

    /**
     * @param string $table
     * @param string $name
     *
     * @return bool
     */
    public function isCoreColumn($table, $name);

    /**
     * @param string $table
     *
     * @return string|null
     */
    public function getTableModel($table);

    /**
     * @return array
     */
    public function getAttributeTables();

    /**
     * @param string $table
     *
     * @return string
     */
    public function getTableForeignKey($table);

    /**
     * @param string $table
     *
     * @return bool
     */
    public function isAttributeTable($table);

    /**
     * @param string $table
     * @param string $column
     *
     * @return bool
     */
    public function isTableColumn($table, $column);

    /**
     * @param string $table
     *
     * @return array
     */
    public function getDependingTables($table);

    /**
     * @param string $table
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getTableColumns($table);
}
