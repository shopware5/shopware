<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class Property extends Hydrator
{
    /**
     * @var Attribute
     */
    private $attributeHydrator;

    function __construct(Attribute $attributeHydrator)
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

        foreach($data as $row) {
            $setId = $row['id'];
            $groupId = $row['__groups_id'];
            $optionId = $row['__options_id'];

            $set = $row;
            if ($sets[$setId]) {
                $set = $sets[$setId];
            }

            if ($set['groups'][$groupId]) {
                $group = $set['groups'][$groupId];
            } else {
                $group = $this->extractFields('__groups_', $row);
            }

            $group['options'][$optionId] = $this->extractFields('__options_', $row);

            $set['groups'][$groupId] = $group;
            $sets[$setId] = $set;
        }

        $structs = array();
        foreach($sets as $setData) {
            $set = $this->hydrateSet($setData);

            $groups = array();
            foreach($setData['groups'] as $groupData) {
                $group = $this->hydrateGroup($groupData);

                $options = array();
                foreach($groupData['options'] as $optionData) {
                    $option = $this->hydrateOption($optionData);
                    $options[$option->getId()] = $option;
                }

                $group->setOptions($options);

                $groups[$group->getId()] = $group;
            }
            $set->setGroups($groups);

            $structs[$set->getId()] = $set;
        }

        return $structs;
    }

    private function hydrateSet(array $data)
    {
        $set = new Struct\Property\Set();
        $set->setId((int)$data['id']);
        $set->setName($data['name']);
        $set->setComparable((bool)$data['comparable']);
        return $set;
    }

    private function hydrateGroup(array $data)
    {
        $group = new Struct\Property\Group();
        $group->setId((int)$data['id']);
        $group->setName($data['name']);
        $group->setFilterable((bool)$data['filterable']);
        return $group;
    }

    private function hydrateOption(array $data)
    {
        $option = new Struct\Property\Option();
        $option->setId((int)$data['id']);
        $option->setName($data['value']);
        return $option;
    }
}