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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomSorting;
use Shopware\Components\ReflectionHelper;

class CustomListingHydrator extends Hydrator
{
    /**
     * @var ReflectionHelper
     */
    private $reflector;

    public function __construct()
    {
        $this->reflector = new ReflectionHelper();
    }

    /**
     * @param array $data
     * @return CustomSorting
     */
    public function hydrateSorting(array $data)
    {
        $id = (int) $data['__customSorting_id'];
        $translation = $this->getTranslation($data, '__customSorting', [], $id);
        $data = array_merge($data, $translation);

        $sorting = new CustomSorting();
        $sorting->setId($id);
        $sorting->setDisplayInCategories((bool) $data['__customSorting_display_in_categories']);
        $sorting->setLabel($data['__customSorting_label']);
        $sorting->setPosition((int) $data['__customSorting_position']);

        $sorting->setSortings(
            $this->unserialize(
                json_decode($data['__customSorting_sortings'], true)
            )
        );

        return $sorting;
    }

    /**
     * @param array[] $serialized
     * @return SortingInterface[]|FacetInterface[]
     */
    private function unserialize($serialized)
    {
        if (empty($serialized)) {
            return [];
        }

        $sortings = [];
        foreach ($serialized as $className => $arguments) {
            $className = explode('|', $className);
            $className = $className[0];
            $sortings[] = $this->reflector->createInstanceFromNamedArguments($className, $arguments);
        }

        return $sortings;
    }

    /**
     * @param array $data
     * @return CustomFacet
     */
    public function hydrateFacet(array $data)
    {
        $id = (int) $data['__customFacet_id'];
        $translation = $this->getTranslation($data, '__customFacet', [], $id);
        $data = array_merge($data, $translation);

        $customFacet = new CustomFacet();

        $customFacet->setId($id);
        $customFacet->setUniqueKey($data['__customFacet_unique_key']);
        $customFacet->setName($data['__customFacet_name']);
        $customFacet->setPosition((int) $data['__customFacet_position']);

        $translation = $this->extractFields('__customFacet_', $translation);
        $facets = json_decode($data['__customFacet_facet'], true);

        foreach ($facets as &$facet) {
            $facet = array_merge($facet, $translation);
        }

        $facets = $this->unserialize($facets);
        $customFacet->setFacet(array_shift($facets));

        return $customFacet;
    }
}
