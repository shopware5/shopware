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

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
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

    /**
     * @var array
     */
    private $translationMapping = [
        'metaTitle' => '__product_metaTitle',
        'txtArtikel' => '__product_name',
        'txtshortdescription' => '__product_description',
        'txtlangbeschreibung' => '__product_description_long',
        'txtzusatztxt' => '__variant_additionaltext',
        'txtkeywords' => '__product_keywords',
        'txtpackunit' => '__unit_packunit',
    ];

    /**
     * @param AttributeHydrator $attributeHydrator
     * @param ManufacturerHydrator $manufacturerHydrator
     * @param TaxHydrator $taxHydrator
     * @param UnitHydrator $unitHydrator
     * @param EsdHydrator $esdHydrator
     */
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
     * @param array $data
     * @return Struct\ListProduct
     */
    public function hydrateProduct(array $data)
    {
        $product = new Struct\Product(
            (int) $data['__product_id'],
            (int) $data['__variant_id'],
            $data['__variant_ordernumber']
        );

        return $this->assignData($product, $data);
    }

    /**
     * Hydrates the passed data and converts the ORM
     * array values into a Struct\ListProduct class.
     *
     * @param array $data
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
     * @param Struct\ListProduct $product
     * @param array $data
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

    /**
     * @param Struct\ListProduct $product
     * @param array $data
     */
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
     *
     * @param Struct\ListProduct $product
     * @param $data
     */
    private function assignProductData(Struct\ListProduct $product, $data)
    {
        if (isset($data['__product_name'])) {
            $product->setName($data['__product_name']);
        }

        if (isset($data['__product_description'])) {
            $product->setShortDescription($data['__product_description']);
        }

        if (isset($data['__product_description_long'])) {
            $product->setLongDescription($data['__product_description_long']);
        }

        if (isset($data['__product_laststock'])) {
            $product->setCloseouts((bool) ($data['__product_laststock']));
        }

        if (isset($data['__product_metaTitle'])) {
            $product->setMetaTitle($data['__product_metaTitle']);
        }

        if (isset($data['__product_filtergroupID'])) {
            $product->setHasProperties($data['__product_filtergroupID'] > 0);
        }

        if (isset($data['__product_topseller'])) {
            $product->setHighlight((bool) ($data['__product_topseller']));
        }

        if (isset($data['__product_notification'])) {
            $product->setAllowsNotification((bool) ($data['__product_notification']));
        }

        if (isset($data['__product_keywords'])) {
            $product->setKeywords($data['__product_keywords']);
        }

        if (isset($data['__product_template'])) {
            $product->setTemplate($data['__product_template']);
        }

        if ($data['__product_datum']) {
            $product->setCreatedAt(
                new \DateTime($data['__product_datum'])
            );
        }

        if (isset($data['__product_configurator_set_id'])) {
            $product->setHasConfigurator(
                ($data['__product_configurator_set_id'] > 0)
            );
        }

        if (isset($data['__product_has_esd'])) {
            $product->setHasEsd(
                (bool) $data['__product_has_esd']
            );
        }

        if (isset($data['__product_pricegroupActive'])) {
            $product->setIsPriceGroupActive(
                (bool) $data['__product_pricegroupActive']
            );
        }

        if (isset($data['__product_blocked_customer_groups'])) {
            $product->setBlockedCustomerGroupIds(
                explode('|', $data['__product_blocked_customer_groups'])
            );
        }

        if (isset($data['__topSeller_sales'])) {
            $product->setSales((int) $data['__topSeller_sales']);
        }

        if (isset($data['__variant_shippingtime'])) {
            $product->setShippingTime($data['__variant_shippingtime']);
        } elseif (isset($data['__product_shippingtime'])) {
            $product->setShippingTime($data['__product_shippingtime']);
        }

        if (isset($data['__variant_shippingfree'])) {
            $product->setShippingFree((bool) ($data['__variant_shippingfree']));
        }

        if (isset($data['__variant_instock'])) {
            $product->setStock((int) $data['__variant_instock']);
        }

        if (isset($data['__variant_suppliernumber'])) {
            $product->setManufacturerNumber($data['__variant_suppliernumber']);
        }

        if (isset($data['__variant_releasedate'])) {
            $product->setReleaseDate(
                new \DateTime($data['__variant_releasedate'])
            );
        }

        if (isset($data['__variant_additionaltext'])) {
            $product->setAdditional($data['__variant_additionaltext']);
        }

        if (isset($data['__variant_ean'])) {
            $product->setEan($data['__variant_ean']);
        }

        if (isset($data['__variant_height'])) {
            $product->setHeight((float) $data['__variant_height']);
        }

        if (isset($data['__variant_length'])) {
            $product->setLength((float) $data['__variant_length']);
        }

        if (isset($data['__variant_stockmin'])) {
            $product->setMinStock((int) $data['__variant_stockmin']);
        }

        if (isset($data['__variant_weight'])) {
            $product->setWeight((float) $data['__variant_weight']);
        }

        if (isset($data['__variant_width'])) {
            $product->setWidth((float) $data['__variant_width']);
        }
    }

    /**
     * Iterates the attribute data and assigns the attribute struct to the product.
     *
     * @param Struct\ListProduct $product
     * @param $data
     */
    private function assignAttributeData(Struct\ListProduct $product, $data)
    {
        $translation = $this->getProductTranslation($data);

        $attribute = $this->attributeHydrator->hydrate(
            $this->extractFields('__productAttribute_', $data)
        );

        foreach ($translation as $key => $value) {
            if ($attribute->exists($key)) {
                $attribute->set($key, $value);
            }
        }

        $product->addAttribute('core', $attribute);
    }

    /**
     * @param $data
     * @return array
     */
    private function getProductTranslation($data)
    {
        $translation = [];
        if (isset($data['__product_translation_fallback'])) {
            $translation = array_merge(
                $translation,
                $this->unserializeTranslation($data['__product_translation_fallback'])
            );
            if ($data['__product_main_detail_id'] != $data['__variant_id']) {
                unset($translation['txtzusatztxt']);
            }
        }
        if (isset($data['__product_translation'])) {
            $translation = array_merge(
                $translation,
                $this->unserializeTranslation($data['__product_translation'])
            );
            if ($data['__product_main_detail_id'] != $data['__variant_id']) {
                unset($translation['txtzusatztxt']);
            }
        }

        if (isset($data['__variant_translation_fallback'])) {
            $translation = array_merge(
                $translation,
                $this->unserializeTranslation($data['__variant_translation_fallback'])
            );
        }
        if (isset($data['__variant_translation'])) {
            $translation = array_merge(
                $translation,
                $this->unserializeTranslation($data['__variant_translation'])
            );
        }

        foreach ($translation as $key => $value) {
            if (strpos($key, 'attr') !== false) {
                $new = '__productAttribute_' . $key;
                $translation[$new] = $value;
            }
        }

        if (empty($translation)) {
            return $translation;
        }

        return $this->convertArrayKeys(
            $translation,
            $this->translationMapping
        );
    }

    /**
     * Helper to unserialize translations
     * In case unserialize() returns false (invalid input string), returns an empty array
     *
     * @param $serializedTranslation
     * @return array
     */
    private function unserializeTranslation($serializedTranslation)
    {
        return unserialize($serializedTranslation) ? : [];
    }
}
