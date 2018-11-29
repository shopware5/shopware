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

use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Option;

/**
 * Class Product
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
    protected $formattedUpdatedAt;

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
     * @var Group[]
     */
    protected $configuration = [];

    /**
     * @var array
     */
    protected $visibility = [];

    /**
     * @var array
     */
    protected $availability = [];

    /**
     * @var Group[]
     */
    protected $fullConfiguration;

    /**
     * @var string[]
     */
    protected $availableCombinations = [];

    /**
     * @var array
     */
    protected $listingVariationPrices = [];

    /**
     * @var array
     */
    protected $filterConfiguration = [];

    /**
     * @param ListProduct $listProduct
     *
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
    public function getFormattedUpdatedAt()
    {
        return $this->formattedUpdatedAt;
    }

    /**
     * @param int $formattedUpdatedAt
     */
    public function setFormattedUpdatedAt($formattedUpdatedAt)
    {
        $this->formattedUpdatedAt = $formattedUpdatedAt;
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

    /**
     * @param Group[] $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return Group[]
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function setVisibility(array $visibility)
    {
        $this->visibility = $visibility;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    public function setAvailableCombinations(array $combinations)
    {
        $this->availableCombinations = $combinations;
    }

    public function getAvailableCombinations()
    {
        return $this->availableCombinations;
    }

    /**
     * @param Group[] $fullConfiguration
     */
    public function setFullConfiguration(array $fullConfiguration)
    {
        $this->fullConfiguration = $fullConfiguration;
    }

    /**
     * @return Group[]
     */
    public function getFullConfiguration()
    {
        return $this->fullConfiguration;
    }

    public function setListingVariationPrices(array $prices)
    {
        $this->listingVariationPrices = $prices;
    }

    public function getListingVariationPrices()
    {
        return $this->listingVariationPrices;
    }

    public function setFilterConfiguration(array $filterConfiguration)
    {
        $this->filterConfiguration = $filterConfiguration;
    }

    public function getFilterConfiguration()
    {
        return $this->filterConfiguration;
    }

    /**
     * @return array
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @param array $availability
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;
    }
}
