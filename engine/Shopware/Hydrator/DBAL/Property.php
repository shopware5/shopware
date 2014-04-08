<?php

namespace Shopware\Hydrator\DBAL;
use Shopware\Struct as Struct;

class Property
{
    /**
     * @var Attribute
     */
    private $attributeHydrator;

    function __construct(Attribute $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }


    public function hydrate(array $data)
    {
        $set = new Struct\PropertySet();

        $set->setId(intval($data['id']));

        $set->setName($data['name']);

        $set->setComparable(boolval($data['comparable']));

        if (isset($data['attribute'])) {
            $set->addAttribute(
                'core',
                $this->attributeHydrator->hydrate($data['attribute'])
            );
        }

        if (isset($data['options'])) {
            $groups = array();

            foreach($data['options'] as $optionData) {
                $key = 'group_' . $optionData['option_id'];

                $group = $groups[$key];

                if (!$group) {
                    $group = new Struct\PropertyGroup();

                    $group->setId(intval($optionData['option_id']));

                    $group->setName($optionData['option_name']);

                    $groups[$key] = $group;
                }

                $options = $group->getOptions();

                $option = new Struct\PropertyOption();

                $option->setId($optionData['value_id']);

                $option->setName($optionData['value']);

                $options[] = $option;

                $group->setOptions($options);
            }
            $set->setGroups(array_values($groups));
        }

        return $set;
    }
}