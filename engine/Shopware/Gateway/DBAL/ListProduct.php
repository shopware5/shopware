<?php

namespace Shopware\Gateway\DBAL;


use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;
use Shopware\Struct;

/**
 * Class Product gateway.
 *
 * @package Shopware\Gateway\DBAL
 */
class ListProduct extends Gateway
{
    /**
     * @var \Shopware\Gateway\DBAL\Hydrator\Product
     */
    protected $hydrator;

    /**
     * @param $hydrator
     * @param ModelManager $entityManager
     */
    function __construct(ModelManager $entityManager, Hydrator\Product $hydrator)
    {
        $this->hydrator = $hydrator;
        $this->entityManager = $entityManager;
    }


    /**
     * Returns a single of ListProduct struct which can be used for listings
     * or sliders.
     *
     * A mini product contains only the minified product data.
     * The mini data contains data sources:
     *  - article
     *  - variant
     *  - unit
     *  - attribute
     *  - tax
     *  - manufacturer
     *  - price group
     *
     * @param $number
     * @param \Shopware\Struct\Context $context
     * @return Struct\ListProduct
     */
    public function get($number, Struct\Context $context)
    {
        $products = $this->getList(array($number), $context);

        return array_shift($products);
    }

    /**
     * Returns a list of ListProduct structs which can be used for listings
     * or sliders.
     *
     * A mini product contains only the minified product data.
     * The mini data contains data sources:
     *  - article
     *  - variant
     *  - unit
     *  - attribute
     *  - tax
     *  - manufacturer
     *  - price group
     *
     * @param array $numbers
     * @param \Shopware\Struct\Context $context
     * @return Struct\ListProduct[]
     */
    public function getList(array $numbers, Struct\Context $context)
    {
        $query = $this->getQuery($numbers, $context);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $products = array();
        foreach ($data as $product) {
            $key = $product['ordernumber'];
            $products[$key] = $this->hydrator->hydrateListProduct($product);
        }

        return $products;
    }

    protected function getQuery(array $numbers, Struct\Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->getArticleFields())
            ->addSelect($this->getVariantFields())
            ->addSelect($this->getUnitFields())
            ->addSelect($this->getTaxFields())
            ->addSelect($this->getPriceGroupFields())
            ->addSelect($this->getManufacturerFields())
            ->addSelect($this->getTableFields('s_articles_attributes', 'attribute'))
            ->addSelect($this->getTableFields('s_articles_supplier_attributes', 'manufacturerAttribute'));

        $query->from('s_articles_details', 'variant')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->innerJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID')
            ->leftJoin('variant', 's_articles_attributes', 'attribute', 'attribute.articledetailsID = variant.id')
            ->leftJoin('variant', 's_core_units', 'unit', 'unit.id = variant.unitID')
            ->leftJoin('product', 's_articles_supplier', 'manufacturer', 'manufacturer.id = product.supplierID')
            ->leftJoin('product', 's_articles_supplier_attributes', 'manufacturerAttribute', 'manufacturerAttribute.id = product.supplierID')
            ->leftJoin('product', 's_core_pricegroups', 'priceGroup', 'priceGroup.id = product.pricegroupID')
            ->where('variant.ordernumber IN (:numbers)')
            ->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        $query->leftJoin(
            'variant',
            's_core_translations',
            'productTranslation',
            'productTranslation.objecttype = :productType AND
             productTranslation.objectkey = variant.articleID AND
             productTranslation.objectlanguage = :language'
        );

        $query->leftJoin(
            'variant',
            's_core_translations',
            'variantTranslation',
            'variantTranslation.objecttype = :variantType AND
             variantTranslation.objectkey = variant.id AND
             variantTranslation.objectlanguage = :language'
        );

        $query->leftJoin(
            'manufacturer',
            's_core_translations',
            'manufacturerTranslation',
            'manufacturerTranslation.objecttype = :manufacturerType AND
             manufacturerTranslation.objectkey = manufacturer.id AND
             manufacturerTranslation.objectlanguage = :language'
        );

        $query->leftJoin(
            'variant',
            's_core_translations',
            'unitTranslation',
            'unitTranslation.objecttype = :unitType AND
             unitTranslation.objectkey = variant.unitID AND
             unitTranslation.objectlanguage = :language'
        );

        $query->setParameter(':productType', 'article')
            ->setParameter(':variantType', 'variant')
            ->setParameter(':manufacturerType', 'supplier')
            ->setParameter(':unitType', 'config_units')
//            ->setParameter(':language', $context->getShop()->getId())
            ->setParameter(':language', 2)
        ;

        $query->addSelect(array(
            'productTranslation.objectdata as __product_translations',
            'variantTranslation.objectdata as __variant_translations',
            'manufacturerTranslation.objectdata as __manufacturer_translations',
            'unitTranslation.objectdata as __unit_translations'
        ));

        return $query;
    }

    /**
     * Defines which s_articles fields should be selected.
     * @return array
     */
    private function getArticleFields()
    {
        return array(
            'product.id',
            'product.supplierID',
            'product.name',
            'product.description',
            'product.description_long',
            'product.shippingtime',
            'product.datum',
            'product.active',
            'product.taxID',
            'product.pseudosales',
            'product.topseller',
            'product.metaTitle',
            'product.keywords',
            'product.changetime',
            'product.pricegroupID',
            'product.pricegroupActive',
            'product.filtergroupID',
            'product.laststock',
            'product.crossbundlelook',
            'product.notification',
            'product.template',
            'product.mode',
            'product.main_detail_id',
            'product.available_from',
            'product.available_to',
            'product.configurator_set_id'
        );
    }

    /**
     * Defines which s_articles_details fields should be selected.
     * @return array
     */
    private function getVariantFields()
    {
        return array(
            'variant.id as variantId',
            'variant.ordernumber',
            'variant.suppliernumber',
            'variant.kind',
            'variant.additionaltext',
            'variant.impressions',
            'variant.sales',
            'variant.active',
            'variant.instock',
            'variant.stockmin',
            'variant.weight',
            'variant.position',
            'variant.width',
            'variant.height',
            'variant.length',
            'variant.ean',
            'variant.unitID',
            'variant.purchasesteps',
            'variant.maxpurchase',
            'variant.minpurchase',
            'variant.purchaseunit',
            'variant.referenceunit',
            'variant.packunit',
            'variant.releasedate',
            'variant.shippingfree',
            'variant.shippingtime'
        );
    }

    /**
     * Defines which s_core_units fields should be selected
     * @return array
     */
    private function getUnitFields()
    {
        return array(
            'unit.id as __unit_id',
            'unit.unit as __unit_unit',
            'unit.description as __unit_description'
        );
    }

    /**
     * Defines which s_core_tax fields should be selected
     * @return array
     */
    private function getTaxFields()
    {
        return array(
            'tax.id as __tax_id',
            'tax.tax as __tax_tax',
            'tax.description as __tax_description'
        );
    }

    /**
     * Defines which s_core_pricegroups fields should be selected
     * @return array
     */
    private function getPriceGroupFields()
    {
        return array(
            'priceGroup.id as __priceGroup_id',
            'priceGroup.description as __priceGroup_description'
        );
    }

    /**
     * Defines which s_articles_suppliers fields should be selected
     * @return array
     */
    private function getManufacturerFields()
    {
        return array(
            'manufacturer.id as __manufacturer_id',
            'manufacturer.name as __manufacturer_name',
            'manufacturer.img as __manufacturer_img',
            'manufacturer.link as __manufacturer_link',
            'manufacturer.description as __manufacturer_description',
            'manufacturer.meta_title as __manufacturer_meta_title',
            'manufacturer.meta_description as __manufacturer_description',
            'manufacturer.meta_keywords as __manufacturer_keywords'
        );
    }
}