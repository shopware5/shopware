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
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

/**
 * Class ProductMapping
 */
class ProductMapping implements MappingInterface
{
    const TYPE = 'product';

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
     * @param IdentifierSelector    $identifierSelector
     * @param FieldMappingInterface $fieldMapping
     * @param TextMappingInterface  $textMapping
     * @param CrudService           $crudService
     */
    public function __construct(
        IdentifierSelector $identifierSelector,
        FieldMappingInterface $fieldMapping,
        TextMappingInterface $textMapping,
        CrudService $crudService
    ) {
        $this->identifierSelector = $identifierSelector;
        $this->fieldMapping = $fieldMapping;
        $this->textMapping = $textMapping;
        $this->crudService = $crudService;
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
            '_source' => [
                'includes' => ['id', 'mainVariantId', 'variantId', 'number'],
            ],
            'properties' => [
                //identifiers
                'id' => ['type' => 'long'],
                'mainVariantId' => ['type' => 'long'],
                'variantId' => ['type' => 'long'],

                //number fields
                'number' => $this->textMapping->getNotAnalyzedField(),
                'ean' => $this->textMapping->getNotAnalyzedField(),
                'manufacturerNumber' => $this->textMapping->getNotAnalyzedField(),

                //language fields
                'name' => $this->fieldMapping->getLanguageField($shop),
                'shortDescription' => $this->fieldMapping->getLanguageField($shop),
                'longDescription' => $this->fieldMapping->getLanguageField($shop),
                'additional' => $this->fieldMapping->getLanguageField($shop),
                'keywords' => $this->fieldMapping->getLanguageField($shop),
                'metaTitle' => $this->fieldMapping->getLanguageField($shop),

                //other fields
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

                //grouped id fields
                'blockedCustomerGroupIds' => ['type' => 'long'],
                'categoryIds' => ['type' => 'long'],

                //flags
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

                //dates
                'formattedCreatedAt' => ['type' => 'date', 'format' => 'yyyy-MM-dd'],
                'formattedReleaseDate' => ['type' => 'date', 'format' => 'yyyy-MM-dd'],

                //nested structs
                'manufacturer' => $this->getManufacturerMapping($shop),
                'priceGroup' => $this->getPriceGroupMapping(),
                'properties' => $this->getPropertyMapping($shop),
                'esd' => $this->getEsdMapping(),
                'tax' => $this->getTaxMapping(),
                'unit' => $this->getUnitMapping(),

                'attributes' => $this->getAttributeMapping(),
            ],
        ];
    }

    /**
     * @param Shop $shop
     *
     * @return array
     */
    private function getPropertyMapping(Shop $shop)
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

    /**
     * @return array
     */
    private function getUnitMapping()
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

    /**
     * @param Shop $shop
     *
     * @return array
     */
    private function getManufacturerMapping(Shop $shop)
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

    /**
     * @return array
     */
    private function getPriceGroupMapping()
    {
        return [
            'properties' => [
                'id' => ['type' => 'long'],
                'name' => $this->textMapping->getKeywordField(),
            ],
        ];
    }

    /**
     * @return array
     */
    private function getEsdMapping()
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

    /**
     * @return array
     */
    private function getTaxMapping()
    {
        return [
            'properties' => [
                'id' => ['type' => 'long'],
                'name' => $this->textMapping->getKeywordField(),
                'tax' => ['type' => 'long'],
            ],
        ];
    }

    /**
     * @param Shop $shop
     *
     * @return array
     */
    private function getCalculatedPricesMapping(Shop $shop)
    {
        $prices = [];
        $customerGroups = $this->identifierSelector->getCustomerGroupKeys();
        $currencies = $this->identifierSelector->getShopCurrencyIds($shop->getId());

        foreach ($currencies as $currency) {
            foreach ($customerGroups as $customerGroup) {
                $key = $customerGroup . '_' . $currency;
                $prices[$key] = $this->getPriceMapping();
            }
        }

        return ['properties' => $prices];
    }

    /**
     * @return array
     */
    private function getPriceMapping()
    {
        return [
            'properties' => [
                'calculatedPrice' => ['type' => 'double'],
                'calculatedReferencePrice' => ['type' => 'double'],
                'calculatedPseudoPrice' => ['type' => 'double'],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getAttributeMapping()
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
                    $type['fields']['raw'] = $this->textMapping->getNotAnalyzedField();
                    break;

                case 'string':
                case 'text':
                    $type = $this->textMapping->getTextField();
                    $type['fields']['raw'] = $this->textMapping->getNotAnalyzedField();
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
}
