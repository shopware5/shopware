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

namespace Shopware\Bundle\ESIndexingBundle\Product;

use Shopware\Bundle\ESIndexingBundle\Struct\Product;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Option;

class ProductListingVisibilityLoader
{
    /**
     * Builds the visibility for the variant listings
     *
     * @param Product      $product
     * @param VariantFacet $facet
     *
     * @return array
     */
    public function getVisibility(Product $product, VariantFacet $facet)
    {
        $groups = $product->getFullConfiguration();
        $groups = array_filter($groups, function (Group $group) use ($facet) {
            return in_array($group->getId(), $facet->getExpandGroupIds(), true);
        });

        $splitting = $this->createSplitting(
            $groups,
            $product->getAvailableCombinations()
        );

        return $this->buildListingVisibility($splitting, $product->getConfiguration());
    }

    /**
     * Combines all array elements with all array elements
     *
     * @param array $array
     *
     * @return array
     */
    public function arrayCombinations(array $array)
    {
        $results = [[]];

        foreach ($array as $element) {
            foreach ($results as $combination) {
                array_push($results, array_merge([$element], $combination));
            }
        }

        return array_filter($results);
    }

    private function createSplitting(array $groups, array $availability)
    {
        $c = $this->arrayCombinations(array_keys($groups));

        //flip keys for later intersection
        $keys = array_flip(array_keys($groups));

        $result = [];
        foreach ($c as $combination) {
            //flip combination to use key intersect
            $combination = array_flip($combination);

            //all options of groups will be combined together
            $full = array_intersect_key($groups, $combination);

            $first = array_intersect_key($groups, array_diff_key($keys, $combination));

            usort($full, function (Group $a, Group $b) {
                return $a->getId() > $b->getId();
            });

            //create unique group key
            $groupKey = array_map(function (Group $group) {
                return $group->getId();
            }, $full);
            $groupKey = 'g' . implode('-', $groupKey);

            $all = array_filter(array_merge($full, $first));

            $firstIds = array_map(function (Group $group) {
                return $group->getId();
            }, $first);

            $result[$groupKey] = $this->nestedArrayCombinations($all, $firstIds, $availability);
        }

        return $result;
    }

    /**
     * Builds all possible combinations of an nested array
     *
     * @param array   $groups
     * @param Group[] $onlyFirst
     * @param array   $availability
     *
     * @return array
     */
    private function nestedArrayCombinations(array $groups, array $onlyFirst, array $availability)
    {
        $result = [[]];

        $groups = array_values($groups);

        /** @var Group $group */
        foreach ($groups as $index => $group) {
            $isFirst = in_array($group->getId(), $onlyFirst, true);
            $new = [];
            foreach ($result as $item) {
                $options = array_values($group->getOptions());

                usort($options, function (Option $a, Option $b) {
                    return $a->getId() > $b->getId();
                });

                /** @var Option $option */
                foreach ($options as $option) {
                    $tmp = array_merge($item, [$index => (int) $option->getId()]);
                    sort($tmp, SORT_NUMERIC);

                    $isAvailable = false;
                    foreach ($availability as $available) {
                        $available = '-' . $available . '-';

                        $allMatch = true;
                        foreach ($tmp as $key) {
                            if (strpos($available, '-' . $key . '-') === false) {
                                $allMatch = false;
                            }
                        }
                        if ($allMatch) {
                            $isAvailable = true;
                            break;
                        }
                    }

                    if (!$isAvailable) {
                        continue;
                    }

                    $new[] = $tmp;

                    if ($isFirst) {
                        break;
                    }
                }
            }

            if (empty($new)) {
                continue;
            }

            $result = $new;
        }

        foreach ($result as &$toImplode) {
            $toImplode = implode('-', $toImplode);
        }

        return $result;
    }

    private function buildListingVisibility(array $splitting, array $configuration)
    {
        $key = [];

        usort($configuration, function (Group $a, Group $b) {
            return $a->getId() > $b->getId();
        });

        /** @var Group $group */
        foreach ($configuration as $group) {
            foreach ($group->getOptions() as $option) {
                $key[] = $option->getId();
            }
        }
        sort($key, SORT_NUMERIC);
        $key = implode('-', $key);

        $visibility = [];

        foreach ($splitting as $combination => $variants) {
            $visibility[$combination] = in_array($key, $variants);
        }

        return $visibility;
    }
}
