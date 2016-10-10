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

namespace Shopware\Components\ProductStream;

use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Condition\PropertyCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\ImmediateDeliveryFacet;
use Shopware\Bundle\SearchBundle\Facet\ManufacturerFacet;
use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\SearchBundle\Facet\PropertyFacet;
use Shopware\Bundle\SearchBundle\Facet\ShippingFreeFacet;
use Shopware\Bundle\SearchBundle\Facet\VoteAverageFacet;
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundle\FacetResultInterface;

/**
 * Class FacetFilter
 * @package Shopware\Components\ProductStream
 */
class FacetFilter implements FacetFilterInterface
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * FacetFilter constructor.
     * @param \Shopware_Components_Config $config
     */
    public function __construct(\Shopware_Components_Config $config)
    {
        $this->config = $config;
    }


    /**
     * @param Criteria $criteria
     */
    public function add(Criteria $criteria)
    {
        if (!$criteria->hasBaseCondition('immediate_delivery') && $this->config->get('showImmediateDeliveryFacet')) {
            $criteria->addFacet(new ImmediateDeliveryFacet());
        }

        if (!$criteria->hasBaseCondition('shipping_free') && $this->config->get('showShippingFreeFacet')) {
            $criteria->addFacet(new ShippingFreeFacet());
        }

        if ($this->config->get('showPriceFacet')) {
            $criteria->addFacet(new PriceFacet());
        }

        if (!$criteria->hasBaseCondition('vote_average') && $this->config->get('showVoteAverageFacet')) {
            $criteria->addFacet(new VoteAverageFacet());
        }

        if (!$criteria->hasBaseCondition('manufacturer') && $this->config->get('showSupplierInCategories')) {
            $criteria->addFacet(new ManufacturerFacet());
        }

        if ($this->config->get('displayFiltersInListings')) {
            $criteria->addFacet(new PropertyFacet());
        }
    }

    /**
     * @param FacetResultInterface[] $facets
     * @param Criteria $criteria
     * @return FacetResultInterface[]
     */
    public function filter(array $facets, Criteria $criteria)
    {
        if (empty($facets)) {
            return $facets;
        }
        $this->removeStreamPropertyConditions($facets, $criteria);

        $this->switchPriceFilterValues($facets, $criteria);

        $this->switchActivePriceFilter($facets, $criteria);

        return $facets;
    }

    /**
     * @param string $class
     * @param Criteria $criteria
     * @return FacetResultInterface[]
     */
    private function getBaseConditionsByClass($class, Criteria $criteria)
    {
        $conditions = [];
        foreach ($criteria->getBaseConditions() as $condition) {
            if ($condition instanceof $class) {
                $conditions[] = $condition;
            }
        }
        return $conditions;
    }


    /**
     * @param FacetResultInterface[] $facets
     * @param string $name
     * @return FacetResultInterface
     */
    private function getFacetByName(array $facets, $name)
    {
        foreach ($facets as $facet) {
            if ($facet->getFacetName() === $name) {
                return $facet;
            }
        }

        return null;
    }

    /**
     * @param FacetResultInterface[] $facets
     * @param Criteria $criteria
     */
    private function switchActivePriceFilter(array $facets, Criteria $criteria)
    {
        /** @var RangeFacetResult $facet */
        $facet = $this->getFacetByName($facets, 'price');
        if (!$facet) {
            return;
        }
        if (!$criteria->hasUserCondition('price')) {
            $facet->setActive(false);
            return;
        }

        /** @var PriceCondition $condition */
        $condition = $criteria->getUserCondition('price');

        $facet->setActiveMin($condition->getMinPrice());
        $facet->setActiveMax($condition->getMaxPrice());
    }

    /**
     * @param FacetResultInterface[] $facets
     * @param Criteria $criteria
     */
    private function switchPriceFilterValues(array $facets, Criteria $criteria)
    {
        /** @var RangeFacetResult $facet */
        $facet = $this->getFacetByName($facets, 'price');

        if ($criteria->hasBaseCondition('price') && $facet) {
            /** @var PriceCondition $condition */
            $condition = $criteria->getBaseCondition('price');

            $facet->setMin($condition->getMinPrice());
            if ($condition->getMaxPrice() !== 0) {
                $facet->setMax($condition->getMaxPrice());
            }
        }
    }

    /**
     * @param FacetResultInterface[] $facets
     * @param Criteria $criteria
     */
    private function removeStreamPropertyConditions(array $facets, Criteria $criteria)
    {
        /** @var PropertyCondition[] $conditions */
        $conditions = $this->getBaseConditionsByClass('\Shopware\Bundle\SearchBundle\Condition\PropertyCondition', $criteria);
        if (!$conditions) {
            return;
        }

        /** @var FacetResultGroup $facet */
        $facet = $this->getFacetByName($facets, 'property');
        if ($facet === null) {
            return;
        }

        $new = [];
        /** @var ValueListFacetResult $propertyFacet */
        foreach ($facet->getFacetResults() as $propertyFacet) {
            $ids = array_map(
                function ($item) {
                    return $item->getId();
                }, $propertyFacet->getValues()
            );

            $filtered = false;
            foreach ($conditions as $condition) {
                $diff = array_diff($condition->getValueIds(), $ids);
                $filtered = count($condition->getValueIds()) !== count($diff);

                if ($filtered) {
                    break;
                }
            }
            if (!$filtered) {
                $new[] = $propertyFacet;
            }
        }
        $facet->setFacetResults($new);
    }
}
