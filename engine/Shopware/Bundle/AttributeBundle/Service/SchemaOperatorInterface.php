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

interface SchemaOperatorInterface
{
    /**
     * @param string                $table
     * @param string                $column
     * @param string                $type
     * @param string|int|float|null $defaultValue
     */
    public function createColumn($table, $column, $type, $defaultValue = null);

    /**
     * @param string                $table
     * @param string                $originalName
     * @param string                $newName
     * @param string                $type
     * @param string|int|float|null $defaultValue
     */
    public function changeColumn($table, $originalName, $newName, $type, $defaultValue = null);

    /**
     * @param string $table
     * @param string $column
     */
    public function dropColumn($table, $column);

    /**
     * Updates the provided column data to sql NULL value
     *
     * @param string $table
     * @param string $column
     */
    public function resetColumn($table, $column);
}
