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

use Assert\Assertion;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;
use Shopware\Components\ReflectionAwareInterface;

/**
 * The criteria object is used for the search gateway.
 *
 * The sorting, facet and condition classes are defined global and has
 * to be compatible with all gateway engines.
 *
 * Each of this sorting, facet and condition classes are handled by their
 * own handler classes which implemented for each gateway engine.
 */
class Criteria extends Extendable implements ReflectionAwareInterface
{
    /**
     * Offset for the limitation
     *
     * @var int
     */
    private $offset;

    /**
     * Count of result
     *
     * @var int
     */
    private $limit;

    /**
     * @var ConditionInterface[]
     */
    private $baseConditions = [];

    /**
     * @var ConditionInterface[]
     */
    private $conditions = [];

    /**
     * @var FacetInterface[]
     */
    private $facets = [];

    /**
     * @var SortingInterface[]
     */
    private $sortings = [];

    /**
     * @var bool
     */
    private $generatePartialFacets = false;

    /**
     * @var bool
     */
    private $fetchCount = true;

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function offset($offset)
    {
        Assertion::min($offset, 0, 'The offset must be greater than equals 0');
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param int|null $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        if ($limit === null) {
            $this->limit = null;

            return $this;
        }

        Assertion::min($limit, 1, 'The limit must be greater than equals 1');
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasCondition($name)
    {
        if (array_key_exists($name, $this->baseConditions)) {
            return true;
        }

        return array_key_exists($name, $this->conditions);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasBaseCondition($name)
    {
        return array_key_exists($name, $this->baseConditions);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasUserCondition($name)
    {
        return array_key_exists($name, $this->conditions);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasSorting($name)
    {
        return array_key_exists($name, $this->sortings);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasFacet($name)
    {
        return array_key_exists($name, $this->facets);
    }

    /**
     * @return $this
     */
    public function addFacet(FacetInterface $facet)
    {
        $this->facets[$facet->getName()] = $facet;

        return $this;
    }

    /**
     * @return $this
     */
    public function addCondition(ConditionInterface $condition)
    {
        $this->conditions[$condition->getName()] = $condition;

        return $this;
    }

    /**
     * @return $this
     */
    public function addBaseCondition(ConditionInterface $condition)
    {
        $this->baseConditions[$condition->getName()] = $condition;

        return $this;
    }

    /**
     * @return $this
     */
    public function addSorting(SortingInterface $sorting)
    {
        $this->sortings[$sorting->getName()] = $sorting;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return ConditionInterface|null
     */
    public function getCondition($name)
    {
        if (array_key_exists($name, $this->baseConditions)) {
            return $this->baseConditions[$name];
        }

        if (array_key_exists($name, $this->conditions)) {
            return $this->conditions[$name];
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return ConditionInterface
     */
    public function getBaseCondition($name)
    {
        return $this->baseConditions[$name];
    }

    /**
     * @param string $name
     *
     * @return ConditionInterface
     */
    public function getUserCondition($name)
    {
        return $this->conditions[$name];
    }

    /**
     * @param string $name
     *
     * @return FacetInterface|null
     */
    public function getFacet($name)
    {
        return $this->facets[$name];
    }

    /**
     * @param string $name
     *
     * @return SortingInterface|null
     */
    public function getSorting($name)
    {
        return $this->sortings[$name];
    }

    /**
     * Returns all conditions, including the base conditions.
     *
     * Do not rely on the array key or the order of the returned conditions.
     *
     * @return \Shopware\Bundle\SearchBundle\ConditionInterface[]
     */
    public function getConditions()
    {
        return array_merge(
            array_values($this->baseConditions),
            array_values($this->conditions)
        );
    }

    /**
     * @return \Shopware\Bundle\SearchBundle\FacetInterface[]
     */
    public function getFacets()
    {
        return $this->facets;
    }

    /**
     * @return \Shopware\Bundle\SearchBundle\SortingInterface[]
     */
    public function getSortings()
    {
        return $this->sortings;
    }

    /**
     * Allows to reset the internal sorting collection.
     *
     * @return $this
     */
    public function resetSorting()
    {
        $this->sortings = [];

        return $this;
    }

    /**
     * Allows to reset the internal base condition collection.
     *
     * @return $this
     */
    public function resetBaseConditions()
    {
        $this->baseConditions = [];

        return $this;
    }

    /**
     * Allows to reset the internal condition collection.
     *
     * @return $this
     */
    public function resetConditions()
    {
        $this->conditions = [];

        return $this;
    }

    /**
     * Allows to reset the internal facet collection.
     *
     * @return $this
     */
    public function resetFacets()
    {
        $this->facets = [];

        return $this;
    }

    /**
     * Removes a condition of the current criteria object.
     *
     * @param string $name
     */
    public function removeCondition($name)
    {
        if (array_key_exists($name, $this->conditions)) {
            unset($this->conditions[$name]);
        }
    }

    /**
     * Removes a base condition of the current criteria object.
     *
     * @param string $name
     */
    public function removeBaseCondition($name)
    {
        if (array_key_exists($name, $this->baseConditions)) {
            unset($this->baseConditions[$name]);
        }
    }

    /**
     * Removes a facet of the current criteria object.
     *
     * @param string $name
     */
    public function removeFacet($name)
    {
        if (array_key_exists($name, $this->facets)) {
            unset($this->facets[$name]);
        }
    }

    /**
     * Removes a sorting of the current criteria object.
     *
     * @param string $name
     */
    public function removeSorting($name)
    {
        if (array_key_exists($name, $this->sortings)) {
            unset($this->sortings[$name]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $data = get_object_vars($this);

        $data['baseConditions'] = [];
        foreach ($this->baseConditions as $object) {
            $data['baseConditions'][get_class($object)] = $object;
        }

        $data['conditions'] = [];
        foreach ($this->conditions as $object) {
            $data['conditions'][get_class($object)] = $object;
        }

        $data['sortings'] = [];
        foreach ($this->sortings as $object) {
            $data['sortings'][get_class($object)] = $object;
        }

        $data['facets'] = [];
        foreach ($this->facets as $object) {
            $data['facets'][get_class($object)] = $object;
        }

        return $data;
    }

    /**
     * @return ConditionInterface[]
     */
    public function getBaseConditions()
    {
        return $this->baseConditions;
    }

    /**
     * @return bool
     */
    public function generatePartialFacets()
    {
        return $this->generatePartialFacets;
    }

    /**
     * @param bool $generatePartialFacets
     */
    public function setGeneratePartialFacets($generatePartialFacets)
    {
        $this->generatePartialFacets = $generatePartialFacets;
    }

    /**
     * @return ConditionInterface[]
     */
    public function getUserConditions()
    {
        return $this->conditions;
    }

    /**
     * @return bool
     */
    public function fetchCount()
    {
        return $this->fetchCount;
    }

    /**
     * @param bool $fetchCount
     *
     * @return $this
     */
    public function setFetchCount($fetchCount)
    {
        $this->fetchCount = $fetchCount;

        return $this;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function hasConditionOfClass($class)
    {
        $conditions = $this->getConditionsByClass($class);

        return !empty($conditions);
    }

    /**
     * @param string $class
     *
     * @return ConditionInterface[]
     */
    public function getConditionsByClass($class)
    {
        return array_filter(
            $this->getConditions(),
            function (ConditionInterface $condition) use ($class) {
                return $condition instanceof $class;
            }
        );
    }
}
