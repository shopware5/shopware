<?php

namespace Shopware\Hydrator\ORM;

use Shopware\Struct as Struct;

class Property
{
    /**
     * @param $data
     * @return \Shopware\Struct\PropertySet
     */
    public function hydrateSet($data)
    {
        $set = new Struct\PropertySet();

        $this->assignSetData($set, $data);

        return $set;
    }

    public function hydrateGroup(array $data)
    {
        $group = new Struct\PropertyGroup();

        $this->assignGroupData($group, $data);

        return $group;
    }

    public function hydrateOption(array $data)
    {
        $option = new Struct\PropertyOption();

        $this->assignOptionData($option, $data);

        return $option;
    }

    public function assignSetData(Struct\PropertySet $set, array $data)
    {
        if (isset($data['id'])) {
            $set->setId($data['id']);
        }

        if (isset($data['name'])) {
            $set->setName($data['name']);
        }

        if (isset($data['options'])) {
            $groups = array();
            foreach ($data['options'] as $groupData) {
                $groups[] = $this->hydrateGroup($groupData);
            }
            $set->setGroups($groups);
        }
    }

    public function assignGroupData(Struct\PropertyGroup $group, array $data)
    {
        if (isset($data['id'])) {
            $group->setId($data['id']);
        }

        if (isset($data['name'])) {
            $group->setName($data['name']);
        }

        if (isset($data['values'])) {
            $options = array();
            foreach ($data['values'] as $optionData) {
                $options[] = $this->hydrateOption($optionData);
            }
            $group->setOptions($options);
        }
    }

    public function assignOptionData(Struct\PropertyOption $option, array $data)
    {
        if (isset($data['id'])) {
            $option->setId($data['id']);
        }

        if (isset($data['name'])) {
            $option->setName($data['name']);
        }
    }


}