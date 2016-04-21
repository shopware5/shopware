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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\AttributeBundle\Service
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class TypeMapping
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $types = [
        'string'   => ['sql' => 'VARCHAR(500)', 'extJs' => 'string', 'dbal' => 'string'],
        'text'     => ['sql' => 'TEXT', 'extJs' => 'string', 'dbal' => 'text'],
        'html'     => ['sql' => 'MEDIUMTEXT', 'extJs' => 'string', 'dbal' => 'text'],
        'integer'  => ['sql' => 'INT(11)', 'extJs' => 'integer', 'dbal' => 'integer'],
        'float'    => ['sql' => 'DOUBLE', 'extJs' => 'float', 'dbal' => 'float'],
        'boolean'  => ['sql' => 'INT(1)', 'extJs' => 'boolean', 'dbal' => 'boolean'],
        'date'     => ['sql' => 'DATE', 'extJs' => 'date', 'dbal' => 'date'],
        'datetime' => ['sql' => 'DATETIME', 'extJs' => 'date', 'dbal' => 'datetime'],
        'single_selection' => ['sql' => 'VARCHAR(500)'],
        'multi_selection' => ['sql' => 'MEDIUMTEXT']
    ];

    /**
     * @var array
     */
    private $dbalTypes = [
        'array' => 'text',
        'simple_array' => 'text',
        'json_array' => 'text',
        'bigint' => 'integer',
        'boolean' => 'boolean',
        'datetime' => 'datetime',
        'datetimetz' => 'date',
        'date' => 'date',
        'time' => 'string',
        'decimal' => 'float',
        'integer' => 'integer',
        'object' => 'text',
        'smallint' => 'integer',
        'string' => 'string',
        'text' => 'text',
        'binary' => 'text',
        'blob' => 'text',
        'float' => 'float',
        'guid' => 'text'
    ];
    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippets;

    /**
     * TypeMapping constructor.
     * @param Connection $connection
     * @param \Shopware_Components_Snippet_Manager $snippets
     */
    public function __construct(Connection $connection, \Shopware_Components_Snippet_Manager $snippets)
    {
        $this->connection = $connection;
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
     * @return string[]
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
            'Shopware\Models\Dispatch\Dispatch',
            'Shopware\Models\Payment\Payment',
            'Shopware\Models\Mail\Mail',
            'Shopware\Models\Emotion\Emotion',
            'Shopware\Models\Premium\Premium',
            'Shopware\Models\Voucher\Voucher',
            'Shopware\Models\ProductFeed\ProductFeed',
            'Shopware\Models\Newsletter\Newsletter',
            'Shopware\Models\Partner\Partner',
            'Shopware\Models\Shop\Shop'
        ];

        $result = [];
        foreach ($entities as $entity) {
            $result[] = ['entity' => $entity, 'label' => $snippets->get($entity)];
        }
        return $result;
    }

    /**
     * @param Type $type
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
     * @return string
     */
    public function unifiedToExtJS($type)
    {
        $type = strtolower($type);
        if (!isset($this->types[$type])) {
            return 'string';
        }
        $mapping = $this->types[$type];
        return $mapping['extJs'];
    }

    /**
     * @param string $type
     * @return string
     */
    public function unifiedToSQL($type)
    {
        $type = strtolower($type);
        if (!isset($this->types[$type])) {
            return 'string';
        }
        $mapping = $this->types[$type];
        return $mapping['sql'];
    }
}
