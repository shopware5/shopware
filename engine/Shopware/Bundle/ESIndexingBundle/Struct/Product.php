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

namespace Shopware\Bundle\ESIndexingBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Option;

/**
 * Class Product
 * @package Shopware\Bundle\ESIndexingBundle\Struct
 */
class Product extends ListProduct
{
    /**
     * @var string
     */
    protected $formattedCreatedAt;

    /**
     * @var string
     */
    protected $formattedReleaseDate;

    /**
     * @var Option[]
     */
    protected $properties = [];

    /**
     * @var int[]
     */
    protected $categoryIds = [];

    /**
     * @var Price[]
     */
    protected $calculatedPrices = [];

    /**
     * @param ListProduct $listProduct
     * @return Product
     */
    public static function createFromListProduct(ListProduct $listProduct)
    {
        $product = new self(
            $listProduct->getId(),
            $listProduct->getVariantId(),
            $listProduct->getNumber()
        );
        foreach ($listProduct as $key => $value) {
            $product->$key = $value;
        }
        return $product;
    }

    /**
     * @return Option[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param Option[] $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return int[]
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * @param int[] $categoryIds
     */
    public function setCategoryIds($categoryIds)
    {
        $this->categoryIds = $categoryIds;
    }

    /**
     * @return int
     */
    public function getFormattedCreatedAt()
    {
        return $this->formattedCreatedAt;
    }

    /**
     * @param int $formattedCreatedAt
     */
    public function setFormattedCreatedAt($formattedCreatedAt)
    {
        $this->formattedCreatedAt = $formattedCreatedAt;
    }

    /**
     * @return int
     */
    public function getFormattedReleaseDate()
    {
        return $this->formattedReleaseDate;
    }

    /**
     * @param int $formattedReleaseDate
     */
    public function setFormattedReleaseDate($formattedReleaseDate)
    {
        $this->formattedReleaseDate = $formattedReleaseDate;
    }

    /**
     * @return Price[]
     */
    public function getCalculatedPrices()
    {
        return $this->calculatedPrices;
    }

    /**
     * @param Price[] $calculatedPrices
     */
    public function setCalculatedPrices($calculatedPrices)
    {
        $this->calculatedPrices = $calculatedPrices;
    }
}
