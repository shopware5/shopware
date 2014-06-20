<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:57
 */
namespace Shopware\Service;

use Shopware\Struct;

interface SimilarProducts
{
    /**
     * @see Shopware\Service\SimilarProducts::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return array Indexed with the product number, the values are a list of ListProduct structs.
     */
    public function getList(array $products, Struct\Context $context);

    /**
     * Selects all similar products for the provided product.
     *
     * The relation between the products are selected over the \Shopware\Gateway\SimilarProducts class.
     * After the relation is selected, the \Shopware\Service\ListProduct is used to load
     * the whole product data for the relations.
     *
     * If the product has no manually assigned similar products, the function selects the fallback similar products
     * over the same category.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Service\ListProduct::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\ListProduct[] Indexed by the product order number.
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);
}
