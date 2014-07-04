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

namespace Shopware\Bundle\SearchBundle\Facet;

use Shopware\Bundle\SearchBundle\FacetInterface;

/**
 * @package Shopware\Bundle\SearchBundle\Facet
 */
class PriceFacet implements FacetInterface
{
    /**
     * @var float
     */
    private $minPrice;

    /**
     * @var float
     */
    private $maxPrice;

    /**
     * Flag if the facet is filtered with a condition
     * @var bool
     */
    private $filtered = false;

    public function getName()
    {
        return 'price_facet';
    }

    /**
     * @param bool $filtered
     */
    public function setFiltered($filtered)
    {
        $this->filtered = $filtered;
    }

    /**
     * @return bool
     */
    public function isFiltered()
    {
        return $this->filtered;
    }

    /**
     * @param float $minPrice
     */
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;
    }

    /**
     * @return float
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * @param float $maxPrice
     */
    public function setMaxPrice($maxPrice)
    {
        $this->maxPrice = $maxPrice;
    }

    /**
     * @return float
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }
}
