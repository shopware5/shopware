<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
     * @param AttributeHydrator $attributeHydrator
     */
    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @param array $data
     * @return Struct\Property\Set[]
     */
    public function hydrateValues(array $data)
    {
        $sets = array();

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
            array('groupName' => '__propertySet_name')
        );
        $data = array_merge($data, $translation);

        $set->setId((int) $data['__propertySet_id']);
        $set->setName($data['__propertySet_name']);
        $set->setComparable((bool) $data['__propertySet_comparable']);

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
    private function hydrateGroup(array $data)
    {
        $group = new Struct\Property\Group();
        $translation = $this->getTranslation(
            $data,
            '__propertyGroup_translation',
            '__propertyGroup_translation_fallback',
            array('optionName' => '__propertyGroup_name')
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
    private function hydrateOption(array $data)
    {
        $option = new Struct\Property\Option();
        $translation = $this->getTranslation(
            $data,
            '__propertyOption_translation',
            '__propertyOption_translation_fallback',
            array('optionValue' => '__propertyOption_value')
        );
        $data = array_merge($data, $translation);

        $option->setId((int) $data['__propertyOption_id']);
        $option->setName($data['__propertyOption_value']);

        return $option;
    }

    /**
     * @param $data
     * @param $arrayKey
     * @param $fallbackArrayKey
     * @param $mapping
     * @return array
     */
    private function getTranslation($data, $arrayKey, $fallbackArrayKey, $mapping)
    {
        if (!isset($data[$arrayKey])
            || empty($data[$arrayKey])
        ) {
            $translation = array();
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
            return array();
        }

        return $this->convertArrayKeys($translation, $mapping);
    }

}
