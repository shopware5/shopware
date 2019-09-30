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

class TypeMapping implements TypeMappingInterface
{
    /**
     * @var array
     */
    private $types = [
        TypeMappingInterface::TYPE_STRING => [
            'sql' => 'TEXT',
            'dbal' => 'string',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'string'],
        ],
        TypeMappingInterface::TYPE_TEXT => [
            'sql' => 'TEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'string'],
        ],
        TypeMappingInterface::TYPE_HTML => [
            'sql' => 'MEDIUMTEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'string'],
        ],
        TypeMappingInterface::TYPE_INTEGER => [
            'sql' => 'INT(11)',
            'dbal' => 'integer',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'long'],
        ],
        TypeMappingInterface::TYPE_FLOAT => [
            'sql' => 'DOUBLE',
            'dbal' => 'float',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'double'],
        ],
        TypeMappingInterface::TYPE_BOOLEAN => [
            'sql' => 'INT(1)',
            'dbal' => 'boolean',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'boolean'],
        ],
        TypeMappingInterface::TYPE_DATE => [
            'sql' => 'DATE',
            'dbal' => 'date',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => true,
            'elastic' => ['type' => 'date', 'format' => 'yyyy-MM-dd'],
        ],
        TypeMappingInterface::TYPE_DATETIME => [
            'sql' => 'DATETIME',
            'dbal' => 'datetime',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => true,
            'elastic' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
        ],
        TypeMappingInterface::TYPE_COMBOBOX => [
            'sql' => 'MEDIUMTEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'string'],
        ],
        TypeMappingInterface::TYPE_SINGLE_SELECTION => [
            'sql' => 'TEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => ['type' => 'string'],
        ],
        TypeMappingInterface::TYPE_MULTI_SELECTION => [
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
        'array' => TypeMappingInterface::TYPE_TEXT,
        'simple_array' => TypeMappingInterface::TYPE_TEXT,
        'json_array' => TypeMappingInterface::TYPE_TEXT,
        'bigint' => TypeMappingInterface::TYPE_INTEGER,
        'boolean' => TypeMappingInterface::TYPE_BOOLEAN,
        'datetime' => TypeMappingInterface::TYPE_DATETIME,
        'datetimetz' => TypeMappingInterface::TYPE_DATE,
        'date' => TypeMappingInterface::TYPE_DATE,
        'time' => TypeMappingInterface::TYPE_STRING,
        'decimal' => TypeMappingInterface::TYPE_FLOAT,
        'integer' => TypeMappingInterface::TYPE_INTEGER,
        'object' => TypeMappingInterface::TYPE_TEXT,
        'smallint' => TypeMappingInterface::TYPE_INTEGER,
        'string' => TypeMappingInterface::TYPE_STRING,
        'text' => TypeMappingInterface::TYPE_TEXT,
        'binary' => TypeMappingInterface::TYPE_TEXT,
        'blob' => TypeMappingInterface::TYPE_TEXT,
        'float' => TypeMappingInterface::TYPE_FLOAT,
        'guid' => TypeMappingInterface::TYPE_TEXT,
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
