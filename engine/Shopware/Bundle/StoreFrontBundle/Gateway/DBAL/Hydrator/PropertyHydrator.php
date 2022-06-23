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

use Shopware\Bundle\StoreFrontBundle\Struct\Property\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Option;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Set;

class PropertyHydrator extends Hydrator
{
    private AttributeHydrator $attributeHydrator;

    private MediaHydrator $mediaHydrator;

    public function __construct(AttributeHydrator $attributeHydrator, MediaHydrator $mediaHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
        $this->mediaHydrator = $mediaHydrator;
    }

    /**
     * @param array<array<string, mixed>> $data
     *
     * @return Set[]
     */
    public function hydrateValues(array $data)
    {
        $this->sortGroups($data);

        $sets = [];

        foreach ($data as $row) {
            $setId = $row['__propertySet_id'];
            $groupId = $row['__propertyGroup_id'];
            $optionId = $row['__propertyOption_id'];

            $set = $sets[$setId] ?? $this->hydrateSet($row);

            $groups = $set->getGroups();
            $group = $groups[$groupId] ?? $this->hydrateGroup($row);

            $options = $group->getOptions();
            $options[$optionId] = $this->hydrateOption($row);
            $groups[$groupId] = $group;
            $sets[$setId] = $set;

            $group->setOptions($options);
            $set->setGroups($groups);
        }

        foreach ($sets as $set) {
            foreach ($set->getGroups() as $group) {
                $options = $group->getOptions();
                $this->sortOptions($options, $set->getSortMode());
                $group->setOptions($options);
            }
        }

        return $sets;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Group
     */
    public function hydrateGroup(array $data)
    {
        $group = new Group();
        $translation = $this->getTranslation($data, '__propertyGroup', ['optionName' => 'name']);
        $data = array_merge($data, $translation);

        $group->setId((int) $data['__propertyGroup_id']);
        $group->setName($data['__propertyGroup_name']);
        $group->setFilterable((bool) $data['__propertyGroup_filterable']);

        if ($data['__propertyGroupAttribute_id']) {
            $this->attributeHydrator->addAttribute($group, $data, 'propertyGroupAttribute', null, 'propertyGroup');
        }

        return $group;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Option
     */
    public function hydrateOption(array $data)
    {
        $option = new Option();
        $translation = $this->getTranslation($data, '__propertyOption', ['optionValue' => 'value']);
        $data = array_merge($data, $translation);

        $option->setId((int) $data['__propertyOption_id']);
        $option->setName($data['__propertyOption_value']);
        $option->setPosition((int) $data['__propertyOption_position']);

        if ($data['__propertyOptionAttribute_id']) {
            $this->attributeHydrator->addAttribute($option, $data, 'propertyOptionAttribute', 'core', 'propertyOption');
        }

        if (isset($data['__media_id']) && $data['__media_id']) {
            $option->setMedia(
                $this->mediaHydrator->hydrate($data)
            );
        }

        return $option;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hydrateSet(array $data): Set
    {
        $set = new Set();
        $translation = $this->getTranslation($data, '__propertySet', ['groupName' => 'name']);
        $data = array_merge($data, $translation);

        $set->setId((int) $data['__propertySet_id']);
        $set->setName($data['__propertySet_name']);
        $set->setComparable((bool) $data['__propertySet_comparable']);
        $set->setSortMode((int) $data['__propertySet_sortmode']);

        if ($data['__propertySetAttribute_id']) {
            $this->attributeHydrator->addAttribute($set, $data, 'propertySetAttribute');
        }

        return $set;
    }

    /**
     * Sort groups by position in set
     *
     * @param array<array<string, mixed>> $data
     */
    private function sortGroups(array &$data): void
    {
        usort($data, function (array $a, array $b): int {
            return $a['__relations_position'] <=> $b['__relations_position'];
        });
    }

    /**
     * @param array<Option> $options
     */
    private function sortOptions(array &$options, int $sortMode): void
    {
        if ($sortMode === Set::SORT_POSITION) {
            $this->sortOptionsByPosition($options);

            return;
        }

        if ($sortMode === Set::SORT_NUMERIC) {
            $this->sortOptionsNumericalValue($options);

            return;
        }

        $this->sortOptionsAlphanumeric($options);
    }

    /**
     * @param array<Option> $options
     */
    private function sortOptionsByPosition(array &$options): void
    {
        usort($options, function (Option $a, Option $b): int {
            return $a->getPosition() <=> $b->getPosition();
        });
    }

    /**
     * @param array<Option> $options
     */
    private function sortOptionsNumericalValue(array &$options): void
    {
        usort($options, function (Option $a, Option $b): int {
            $aValue = (float) str_replace(',', '.', $a->getName());
            $bValue = (float) str_replace(',', '.', $b->getName());

            return $aValue <=> $bValue;
        });
    }

    /**
     * @param array<Option> $options
     */
    private function sortOptionsAlphanumeric(array &$options): void
    {
        usort($options, function (Option $a, Option $b): int {
            return strnatcasecmp($a->getName(), $b->getName());
        });
    }
}
