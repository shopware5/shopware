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
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Blog\Blog;
use Shopware\Models\Category\Category;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Customer;
use Shopware\Models\CustomerStream\CustomerStream;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Emotion\Emotion;
use Shopware\Models\Form\Form;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Media\Media;
use Shopware\Models\Newsletter\Newsletter;
use Shopware\Models\Partner\Partner;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Premium\Premium;
use Shopware\Models\ProductFeed\ProductFeed;
use Shopware\Models\ProductStream\ProductStream;
use Shopware\Models\Property\Option;
use Shopware\Models\Property\Value;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Site\Site;
use Shopware\Models\Voucher\Voucher;
use Shopware_Components_Snippet_Manager;

class TypeMapping implements TypeMappingInterface
{
    /**
     * @var array<string, array{sql: string, dbal: string, allowDefaultValue: bool, quoteDefaultValue: bool, elastic: array}>
     */
    private array $types = [
        TypeMappingInterface::TYPE_STRING => [
            'sql' => 'TEXT',
            'dbal' => 'string',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => self::MAPPING_STRING_FIELD,
        ],
        TypeMappingInterface::TYPE_TEXT => [
            'sql' => 'TEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => self::MAPPING_STRING_FIELD,
        ],
        TypeMappingInterface::TYPE_HTML => [
            'sql' => 'MEDIUMTEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => self::MAPPING_STRING_FIELD,
        ],
        TypeMappingInterface::TYPE_INTEGER => [
            'sql' => 'INT(11)',
            'dbal' => 'integer',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => false,
            'elastic' => self::MAPPING_LONG_FIELD,
        ],
        TypeMappingInterface::TYPE_FLOAT => [
            'sql' => 'DOUBLE',
            'dbal' => 'float',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => false,
            'elastic' => self::MAPPING_DOUBLE_FIELD,
        ],
        TypeMappingInterface::TYPE_BOOLEAN => [
            'sql' => 'INT(1)',
            'dbal' => 'boolean',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => false,
            'elastic' => self::MAPPING_BOOLEAN_FIELD,
        ],
        TypeMappingInterface::TYPE_DATE => [
            'sql' => 'DATE',
            'dbal' => 'date',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => true,
            'elastic' => self::MAPPING_DATE_FIELD,
        ],
        TypeMappingInterface::TYPE_DATETIME => [
            'sql' => 'DATETIME',
            'dbal' => 'datetime',
            'allowDefaultValue' => true,
            'quoteDefaultValue' => true,
            'elastic' => self::MAPPING_DATE_TIME_FIELD,
        ],
        TypeMappingInterface::TYPE_COMBOBOX => [
            'sql' => 'MEDIUMTEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => self::MAPPING_STRING_FIELD,
        ],
        TypeMappingInterface::TYPE_SINGLE_SELECTION => [
            'sql' => 'TEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => self::MAPPING_STRING_FIELD,
        ],
        TypeMappingInterface::TYPE_MULTI_SELECTION => [
            'sql' => 'MEDIUMTEXT',
            'dbal' => 'text',
            'allowDefaultValue' => false,
            'quoteDefaultValue' => false,
            'elastic' => self::MAPPING_STRING_FIELD,
        ],
    ];

    /**
     * @var array<string, string>
     */
    private array $dbalTypes = [
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

    private Shopware_Components_Snippet_Manager $snippets;

    public function __construct(Shopware_Components_Snippet_Manager $snippets)
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
            Article::class,
            Detail::class,
            Media::class,
            ProductStream::class,
            Option::class,
            Value::class,
            Category::class,
            Supplier::class,
            Blog::class,
            Form::class,
            Customer::class,
            CustomerStream::class,
            Dispatch::class,
            Payment::class,
            Mail::class,
            Emotion::class,
            Premium::class,
            Voucher::class,
            ProductFeed::class,
            Newsletter::class,
            Partner::class,
            Shop::class,
            Site::class,
            Country::class,
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

        return $this->dbalTypes[$name] ?? TypeMappingInterface::TYPE_STRING;
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

        return TypeMappingInterface::MAPPING_STRING_FIELD;
    }
}
