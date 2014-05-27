<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct;

class Configurator extends Hydrator
{
    /**
     * @var Attribute
     */
    private $attributeHydrator;

    /**
     * @param Attribute $attributeHydrator
     */
    function __construct(Attribute $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }


    public function hydrate(array $data, array $selection = array())
    {
        $set = new Struct\Configurator\Set();
        $setData = $this->extractFields('__set_', $data[0]);

        $set->setName($setData['name']);
        $set->setId($setData['id']);
        $set->setType($setData['type']);

        $set->setGroups(
            $this->hydrateGroups($data, $selection)
        );

        return $set;

    }

    /**
     * @param array $data
     * @param array $selection
     * @return Struct\Configurator\Group[]
     */
    public function hydrateGroups(array $data, array $selection = array())
    {
        $groups = array();

        foreach ($data as $row) {
            $groupId = $row['__group_id'];

            if ($groups[$groupId]) {
                $group = $groups[$groupId];
            } else {
                $group = $this->createGroup(
                    $this->extractFields('__group_', $row)
                );

                $group->setSelected(isset($selection[$groupId]));

                $groups[$groupId] = $group;
            }

            $option = $this->createOption(
                $this->extractFields('__option_', $row)
            );

            $option->setSelected(in_array($option->getId(), $selection));

            $group->addOption($option);
        }
        return array_values($groups);
    }


    private function createSet($data)
    {
        $set = new Struct\Configurator\Set();
        $set->setId((int)$data['id']);
        $set->setName($data['name']);
        $set->setType($data['type']);
        return $set;
    }

    private function createGroup($data)
    {
        $group = new Struct\Configurator\Group();
        $group->setId((int)$data['id']);
        $group->setName($data['name']);
        $group->setDescription($data['description']);
        return $group;
    }

    private function createOption($data)
    {
        $option = new Struct\Configurator\Option();
        $option->setId((int)$data['id']);
        $option->setName($data['name']);
        return $option;
    }
}