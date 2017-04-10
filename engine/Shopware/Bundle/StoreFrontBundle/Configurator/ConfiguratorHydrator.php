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

namespace Shopware\Bundle\StoreFrontBundle\Configurator;

use Shopware\Bundle\StoreFrontBundle\Common\AttributeHydrator;
use Shopware\Bundle\StoreFrontBundle\Common\Hydrator;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ConfiguratorHydrator extends Hydrator
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
     *
     * @return Set
     */
    public function hydrate(array $data)
    {
        $set = $this->createSet($data[0]);
        $set->setGroups($this->hydrateGroups($data));

        return $set;
    }

    /**
     * @param array $data
     *
     * @return Group[]
     */
    public function hydrateGroups(array $data)
    {
        $groups = [];

        foreach ($data as $row) {
            $groupId = $row['__configuratorGroup_id'];

            if (isset($groups[$groupId])) {
                $group = $groups[$groupId];
            } else {
                $group = $this->createGroup($row);
                $groups[$groupId] = $group;
            }

            $option = $this->createOption($row);

            $group->addOption($option);
        }

        return array_values($groups);
    }

    /**
     * @param $data
     *
     * @return Set
     */
    private function createSet($data)
    {
        $set = new Set();
        $set->setId((int) $data['__configuratorSet_id']);
        $set->setName($data['__configuratorSet_name']);
        $set->setType((int) $data['__configuratorSet_type']);

        return $set;
    }

    /**
     * @param array $data
     *
     * @return Group
     */
    private function createGroup($data)
    {
        $group = new Group();
        $translation = $this->getTranslation($data, '__configuratorGroup');
        $data = array_merge($data, $translation);

        $group->setId((int) $data['__configuratorGroup_id']);
        $group->setName($data['__configuratorGroup_name']);
        $group->setDescription($data['__configuratorGroup_description']);

        if ($data['__configuratorGroupAttribute_id']) {
            $this->attributeHydrator->addAttribute($group, $data, 'configuratorGroupAttribute', null, 'configuratorGroup');
        }

        return $group;
    }

    /**
     * @param array $data
     *
     * @return Option
     */
    private function createOption($data)
    {
        $option = new Option();

        $translation = $this->getTranslation($data, '__configuratorOption');
        $data = array_merge($data, $translation);

        $option->setId((int) $data['__configuratorOption_id']);
        $option->setName($data['__configuratorOption_name']);

        if ($data['__configuratorOptionAttribute_id']) {
            $this->attributeHydrator->addAttribute($option, $data, 'configuratorOptionAttribute', null, 'configuratorOption');
        }

        return $option;
    }
}
