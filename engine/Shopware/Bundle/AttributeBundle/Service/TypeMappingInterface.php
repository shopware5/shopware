<?php

declare(strict_types=1);
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

use Doctrine\DBAL\Types\Type;

interface TypeMappingInterface
{
    public const TYPE_STRING = 'string';
    public const TYPE_TEXT = 'text';
    public const TYPE_HTML = 'html';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_FLOAT = 'float';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_DATE = 'date';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_COMBOBOX = 'combobox';
    public const TYPE_SINGLE_SELECTION = 'single_selection';
    public const TYPE_MULTI_SELECTION = 'multi_selection';

    public const MAPPING_DATE_FIELD = ['type' => 'date', 'format' => 'yyyy-MM-dd'];
    public const MAPPING_DATE_TIME_FIELD = ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'];
    public const MAPPING_LONG_FIELD = ['type' => 'long'];
    public const MAPPING_BOOLEAN_FIELD = ['type' => 'boolean'];
    public const MAPPING_DOUBLE_FIELD = ['type' => 'double'];
    public const MAPPING_STRING_FIELD = ['type' => 'string'];

    /**
     * @return array
     */
    public function getTypes();

    /**
     * @return array<array>
     */
    public function getEntities();

    /**
     * @return string
     */
    public function dbalToUnified(Type $type);

    /**
     * @param string $type
     *
     * @return string
     */
    public function unifiedToSQL($type);

    /**
     * @param string $unified
     *
     * @return array
     */
    public function unifiedToElasticSearch($unified);
}
