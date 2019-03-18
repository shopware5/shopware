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

use Shopware\Bundle\StoreFrontBundle\Struct;

class PropertyHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var MediaHydrator
     */
    private $mediaHydrator;

    public function __construct(
        AttributeHydrator $attributeHydrator,
        MediaHydrator $mediaHydrator
    ) {
        $this->attributeHydrator = $attributeHydrator;
        $this->mediaHydrator = $mediaHydrator;
    }

    /**
     * @return Struct\Property\Set[]
     */
    public function hydrateValues(array $data)
    {
        $this->sortGroups($data);

        $sets = [];

        foreach ($data as $row) {
            $setId = $row['__propertySet_id'];
            $groupId = $row['__propertyGroup_id'];
            $optionId = $row['__propertyOption_id'];

            if (isset($sets[$setId])) {
                $set = $sets[$setId];
            } else {
                $set = $this->hydrateSet($row);
            }

            $groups = $set->getGroups();
            if (isset($groups[$groupId])) {
                $group = $groups[$groupId];
            } else {
                $group = $this->hydrateGroup($row);
            }

            $options = $group->getOptions();
            $option = $this->hydrateOption($row);

            $options[$optionId] = $option;
            $groups[$groupId] = $group;
            $sets[$setId] = $set;

            $group->setOptions($options);
            $set->setGroups($groups);
        }

        /** @var Struct\Property\Set[] $sets */
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
     * @return Struct\Property\Group
     */
    public function hydrateGroup(array $data)
    {
        $group = new Struct\Property\Group();
        $translation = $this->getTranslation($data, '__propertyGroup', ['optionName' => 'name']);
        $data = array_merge($data, $translation);

        $group->setId((int) $data['__propertyGroup_id']);
        $group->setName($data['__propertyGroup_name']);
        $group->setFilterable((bool) $data['__propertyGroup_filterable']);

        if ($data['__propertyGroupAttribute_id']) {
            $this->attributeHydrator->addAttribute($group, $data, 'propertyGroupAttribute');
        }

        return $group;
    }

    /**
     * @return Struct\Property\Option
     */
    public function hydrateOption(array $data)
    {
        $option = new Struct\Property\Option();
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
     * @return Struct\Property\Set
     */
    private function hydrateSet(array $data)
    {
        $set = new Struct\Property\Set();
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
     */
    private function sortGroups(array &$data)
    {
        usort($data, function ($a, $b) {
            if ($a['__relations_position'] == $b['__relations_position']) {
                return 0;
            }

            return ($a['__relations_position'] < $b['__relations_position']) ? -1 : 1;
        });
    }

    /**
     * @param Struct\Property\Option[] $options
     * @param int                      $sortMode
     */
    private function sortOptions(&$options, $sortMode)
    {
        if ($sortMode == Struct\Property\Set::SORT_POSITION) {
            $this->sortOptionsByPosition($options);

            return;
        }

        if ($sortMode == Struct\Property\Set::SORT_NUMERIC) {
            $this->sortOptionsNumercialValue($options);

            return;
        }

        $this->sortOptionsAlphanumeric($options);
    }

    /**
     * @param Struct\Property\Option[] $options
     */
    private function sortOptionsByPosition(&$options)
    {
        usort($options, function (Struct\Property\Option $a, Struct\Property\Option $b) {
            if ($a->getPosition() == $b->getPosition()) {
                return 0;
            }

            return ($a->getPosition() < $b->getPosition()) ? -1 : 1;
        });
    }

    /**
     * @param Struct\Property\Option[] $options
     */
    private function sortOptionsNumercialValue(&$options)
    {
        usort($options, function (Struct\Property\Option $a, Struct\Property\Option $b) {
            $a = (float) str_replace(',', '.', $a->getName());
            $b = (float) str_replace(',', '.', $b->getName());

            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
    }

    /**
     * @param Struct\Property\Option[] $options
     */
    private function sortOptionsAlphanumeric(&$options)
    {
        usort($options, function (Struct\Property\Option $a, Struct\Property\Option $b) {
            return strnatcasecmp($a->getName(), $b->getName());
        });
    }
}
