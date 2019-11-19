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

interface CrudServiceInterface
{
    const EXT_JS_PREFIX = '__attribute_';
    const NULL_STRING = 'NULL';

    /**
     * @param string $table
     * @param string $column
     * @param bool   $updateDependingTables
     */
    public function delete($table, $column, $updateDependingTables = false);

    /**
     * Translations for different fields (help, support, label) can be configured via snippets.
     * Snippet namespace         :  backend/attribute_columns
     * Snippet name label        :  s_articles_attributes_attr1_label
     * Snippet name support text :  s_articles_attributes_attr1_supportText
     * Snippet name help text    :  s_articles_attributes_attr1_helpText
     *
     * @param string                $table
     * @param string                $columnName
     * @param string                $unifiedType
     * @param string|null           $newColumnName
     * @param bool                  $updateDependingTables
     * @param string|int|float|null $defaultValue
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
