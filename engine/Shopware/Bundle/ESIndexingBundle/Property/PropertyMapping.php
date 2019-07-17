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

namespace Shopware\Bundle\ESIndexingBundle\Property;

use Shopware\Bundle\ESIndexingBundle\FieldMappingInterface;
use Shopware\Bundle\ESIndexingBundle\MappingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class PropertyMapping implements MappingInterface
{
    const TYPE = 'property';

    /**
     * @var bool
     */
    protected $isDebug;

    /**
     * @var FieldMappingInterface
     */
    private $fieldMapping;

    /**
     * @var bool
     */
    private $isDynamic;

    public function __construct(
        FieldMappingInterface $fieldMapping,
        bool $isDynamic = true,
        bool $isDebug = false
    ) {
        $this->fieldMapping = $fieldMapping;
        $this->isDynamic = $isDynamic;
        $this->isDebug = $isDebug;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Shop $shop)
    {
        $mapping = [
            'dynamic' => $this->isDynamic,
            'properties' => [
                'id' => ['type' => 'long'],
                'name' => $this->fieldMapping->getLanguageField($shop),
                'filterable' => ['type' => 'boolean'],
                'options' => [
                    'properties' => [
                        'id' => ['type' => 'long'],
                        'name' => $this->fieldMapping->getLanguageField($shop),
                        'position' => ['type' => 'long'],
                    ],
                ],
            ],
        ];

        if ($this->isDebug) {
            unset($mapping['_source']);
        }

        return $mapping;
    }
}
