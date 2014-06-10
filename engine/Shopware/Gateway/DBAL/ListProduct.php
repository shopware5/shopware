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
class ListProduct
{
    /**
     * @var \Shopware\Gateway\DBAL\Hydrator\Product
     */
    protected $hydrator;


    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var FieldHelper
     */
    protected $fieldHelper;


    /**
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param \Shopware\Gateway\DBAL\Hydrator\Product $hydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Product $hydrator
    ) {
        $this->hydrator = $hydrator;
        $this->fieldHelper = $fieldHelper;
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
     * Required states:
     *  - ListProduct::STATE_TRANSLATED
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
            $key = $product['__variant_ordernumber'];
            $products[$key] = $this->hydrator->hydrateListProduct($product);
        }

        return $products;
    }

    protected function getQuery(array $numbers, Struct\Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->fieldHelper->getArticleFields())
            ->addSelect($this->fieldHelper->getVariantFields())
            ->addSelect($this->fieldHelper->getUnitFields())
            ->addSelect($this->fieldHelper->getTaxFields())
            ->addSelect($this->fieldHelper->getPriceGroupFields())
            ->addSelect($this->fieldHelper->getManufacturerFields());

        $query->from('s_articles_details', 'variant')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->innerJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID')
            ->leftJoin('variant', 's_core_units', 'unit', 'unit.id = variant.unitID')
            ->leftJoin('product', 's_articles_supplier', 'manufacturer', 'manufacturer.id = product.supplierID')
            ->leftJoin('product', 's_core_pricegroups', 'priceGroup', 'priceGroup.id = product.pricegroupID')
            ->leftJoin('variant', 's_articles_attributes', 'productAttribute', 'productAttribute.articledetailsID = variant.id')
            ->leftJoin('product', 's_articles_supplier_attributes', 'manufacturerAttribute', 'manufacturerAttribute.id = product.supplierID')
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
             unitTranslation.objectkey = 1 AND
             unitTranslation.objectlanguage = :language'
        );

        $query->setParameter(':productType', 'article')
            ->setParameter(':variantType', 'variant')
            ->setParameter(':manufacturerType', 'supplier')
            ->setParameter(':unitType', 'config_units')
            ->setParameter(':language', $context->getShop()->getId())
        ;

        $query->addSelect(array(
            'productTranslation.objectdata as __product_translation',
            'variantTranslation.objectdata as __variant_translation',
            'manufacturerTranslation.objectdata as __manufacturer_translation',
            'unitTranslation.objectdata as __unit_translation'
        ));

        return $query;
    }
}