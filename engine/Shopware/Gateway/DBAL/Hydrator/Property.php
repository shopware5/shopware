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


    private function hydrateSet(array $data)
    {
        $set = new Struct\Property\Set();
        $set->setId((int)$data['__propertySet_id']);
        $set->setName($data['__propertySet_name']);
        $set->setComparable((bool)$data['__propertySet_comparable']);

        if ($data['__propertySetAttribute_id']) {
            $attribute = $this->extractFields('__propertySetAttribute_', $data);
            $set->addAttribute('core', $this->attributeHydrator->hydrate($attribute));
        }

        return $set;
    }

    private function hydrateGroup(array $data)
    {
        $group = new Struct\Property\Group();
        $group->setId((int)$data['__propertyGroup_id']);
        $group->setName($data['__propertyGroup_name']);
        $group->setFilterable((bool)$data['__propertyGroup_filterable']);
        return $group;
    }

    private function hydrateOption(array $data)
    {
        $option = new Struct\Property\Option();
        $option->setId((int)$data['__propertyOption_id']);
        $option->setName($data['__propertyOption_value']);
        return $option;
    }
}