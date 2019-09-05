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

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\ESIndexingBundle\FieldMappingInterface;
use Shopware\Bundle\ESIndexingBundle\IdentifierSelector;
use Shopware\Bundle\ESIndexingBundle\MappingInterface;
use Shopware\Bundle\ESIndexingBundle\TextMappingInterface;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class ProductMapping implements MappingInterface
{
    const TYPE = 'product';

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
     * @var CrudService
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
        CrudService $crudService,
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
            '_source' => [
                'includes' => ['id', 'mainVariantId', 'variantId', 'number'],
            ],
            'properties' => [
                // Identifiers
                'id' => ['type' => 'long'],
                'mainVariantId' => ['type' => 'long'],
                'variantId' => ['type' => 'long'],

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
                'minStock' => ['type' => 'long'],
                'stock' => ['type' => 'long'],
                'sales' => ['type' => 'long'],
                'states' => $this->textMapping->getKeywordField(),
                'template' => $this->textMapping->getKeywordField(),
                'shippingTime' => $this->textMapping->getKeywordField(),
                'weight' => ['type' => 'double'],
                'height' => ['type' => 'long'],
                'length' => ['type' => 'long'],
                'width' => ['type' => 'double'],

                // Grouped id fields
                'blockedCustomerGroupIds' => ['type' => 'long'],
                'categoryIds' => ['type' => 'long'],

                // Flags
                'isMainVariant' => ['type' => 'boolean'],
                'closeouts' => ['type' => 'boolean'],
                'allowsNotification' => ['type' => 'boolean'],
                'hasProperties' => ['type' => 'boolean'],
                'hasAvailableVariant' => ['type' => 'boolean'],
                'hasConfigurator' => ['type' => 'boolean'],
                'hasEsd' => ['type' => 'boolean'],
                'isPriceGroupActive' => ['type' => 'boolean'],
                'shippingFree' => ['type' => 'boolean'],
                'highlight' => ['type' => 'boolean'],
                'customerPriceCount' => ['type' => 'long'],
                'fallbackPriceCount' => ['type' => 'long'],

                // Dates
                'formattedCreatedAt' => ['type' => 'date', 'format' => 'yyyy-MM-dd'],
                'formattedUpdatedAt' => ['type' => 'date', 'format' => 'yyyy-MM-dd'],
                'formattedReleaseDate' => ['type' => 'date', 'format' => 'yyyy-MM-dd'],

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
            $productMapping['properties']['listingVariationPrices'] = $this->getVariantPricesMapping($variantFacet, $shop);
            $productMapping['properties']['availability'] = $this->getVariantConfigurationMapping($variantFacet, 'boolean');
            $productMapping['properties']['visibility'] = $this->getVariantConfigurationMapping($variantFacet, 'boolean');
        }

        if ($this->isDebug) {
            unset($productMapping['_source']);
        }

        return $productMapping;
    }

    private function getVariantPricesMapping(VariantFacet $facet, Shop $shop): array
    {
        $customerGroups = $this->identifierSelector->getCustomerGroupKeys();

        if (!$shop->isMain()) {
            $currencies = $this->identifierSelector->getShopCurrencyIds($shop->getParentId());
        } else {
            $currencies = $this->identifierSelector->getShopCurrencyIds($shop->getId());
        }

        $result = [];

        foreach ($customerGroups as $customerGroup) {
            foreach ($currencies as $currency) {
                $result['properties'][$customerGroup . '_' . $currency] = $this->getVariantConfigurationMapping($facet, 'double');
            }
        }

        return $result;
    }

    private function getVariantConfigurationMapping(VariantFacet $facet, string $type): array
    {
        $properties = [];

        foreach (ProductListingVariationLoader::arrayCombinations($facet->getGroupIds()) as $combination) {
            sort($combination, SORT_NUMERIC);

            $properties['g' . implode('-', $combination)] = ['type' => $type];
        }

        return [
            'properties' => $properties,
        ];
    }

    private function getPropertyMapping(Shop $shop): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'long'],
                'name' => $this->fieldMapping->getLanguageField($shop),
                'position' => ['type' => 'long'],
            ],
        ];
    }

    private function getUnitMapping(): array
    {
        return [
            'properties' => [
                'id' => ['type' => 'long'],
                'name' => $this->textMapping->getKeywordField(),
                'unit' => $this->textMapping->getKeywordField(),
                'minPurchase' => ['type' => 'long'],
                'maxPurchase' => ['type' => 'long'],
                'packUnit' => $this->textMapping->getKeywordField(),
                'purchaseStep' => ['type' => 'long'],
                'purchaseUnit' => ['type' => 'long'],
                'referenceUnit' => ['type' => 'long'],
            ],
        ];
    }

    private function getManufacturerMapping(Shop $shop): array
    {
        return [
            'properties' => [
                'id' => ['type' => 'long'],
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

    private function getPriceGroupMapping(): array
    {
        return [
            'properties' => [
                'id' => ['type' => 'long'],
                'name' => $this->textMapping->getKeywordField(),
            ],
        ];
    }

    private function getEsdMapping(): array
    {
        return [
            'properties' => [
                'id' => ['type' => 'long'],
                'file' => $this->textMapping->getKeywordField(),
                'hasSerials' => ['type' => 'boolean'],
                'createdAt' => [
                    'properties' => [
                        'date' => $this->textMapping->getKeywordField(),
                        'timezone' => $this->textMapping->getKeywordField(),
                        'timezone_type' => ['type' => 'long'],
                    ],
                ],
            ],
        ];
    }

    private function getTaxMapping(): array
    {
        return [
            'properties' => [
                'id' => ['type' => 'long'],
                'name' => $this->textMapping->getKeywordField(),
                'tax' => ['type' => 'long'],
            ],
        ];
    }

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

    private function getPriceMapping(): array
    {
        return [
            'properties' => [
                'calculatedPrice' => ['type' => 'double'],
                'calculatedReferencePrice' => ['type' => 'double'],
                'calculatedPseudoPrice' => ['type' => 'double'],
            ],
        ];
    }

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

    private function getVariantOptionsMapping(Shop $shop): array
    {
        return [
            'properties' => [
                'id' => ['type' => 'long'],
                'name' => $this->fieldMapping->getLanguageField($shop),
                'description' => $this->fieldMapping->getLanguageField($shop),
                'options' => [
                    'properties' => [
                        'id' => ['type' => 'long'],
                        'name' => $this->fieldMapping->getLanguageField($shop),
                        'description' => $this->fieldMapping->getLanguageField($shop),
                    ],
                ],
            ],
        ];
    }

    private function getManualMapping(): array
    {
        return [
            'type' => 'nested',
            'properties' => [
                'category_id' => ['type' => 'long'],
                'position' => ['type' => 'long'],
            ],
        ];
    }

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
