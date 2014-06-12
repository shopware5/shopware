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
            ->addSelect($this->fieldHelper->getTopSellerFields())
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
            ->leftJoin('product', 's_articles_top_seller_ro', 'topSeller', 'topSeller.article_id = product.id')
            ->where('variant.ordernumber IN (:numbers)')
            ->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);


        $this->fieldHelper->addProductTranslation($query);
        $this->fieldHelper->addVariantTranslation($query);
        $this->fieldHelper->addManufacturerTranslation($query);
        $this->fieldHelper->addUnitTranslation($query);

        $query->setParameter(':language', $context->getShop()->getId());

        return $query;
    }
}
