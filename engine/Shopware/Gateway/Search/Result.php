<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Gateway\Search;

/**
 * @package Shopware\Gateway\Search
 */
class Result
{
    /**
     * @var Product[]
     */
    protected $products;

    /**
     * @var int
     */
    protected $totalCount;

    /**
     * @var Facet[]
     */
    protected $facets;

    function __construct(array $products, $totalCount, $facets)
    {
        $this->products = $products;
        $this->totalCount = $totalCount;
        $this->facets = $facets;
    }

    /**
     * @return \Shopware\Gateway\Search\Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return Facet[]
     */
    public function getFacets()
    {
        return $this->facets;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }


}
