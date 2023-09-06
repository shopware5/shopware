<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\ESIndexingBundle\Property;

use Shopware\Bundle\AttributeBundle\Service\TypeMappingInterface;
use Shopware\Bundle\ESIndexingBundle\FieldMappingInterface;
use Shopware\Bundle\ESIndexingBundle\MappingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class PropertyMapping implements MappingInterface
{
    public const TYPE = 'property';

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
        bool $isDynamic = true
    ) {
        $this->fieldMapping = $fieldMapping;
        $this->isDynamic = $isDynamic;
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
        return [
            'dynamic' => $this->isDynamic,
            'properties' => [
                'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'name' => $this->fieldMapping->getLanguageField($shop),
                'filterable' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'options' => [
                    'properties' => [
                        'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                        'name' => $this->fieldMapping->getLanguageField($shop),
                        'position' => TypeMappingInterface::MAPPING_LONG_FIELD,
                    ],
                ],
            ],
        ];
    }
}
