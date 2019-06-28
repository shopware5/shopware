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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\StoreFrontBundle\Struct;

class ProductHydrator extends Hydrator
{
    /**
     * @var ManufacturerHydrator
     */
    private $manufacturerHydrator;

    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var TaxHydrator
     */
    private $taxHydrator;

    /**
     * @var UnitHydrator
     */
    private $unitHydrator;

    /**
     * @var EsdHydrator
     */
    private $esdHydrator;

    public function __construct(
        AttributeHydrator $attributeHydrator,
        ManufacturerHydrator $manufacturerHydrator,
        TaxHydrator $taxHydrator,
        UnitHydrator $unitHydrator,
        EsdHydrator $esdHydrator
    ) {
        $this->attributeHydrator = $attributeHydrator;
        $this->manufacturerHydrator = $manufacturerHydrator;
        $this->taxHydrator = $taxHydrator;
        $this->unitHydrator = $unitHydrator;
        $this->esdHydrator = $esdHydrator;
    }

    /**
     * Hydrates the passed data and converts the ORM
     * array values into a Struct\ListProduct class.
     *
     * @return Struct\ListProduct
     */
    public function hydrateListProduct(array $data)
    {
        $product = new Struct\ListProduct(
            (int) $data['__product_id'],
            (int) $data['__variant_id'],
            $data['__variant_ordernumber']
        );

        return $this->assignData($product, $data);
    }

    /**
     * @return array
     */
    public function getProductTranslation(array $data)
    {
        $translation = $this->getTranslation($data, '__product', [], null, false);
        $variant = $this->getTranslation($data, '__variant', [], null, false);
        $translation = array_merge($translation, $variant);

        if (empty($translation)) {
            return $translation;
        }

        $result = $this->convertArrayKeys($translation, [
            'metaTitle' => '__product_metaTitle',
            'txtArtikel' => '__product_name',
            'txtshortdescription' => '__product_description',
            'txtlangbeschreibung' => '__product_description_long',
            'txtshippingtime' => '__variant_shippingtime',
            'txtzusatztxt' => '__variant_additionaltext',
            'txtkeywords' => '__product_keywords',
            'txtpackunit' => '__unit_packunit',
        ]);

        return $result;
    }

    /**
     * @return Struct\ListProduct
     */
    private function assignData(Struct\ListProduct $product, array $data)
    {
        $translation = $this->getProductTranslation($data);
        $data = array_merge($data, $translation);

        $this->assignProductData($product, $data);

        $product->setTax(
            $this->taxHydrator->hydrate($data)
        );

        $this->assignPriceGroupData($product, $data);

        if ($data['__product_supplierID']) {
            $product->setManufacturer(
                $this->manufacturerHydrator->hydrate($data)
            );
        }

        if ($data['__esd_id']) {
            $product->setEsd(
                $this->esdHydrator->hydrate($data)
            );
        }

        $product->setUnit(
            $this->unitHydrator->hydrate($data)
        );

        if (!empty($data['__productAttribute_id'])) {
            $this->assignAttributeData($product, $data);
        }

        return $product;
    }

    private function assignPriceGroupData(Struct\ListProduct $product, array $data)
    {
        if (!empty($data['__priceGroup_id'])) {
            $product->setPriceGroup(new Struct\Product\PriceGroup());
            $product->getPriceGroup()->setId((int) $data['__priceGroup_id']);
            $product->getPriceGroup()->setName($data['__priceGroup_description']);
        }
    }

    /**
     * Helper function which assigns the shopware article
     * data to the product. (data of s_articles)
     */
    private function assignProductData(Struct\ListProduct $product, array $data)
    {
        $product->setName($data['__product_name']);
        $product->setShortDescription($data['__product_description']);
        $product->setLongDescription($data['__product_description_long']);
        $product->setCloseouts((bool) ($data['__product_laststock']));
        $product->setMetaTitle($data['__product_metaTitle']);
        $product->setHasProperties($data['__product_filtergroupID'] > 0);
        $product->setHighlight((bool) ($data['__product_topseller']));
        $product->setAllowsNotification((bool) ($data['__product_notification']));
        $product->setKeywords($data['__product_keywords']);
        $product->setTemplate($data['__product_template']);
        $product->setHasConfigurator(($data['__product_configurator_set_id'] > 0));
        $product->setHasEsd((bool) $data['__product_has_esd']);
        $product->setIsPriceGroupActive((bool) $data['__product_pricegroupActive']);
        $product->setSales((int) $data['__topSeller_sales']);
        $product->setShippingFree((bool) ($data['__variant_shippingfree']));
        $product->setStock((int) $data['__variant_instock']);
        $product->setManufacturerNumber($data['__variant_suppliernumber']);
        $product->setMainVariantId((int) $data['__product_main_detail_id']);

        if ($data['__variant_shippingtime']) {
            $product->setShippingTime($data['__variant_shippingtime']);
        } elseif ($data['__product_shippingtime']) {
            $product->setShippingTime($data['__product_shippingtime']);
        }

        if ($data['__variant_releasedate']) {
            $product->setReleaseDate(
                new \DateTime($data['__variant_releasedate'])
            );
        }
        if ($data['__product_datum']) {
            $product->setCreatedAt(
                new \DateTime($data['__product_datum'])
            );
        }
        if ($data['__product_changetime']) {
            $product->setUpdatedAt(
                new \DateTime($data['__product_changetime'])
            );
        }

        $product->setAdditional($data['__variant_additionaltext']);
        $product->setEan($data['__variant_ean']);
        $product->setHeight((float) $data['__variant_height']);
        $product->setLength((float) $data['__variant_length']);
        $product->setMinStock((int) $data['__variant_stockmin']);
        $product->setWeight((float) $data['__variant_weight']);
        $product->setWidth((float) $data['__variant_width']);

        $customerGroups = explode('|', $data['__product_blocked_customer_groups']);
        $customerGroups = array_filter($customerGroups);
        $product->setBlockedCustomerGroupIds($customerGroups);
        $product->setHasAvailableVariant($data['__product_has_available_variants'] > 0);

        $product->setFallbackPriceCount($data['__product_fallback_price_count']);
        if (array_key_exists('__product_custom_price_count', $data)) {
            $product->setCustomerPriceCount($data['__product_custom_price_count']);
        } else {
            $product->setCustomerPriceCount($data['__product_fallback_price_count']);
        }
    }

    /**
     * Iterates the attribute data and assigns the attribute struct to the product.
     */
    private function assignAttributeData(Struct\ListProduct $product, array $data)
    {
        $translation = $this->getProductTranslation($data);
        $translation = $this->extractFields('__attribute_', $translation);
        $attributeData = $this->extractFields('__productAttribute_', $data);
        $attributeData = array_merge($attributeData, $translation);
        $attribute = $this->attributeHydrator->hydrate($attributeData);
        $product->addAttribute('core', $attribute);
    }
}
