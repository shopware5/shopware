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
    protected $fullConfiguration = [];

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
     * @var array<int, int>
     */
    protected $manualSorting = [];

    /**
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
     * @return string
     */
    public function getFormattedCreatedAt()
    {
        return $this->formattedCreatedAt;
    }

    /**
     * @param string $formattedCreatedAt
     */
    public function setFormattedCreatedAt($formattedCreatedAt)
    {
        $this->formattedCreatedAt = $formattedCreatedAt;
    }

    /**
     * @return string
     */
    public function getFormattedUpdatedAt()
    {
        return $this->formattedUpdatedAt;
    }

    /**
     * @param string $formattedUpdatedAt
     */
    public function setFormattedUpdatedAt($formattedUpdatedAt)
    {
        $this->formattedUpdatedAt = $formattedUpdatedAt;
    }

    /**
     * @return string
     */
    public function getFormattedReleaseDate()
    {
        return $this->formattedReleaseDate;
    }

    /**
     * @param string $formattedReleaseDate
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

    public function setAvailableCombinations(array $combinations = null)
    {
        $this->availableCombinations = $combinations;
    }

    public function getAvailableCombinations()
    {
        return $this->availableCombinations;
    }

    /**
     * @param Group[]|null $fullConfiguration
     */
    public function setFullConfiguration(array $fullConfiguration = null)
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

    public function getManualSorting(): array
    {
        return $this->manualSorting;
    }

    /**
     * @param array<int, int> $manualSorting
     */
    public function setManualSorting(array $manualSorting): void
    {
        $this->manualSorting = $manualSorting;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        unset(
            $data['fullConfiguration'],
            $data['releaseDate'],
            $data['cheapestPrice'],
            $data['priceRules'],
            $data['prices'],
            $data['allowBuyInListing'],
            $data['displayFromPrice'],
            $data['cover']
        );

        return $data;
    }
}
