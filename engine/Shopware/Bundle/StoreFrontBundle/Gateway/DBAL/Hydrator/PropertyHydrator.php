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

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator
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
     * @param MediaHydrator $mediaHydrator
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
     * @param array $data
     * @return Struct\Property\Set
     */
    private function hydrateSet(array $data)
    {
        $set = new Struct\Property\Set();
        $translation = $this->getTranslation(
            $data,
            '__propertySet_translation',
            '__propertySet_translation_fallback',
            ['groupName' => '__propertySet_name']
        );
        $data = array_merge($data, $translation);

        $set->setId((int) $data['__propertySet_id']);
        $set->setName($data['__propertySet_name']);
        $set->setComparable((bool) $data['__propertySet_comparable']);
        $set->setSortMode((int) $data['__propertySet_sortmode']);

        if ($data['__propertySetAttribute_id']) {
            $attribute = $this->extractFields('__propertySetAttribute_', $data);
            $set->addAttribute('core', $this->attributeHydrator->hydrate($attribute));
        }

        return $set;
    }

    /**
     * @param array $data
     * @return Struct\Property\Group
     */
    public function hydrateGroup(array $data)
    {
        $group = new Struct\Property\Group();
        $translation = $this->getTranslation(
            $data,
            '__propertyGroup_translation',
            '__propertyGroup_translation_fallback',
            ['optionName' => '__propertyGroup_name']
        );
        $data = array_merge($data, $translation);

        $group->setId((int) $data['__propertyGroup_id']);
        $group->setName($data['__propertyGroup_name']);
        $group->setFilterable((bool) $data['__propertyGroup_filterable']);

        return $group;
    }

    /**
     * @param array $data
     * @return Struct\Property\Option
     */
    public function hydrateOption(array $data)
    {
        $option = new Struct\Property\Option();
        $translation = $this->getTranslation(
            $data,
            '__propertyOption_translation',
            '__propertyOption_translation_fallback',
            ['optionValue' => '__propertyOption_value']
        );
        $data = array_merge($data, $translation);

        $option->setId((int) $data['__propertyOption_id']);
        $option->setName($data['__propertyOption_value']);
        $option->setPosition((int) $data['__propertyOption_position']);

        if (isset($data['__media_id']) && $data['__media_id']) {
            $option->setMedia(
                $this->mediaHydrator->hydrate($data)
            );
        }

        return $option;
    }

    /**
     * @param array $data
     * @param string $arrayKey
     * @param string $fallbackArrayKey
     * @param array $mapping
     * @return array
     */
    private function getTranslation(array $data, $arrayKey, $fallbackArrayKey, array $mapping)
    {
        if (!isset($data[$arrayKey])
            || empty($data[$arrayKey])
        ) {
            $translation = [];
        } else {
            $translation = unserialize($data[$arrayKey]);
        }

        if (isset($data[$fallbackArrayKey])
            && !empty($data[$fallbackArrayKey])
        ) {
            $fallbackTranslation = unserialize($data[$fallbackArrayKey]);
            $translation += $fallbackTranslation;
        }

        if (empty($translation)) {
            return [];
        }

        return $this->convertArrayKeys($translation, $mapping);
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
     * @param $options Struct\Property\Option[]
     * @param int $sortMode
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
     * @param $options Struct\Property\Option[]
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
     * @param $options Struct\Property\Option[]
     */
    private function sortOptionsNumercialValue(&$options)
    {
        usort($options, function (Struct\Property\Option $a, Struct\Property\Option $b) {
            $a = floatval(str_replace(',', '.', $a->getName()));
            $b = floatval(str_replace(',', '.', $b->getName()));

            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
    }

    /**
     * @param $options Struct\Property\Option[]
     */
    private function sortOptionsAlphanumeric(&$options)
    {
        usort($options, function (Struct\Property\Option $a, Struct\Property\Option $b) {
            return strnatcasecmp($a->getName(), $b->getName());
        });
    }
}
