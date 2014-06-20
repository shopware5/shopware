<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:52
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface VariantMedia
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\VariantMedia::get()
     *
     * The passed $products array contains in some case two variations of the same product.
     * For example:
     *  - Product.1  (white / XL)
     *  - Product.2  (black / L)
     *
     * The
     * <php>
     * array(
     *     'Product.1' => array(
     *          Shopware\Struct\Media(id=3)  (configuration: color=white / size=XL)
     *          Shopware\Struct\Media(id=4)  (configuration: color=white)
     *      ),
     *     'Product.2' => array(
     *          Shopware\Struct\Media(id=1)  (configuration: color=black)
     *          Shopware\Struct\Media(id=2)  (configuration: size=L)
     *      )
     * )
     * </php>
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return array Indexed by product number. Each element contains a \Shopware\Struct\Media array.
     */
    public function getList(array $products, Struct\Context $context);

    /**
     * The \Shopware\Struct\Media requires the following data:
     * - Product image data
     * - Media data
     * - Core attribute of the product image
     * - Core attribute of the media
     *
     * Required translation in the provided context language:
     * - Product image
     *
     * Required conditions for the selection:
     * - Selects only product media which has no configurator configuration
     * - Sorted ascending by the image main flag and position
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Media
     */
    public function getCover(Struct\ListProduct $product, Struct\Context $context);

    /**
     * The \Shopware\Struct\Media requires the following data:
     * - Product image data
     * - Media data
     * - Core attribute of the product image
     * - Core attribute of the media
     *
     * Required translation in the provided context language:
     * - Product image
     *
     * Required conditions for the selection:
     * - Selects only product media which has a configurator configuration for the provided variants.
     * - Sorted ascending by the image main flag and image position
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Media[]
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);

    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\VariantMedia::getCover()
     *
     * The passed $products array contains in some case two variations of the same product.
     * For example:
     *  - Product.1  (white / XL)
     *  - Product.2  (black / L)
     *
     * The
     * <php>
     * array(
     *     'Product.1' => Shopware\Struct\Media(id=4)  (configuration: color=white)
     *     'Product.2' => Shopware\Struct\Media(id=1)  (configuration: color=black)
     * )
     * </php>
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return Struct\Media[] Indexed by the product order number
     */
    public function getCovers(array $products, Struct\Context $context);
}