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

use Doctrine\DBAL\Types\Type;

class TypeMapping
{
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_HTML = 'html';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_COMBOBOX = 'combobox';
    const TYPE_SINGLE_SELECTION = 'single_selection';
    const TYPE_MULTI_SELECTION = 'multi_selection';

    /**
     * @var array
     */
    private $types = [
        self::TYPE_STRING => [
            'sql' => 'TEXT',
            'dbal' => 'string',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'string'],
        ],
        self::TYPE_TEXT => [
            'sql' => 'TEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'string'],
        ],
        self::TYPE_HTML => [
            'sql' => 'MEDIUMTEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'string'],
        ],
        self::TYPE_INTEGER => [
            'sql' => 'INT(11)',
            'dbal' => 'integer',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'long'],
        ],
        self::TYPE_FLOAT => [
            'sql' => 'DOUBLE',
            'dbal' => 'float',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'double'],
        ],
        self::TYPE_BOOLEAN => [
            'sql' => 'INT(1)',
            'dbal' => 'boolean',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'boolean'],
        ],
        self::TYPE_DATE => [
            'sql' => 'DATE',
            'dbal' => 'date',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => true,
            'elastic' => ['type' => 'date', 'format' => 'yyyy-MM-dd'],
        ],
        self::TYPE_DATETIME => [
            'sql' => 'DATETIME',
            'dbal' => 'datetime',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => true,
            'elastic' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
        ],
        self::TYPE_COMBOBOX => [
            'sql' => 'MEDIUMTEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'string'],
        ],
        self::TYPE_SINGLE_SELECTION => [
            'sql' => 'TEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'string'],
        ],
        self::TYPE_MULTI_SELECTION => [
            'sql' => 'MEDIUMTEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'string'],
        ],
    ];

    /**
     * @var array
     */
    private $dbalTypes = [
        'array' => self::TYPE_TEXT,
        'simple_array' => self::TYPE_TEXT,
        'json_array' => self::TYPE_TEXT,
        'bigint' => self::TYPE_INTEGER,
        'boolean' => self::TYPE_BOOLEAN,
        'datetime' => self::TYPE_DATETIME,
        'datetimetz' => self::TYPE_DATE,
        'date' => self::TYPE_DATE,
        'time' => self::TYPE_STRING,
        'decimal' => self::TYPE_FLOAT,
        'integer' => self::TYPE_INTEGER,
        'object' => self::TYPE_TEXT,
        'smallint' => self::TYPE_INTEGER,
        'string' => self::TYPE_STRING,
        'text' => self::TYPE_TEXT,
        'binary' => self::TYPE_TEXT,
        'blob' => self::TYPE_TEXT,
        'float' => self::TYPE_FLOAT,
        'guid' => self::TYPE_TEXT,
    ];

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippets;

    public function __construct(\Shopware_Components_Snippet_Manager $snippets)
    {
        $this->snippets = $snippets;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        $result = [];
        foreach ($this->types as $unified => $type) {
            $type['unified'] = $unified;
            $result[$unified] = $type;
        }

        return $result;
    }

    /**
     * @return array<array>
     */
    public function getEntities()
    {
        $snippets = $this->snippets->getNamespace('backend/attributes/main');

        $entities = [
            'Shopware\Models\Article\Article',
            'Shopware\Models\Article\Detail',
            'Shopware\Models\Media\Media',
            'Shopware\Models\ProductStream\ProductStream',
            'Shopware\Models\Property\Option',
            'Shopware\Models\Property\Value',
            'Shopware\Models\Category\Category',
            'Shopware\Models\Article\Supplier',
            'Shopware\Models\Blog\Blog',
            'Shopware\Models\Form\Form',
            'Shopware\Models\Customer\Customer',
            'Shopware\Models\CustomerStream\CustomerStream',
            'Shopware\Models\Dispatch\Dispatch',
            'Shopware\Models\Payment\Payment',
            'Shopware\Models\Mail\Mail',
            'Shopware\Models\Emotion\Emotion',
            'Shopware\Models\Premium\Premium',
            'Shopware\Models\Voucher\Voucher',
            'Shopware\Models\ProductFeed\ProductFeed',
            'Shopware\Models\Newsletter\Newsletter',
            'Shopware\Models\Partner\Partner',
            'Shopware\Models\Shop\Shop',
            'Shopware\Models\Site\Site',
            'Shopware\Models\Country\Country',
        ];

        $result = [];
        foreach ($entities as $entity) {
            $result[] = ['entity' => $entity, 'label' => $snippets->get($entity)];
        }

        return $result;
    }

    /**
     * @return string
     */
    public function dbalToUnified(Type $type)
    {
        $name = strtolower($type->getName());

        if (!isset($this->dbalTypes)) {
            return 'string';
        }

        return $this->dbalTypes[$name];
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function unifiedToSQL($type)
    {
        $type = strtolower($type);
        if (!isset($this->types[$type])) {
            return $this->types['string']['sql'];
        }
        $mapping = $this->types[$type];

        return $mapping['sql'];
    }

    /**
     * @param string $unified
     *
     * @return array
     */
    public function unifiedToElasticSearch($unified)
    {
        $type = strtolower($unified);
        if (isset($this->types[$type])) {
            return $this->types[$type]['elastic'];
        }

        return ['type' => 'string'];
    }
}
