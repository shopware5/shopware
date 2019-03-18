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

use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

/**
 * Defines the search result of the search gateway.
 */
class ProductNumberSearchResult extends Extendable
{
    /**
     * @var BaseProduct[] Indexed by the product order number
     */
    protected $products;

    /**
     * @var int
     */
    protected $totalCount;

    /**
     * @var FacetResultInterface[]
     */
    protected $facets;

    /**
     * @param BaseProduct[]          $products   Indexed by the product order number
     * @param int                    $totalCount
     * @param FacetResultInterface[] $facets
     * @param array                  $attributes
     */
    public function __construct($products, $totalCount, $facets, $attributes = [])
    {
        $this->products = $products;
        $this->totalCount = $totalCount;
        $this->facets = $facets;
        $this->attributes = $attributes;
    }

    /**
     * @return BaseProduct[] Indexed by the product order number
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return FacetResultInterface[]
     */
    public function getFacets()
    {
        return $this->facets;
    }

    public function addFacet(FacetResultInterface $facet)
    {
        $this->facets[] = $facet;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
