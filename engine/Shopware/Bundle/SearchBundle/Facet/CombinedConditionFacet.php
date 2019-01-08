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

namespace Shopware\Bundle\SearchBundle\Facet;

use Shopware\Bundle\SearchBundle\Condition\OrdernumberCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Components\ReflectionHelper;

class CombinedConditionFacet implements FacetInterface
{
    /**
     * @var ConditionInterface[]
     */
    protected $conditions;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $requestParameter;

    /**
     * @param string|array $conditions
     * @param string       $label
     * @param string       $requestParameter
     * @param array|null   $stream
     */
    public function __construct($conditions, $label, $requestParameter, $stream = null)
    {
        if (is_array($conditions)) {
            $this->conditions = $conditions;
        } else {
            $this->conditions = $this->unserialize(json_decode($conditions, true));
        }
        $this->label = $label;
        $this->requestParameter = $requestParameter;

        if (!$stream) {
            return;
        }

        if (!empty($stream['numbers'])) {
            $numbers = array_filter(explode(',', $stream['numbers']));
            $this->conditions = [new OrdernumberCondition($numbers)];

            return;
        }

        $this->conditions = $this->unserialize(json_decode($stream['conditions'], true));
    }

    /**
     * @return string
     */
    public function getName()
    {
        $classes = array_map(function ($class) {
            return get_class($class);
        }, $this->conditions);

        return 'combined_facet_' . md5(json_encode($this->conditions) . json_encode($classes));
    }

    /**
     * @return \Shopware\Bundle\SearchBundle\ConditionInterface[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getRequestParameter()
    {
        return $this->requestParameter;
    }

    /**
     * @param array $serialized
     *
     * @return ConditionInterface[]
     */
    private function unserialize($serialized)
    {
        $reflector = new ReflectionHelper();
        if (empty($serialized)) {
            return [];
        }
        /** @var array<int, ConditionInterface> $sortings */
        $sortings = [];
        foreach ($serialized as $className => $arguments) {
            $className = explode('|', $className);
            $className = $className[0];
            /** @var ConditionInterface $condition */
            $condition = $reflector->createInstanceFromNamedArguments($className, $arguments);
            $sortings[] = $condition;
        }

        return $sortings;
    }
}
