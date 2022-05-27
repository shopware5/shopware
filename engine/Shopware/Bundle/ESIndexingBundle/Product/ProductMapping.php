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

namespace Shopware\Bundle\ESIndexingBundle\Product;

use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Bundle\AttributeBundle\Service\TypeMappingInterface;
use Shopware\Bundle\ESIndexingBundle\FieldMappingInterface;
use Shopware\Bundle\ESIndexingBundle\IdentifierSelector;
use Shopware\Bundle\ESIndexingBundle\MappingInterface;
use Shopware\Bundle\ESIndexingBundle\TextMappingInterface;
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class ProductMapping implements MappingInterface
{
    public const TYPE = 'product';

    private const DYNAMIC_MAPPING = [
        'type' => 'object',
        'dynamic' => true,
    ];

    /**
     * @var bool
     */
    protected $isDebug;

    /**
     * @var VariantHelperInterface
     */
    protected $variantHelper;

    /**
     * @var IdentifierSelector
     */
    private $identifierSelector;

    /**
     * @var FieldMappingInterface
     */
    private $fieldMapping;

    /**
     * @var TextMappingInterface
     */
    private $textMapping;

    /**
     * @var CrudServiceInterface
     */
    private $crudService;

    /**
     * @var bool
     */
    private $isDynamic;

    public function __construct(
        IdentifierSelector $identifierSelector,
        FieldMappingInterface $fieldMapping,
        TextMappingInterface $textMapping,
        CrudServiceInterface $crudService,
        VariantHelperInterface $variantHelper,
        bool $isDynamic = true,
        bool $isDebug = false
    ) {
        $this->identifierSelector = $identifierSelector;
        $this->fieldMapping = $fieldMapping;
        $this->textMapping = $textMapping;
        $this->crudService = $crudService;
        $this->variantHelper = $variantHelper;
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
        $variantFacet = $this->variantHelper->getVariantFacet();

        $productMapping = [
            'dynamic' => $this->isDynamic,
            'dynamic_templates' => [
                [
                    'variants_float_prices_to_double' => [
                        'path_match' => 'listingVariationPrices.*',
                        'match_mapping_type' => 'double',
                        'mapping' => [
                            'type' => 'double',
                        ],
                    ],
                ],
                [
                    'variants_long_prices_to_double' => [
                        'path_match' => 'listingVariationPrices.*',
                        'match_mapping_type' => 'long',
                        'mapping' => [
                            'type' => 'double',
                        ],
                    ],
                ],
            ],
            '_source' => [
                'includes' => ['id', 'mainVariantId', 'variantId', 'number'],
            ],
            'properties' => [
                // Identifiers
                'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'mainVariantId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'variantId' => TypeMappingInterface::MAPPING_LONG_FIELD,

                // Number fields
                'number' => array_merge($this->textMapping->getTextField(), ['analyzer' => 'standard', 'fields' => ['raw' => $this->textMapping->getKeywordField()]]),
                'ean' => $this->textMapping->getKeywordField(),
                'manufacturerNumber' => $this->fieldMapping->getLanguageField($shop),

                // Language fields
                'name' => $this->fieldMapping->getLanguageField($shop),
                'shortDescription' => $this->fieldMapping->getLanguageField($shop),
                'longDescription' => $this->fieldMapping->getLanguageField($shop),
                'additional' => $this->fieldMapping->getLanguageField($shop),
                'keywords' => $this->fieldMapping->getLanguageField($shop),
                'metaTitle' => $this->fieldMapping->getLanguageField($shop),

                // Other fields
                'calculatedPrices' => $this->getCalculatedPricesMapping($shop),
                'minStock' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'stock' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'sales' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'states' => $this->textMapping->getKeywordField(),
                'template' => $this->textMapping->getKeywordField(),
                'shippingTime' => $this->textMapping->getKeywordField(),
                'weight' => TypeMappingInterface::MAPPING_DOUBLE_FIELD,
                'height' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'length' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'width' => TypeMappingInterface::MAPPING_DOUBLE_FIELD,

                // Grouped id fields
                'blockedCustomerGroupIds' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'categoryIds' => TypeMappingInterface::MAPPING_LONG_FIELD,

                // Flags
                'isMainVariant' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'closeouts' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'allowsNotification' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'hasProperties' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'hasAvailableVariant' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'hasConfigurator' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'hasEsd' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'isPriceGroupActive' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'shippingFree' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'highlight' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'hasStock' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'customerPriceCount' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'fallbackPriceCount' => TypeMappingInterface::MAPPING_LONG_FIELD,

                // Dates
                'formattedCreatedAt' => TypeMappingInterface::MAPPING_DATE_FIELD,
                'formattedUpdatedAt' => TypeMappingInterface::MAPPING_DATE_FIELD,
                'formattedReleaseDate' => TypeMappingInterface::MAPPING_DATE_FIELD,

                // Nested structs
                'manufacturer' => $this->getManufacturerMapping($shop),
                'priceGroup' => $this->getPriceGroupMapping(),
                'properties' => $this->getPropertyMapping($shop),
                'esd' => $this->getEsdMapping(),
                'tax' => $this->getTaxMapping(),
                'unit' => $this->getUnitMapping(),

                'attributes' => $this->getAttributeMapping(),
                'configuration' => $this->getVariantOptionsMapping($shop),

                'voteAverage' => $this->getVoteAverageMapping(),
                'manualSorting' => $this->getManualMapping(),
            ],
        ];

        if ($variantFacet) {
            $productMapping['properties']['filterConfiguration'] = $this->getVariantOptionsMapping($shop);
            $productMapping['properties']['listingVariationPrices'] = self::DYNAMIC_MAPPING;
            $productMapping['properties']['availability'] = self::DYNAMIC_MAPPING;
            $productMapping['properties']['visibility'] = self::DYNAMIC_MAPPING;
        }

        if ($this->isDebug) {
            unset($productMapping['_source']);
        }

        return $productMapping;
    }

    /**
     * @return array<string, mixed>
     */
    private function getPropertyMapping(Shop $shop): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'name' => $this->fieldMapping->getLanguageField($shop),
                'position' => TypeMappingInterface::MAPPING_LONG_FIELD,
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getUnitMapping(): array
    {
        return [
            'properties' => [
                'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'name' => $this->textMapping->getKeywordField(),
                'unit' => $this->textMapping->getKeywordField(),
                'minPurchase' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'maxPurchase' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'packUnit' => $this->textMapping->getKeywordField(),
                'purchaseStep' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'purchaseUnit' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'referenceUnit' => TypeMappingInterface::MAPPING_LONG_FIELD,
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getManufacturerMapping(Shop $shop): array
    {
        return [
            'properties' => [
                'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'name' => $this->fieldMapping->getLanguageField($shop),
                'description' => $this->textMapping->getKeywordField(),
                'coverFile' => $this->textMapping->getKeywordField(),
                'link' => $this->textMapping->getKeywordField(),
                'metaTitle' => $this->textMapping->getKeywordField(),
                'metaDescription' => $this->textMapping->getKeywordField(),
                'metaKeywords' => $this->textMapping->getKeywordField(),
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getPriceGroupMapping(): array
    {
        return [
            'properties' => [
                'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'name' => $this->textMapping->getKeywordField(),
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getEsdMapping(): array
    {
        return [
            'properties' => [
                'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'file' => $this->textMapping->getKeywordField(),
                'hasSerials' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'createdAt' => [
                    'properties' => [
                        'date' => $this->textMapping->getKeywordField(),
                        'timezone' => $this->textMapping->getKeywordField(),
                        'timezone_type' => TypeMappingInterface::MAPPING_LONG_FIELD,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getTaxMapping(): array
    {
        return [
            'properties' => [
                'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'name' => $this->textMapping->getKeywordField(),
                'tax' => TypeMappingInterface::MAPPING_LONG_FIELD,
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getCalculatedPricesMapping(Shop $shop): array
    {
        $prices = [];
        $customerGroups = $this->identifierSelector->getCustomerGroupKeys();
        $currencies = $this->identifierSelector->getShopCurrencyIds($shop->getId());
        if (!$shop->isMain()) {
            $currencies = $this->identifierSelector->getShopCurrencyIds($shop->getParentId());
        }

        foreach ($currencies as $currency) {
            foreach ($customerGroups as $customerGroup) {
                $key = $customerGroup . '_' . $currency;
                $prices[$key] = $this->getPriceMapping();
            }
        }

        return ['properties' => $prices];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getPriceMapping(): array
    {
        return [
            'properties' => [
                'calculatedPrice' => TypeMappingInterface::MAPPING_DOUBLE_FIELD,
                'calculatedReferencePrice' => TypeMappingInterface::MAPPING_DOUBLE_FIELD,
                'calculatedPseudoPrice' => TypeMappingInterface::MAPPING_DOUBLE_FIELD,
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getAttributeMapping(): array
    {
        $attributes = $this->crudService->getList('s_articles_attributes');

        $properties = [];
        foreach ($attributes as $attribute) {
            $name = $attribute->getColumnName();
            $type = $attribute->getElasticSearchType();

            if ($attribute->isIdentifier()) {
                continue;
            }

            switch ($type['type']) {
                case 'keyword':
                    $type = $this->textMapping->getKeywordField();
                    $type['fields']['raw'] = $this->textMapping->getKeywordField();
                    break;

                case 'string':
                case 'text':
                    $type = $this->textMapping->getTextField();
                    $type['fields']['raw'] = $this->textMapping->getKeywordField();
                    break;
            }

            $properties[$name] = $type;
        }

        return [
            'properties' => [
                'core' => [
                    'properties' => $properties,
                ],
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getVariantOptionsMapping(Shop $shop): array
    {
        return [
            'properties' => [
                'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'name' => $this->fieldMapping->getLanguageField($shop),
                'description' => $this->fieldMapping->getLanguageField($shop),
                'options' => [
                    'properties' => [
                        'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                        'name' => $this->fieldMapping->getLanguageField($shop),
                        'description' => $this->fieldMapping->getLanguageField($shop),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getManualMapping(): array
    {
        return [
            'type' => 'nested',
            'properties' => [
                'category_id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'position' => TypeMappingInterface::MAPPING_LONG_FIELD,
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getVoteAverageMapping(): array
    {
        return [
            'properties' => [
                'average' => [
                    'type' => 'double',
                ],
            ],
        ];
    }
}
