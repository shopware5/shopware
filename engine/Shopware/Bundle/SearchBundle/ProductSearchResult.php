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

namespace Shopware\Bundle\SearchBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;

/**
 * Defines the search result of the search gateway.
 *
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductSearchResult extends ProductNumberSearchResult implements \JsonSerializable
{
    /**
     * @var ListProduct[] Indexed by the product order number
     */
    protected $products;

    /**
     * @param ListProduct[] $products Indexed by the product order number
     * @param int $totalCount
     * @param FacetResultInterface[] $facets
     */
    public function __construct($products, $totalCount, $facets)
    {
        $this->products = $products;
        $this->totalCount = $totalCount;
        $this->facets = $facets;
    }

    /**
     * @return ListProduct[] Indexed by the product order number
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
