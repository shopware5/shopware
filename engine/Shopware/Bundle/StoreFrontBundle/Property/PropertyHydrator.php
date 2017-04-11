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

namespace Shopware\Bundle\StoreFrontBundle\Property;

use Shopware\Bundle\StoreFrontBundle\Common\AttributeHydrator;
use Shopware\Bundle\StoreFrontBundle\Common\Hydrator;
use Shopware\Bundle\StoreFrontBundle\Media\MediaHydrator;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
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

    /**
     * @param AttributeHydrator $attributeHydrator
     * @param MediaHydrator     $mediaHydrator
     */
    public function __construct(
        AttributeHydrator $attributeHydrator,
        MediaHydrator $mediaHydrator
    ) {
        $this->attributeHydrator = $attributeHydrator;
        $this->mediaHydrator = $mediaHydrator;
    }

    /**
     * @param array $data
     *
     * @return PropertySet[]
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

        /** @var PropertySet[] $sets */
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
     * @param array $data
     *
     * @return PropertyGroup
     */
    public function hydrateGroup(array $data)
    {
        $group = new PropertyGroup();
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
     * @param array $data
     *
     * @return PropertyOption
     */
    public function hydrateOption(array $data)
    {
        $option = new PropertyOption();
        $translation = $this->getTranslation($data, '__propertyOption', ['optionValue' => 'value']);
        $data = array_merge($data, $translation);

        $option->setId((int) $data['__propertyOption_id']);
        $option->setName($data['__propertyOption_value']);
        $option->setPosition((int) $data['__propertyOption_position']);

        if ($data['__propertyOptionAttribute_id']) {
            $this->attributeHydrator->addAttribute($option, $data, 'propertyOptionAttribute');
        }

        if (isset($data['__media_id']) && $data['__media_id']) {
            $option->setMedia(
                $this->mediaHydrator->hydrate($data)
            );
        }

        return $option;
    }

    /**
     * @param array $data
     *
     * @return PropertySet
     */
    private function hydrateSet(array $data)
    {
        $set = new PropertySet();
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
     * @param array $data
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
     * @param $options PropertyOption[]
     * @param int $sortMode
     */
    private function sortOptions(&$options, $sortMode)
    {
        if ($sortMode == PropertySet::SORT_POSITION) {
            $this->sortOptionsByPosition($options);

            return;
        }

        if ($sortMode == PropertySet::SORT_NUMERIC) {
            $this->sortOptionsNumercialValue($options);

            return;
        }

        $this->sortOptionsAlphanumeric($options);
    }

    /**
     * @param $options PropertyOption[]
     */
    private function sortOptionsByPosition(&$options)
    {
        usort($options, function (
            PropertyOption $a, PropertyOption $b) {
            if ($a->getPosition() == $b->getPosition()) {
                return 0;
            }

            return ($a->getPosition() < $b->getPosition()) ? -1 : 1;
        });
    }

    /**
     * @param $options PropertyOption[]
     */
    private function sortOptionsNumercialValue(&$options)
    {
        usort($options, function (
            PropertyOption $a, PropertyOption $b) {
            $a = floatval(str_replace(',', '.', $a->getName()));
            $b = floatval(str_replace(',', '.', $b->getName()));

            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
    }

    /**
     * @param $options PropertyOption[]
     */
    private function sortOptionsAlphanumeric(&$options)
    {
        usort($options, function (
            PropertyOption $a, PropertyOption $b) {
            return strnatcasecmp($a->getName(), $b->getName());
        });
    }
}
