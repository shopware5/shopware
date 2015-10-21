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
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\ImmediateDeliveryFacet;
use Shopware\Bundle\SearchBundle\Facet\ManufacturerFacet;
use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\SearchBundle\Facet\PropertyFacet;
use Shopware\Bundle\SearchBundle\Facet\ShippingFreeFacet;
use Shopware\Bundle\SearchBundle\Facet\VoteAverageFacet;
use Shopware\Bundle\SearchBundle\FacetResultInterface;

/**
 * Class FacetFilter
 * @package Shopware\Components\ProductStream
 */
class FacetFilter implements FacetFilterInterface
{
    /**
     * @param Criteria $criteria
     */
    public function add(Criteria $criteria)
    {
        $criteria->addFacet(new PriceFacet());
        $criteria->addFacet(new PropertyFacet());

        if (!$criteria->hasBaseCondition('immediate_delivery')) {
            $criteria->addFacet(new ImmediateDeliveryFacet());
        }

        if (!$criteria->hasBaseCondition('manufacturer')) {
            $criteria->addFacet(new ManufacturerFacet());
        }

        if (!$criteria->hasBaseCondition('shipping_free')) {
            $criteria->addFacet(new ShippingFreeFacet());
        }

        if (!$criteria->hasBaseCondition('vote_average')) {
            $criteria->addFacet(new VoteAverageFacet());
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
        /** @var \Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult $facet */
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
        /** @var \Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult $facet */
        $facet = $this->getFacetByName($facets, 'price');

        if ($criteria->hasBaseCondition('price') && $facet) {
            /** @var \Shopware\Bundle\SearchBundle\Condition\PriceCondition $condition */
            $condition = $criteria->getBaseCondition('price');

            $facet->setMin($condition->getMinPrice());
            $facet->setMax($condition->getMaxPrice());
        }
    }

    /**
     * @param FacetResultInterface[] $facets
     * @param Criteria $criteria
     */
    private function removeStreamPropertyConditions(array $facets, Criteria $criteria)
    {
        /** @var \Shopware\Bundle\SearchBundle\Condition\PropertyCondition[] $conditions */
        if ($conditions = $this->getBaseConditionsByClass('\Shopware\Bundle\SearchBundle\Condition\PropertyCondition', $criteria)) {

            /** @var \Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup $facet */
            $facet = $this->getFacetByName($facets, 'property');

            $new = [];
            /** @var \Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult $propertyFacet */
            foreach ($facet->getFacetResults() as $propertyFacet) {
                $ids = array_map(
                    function (\Shopware\Bundle\SearchBundle\FacetResult\ValueListItem $item) {
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
}
