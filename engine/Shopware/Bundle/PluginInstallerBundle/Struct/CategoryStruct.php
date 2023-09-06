<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\PluginInstallerBundle\Struct;

use JsonSerializable;
use Shopware\Components\ObjectJsonSerializeTraitDeprecated;

class CategoryStruct implements JsonSerializable
{
    use ObjectJsonSerializeTraitDeprecated;

    /**
     * @var int
     */
    private $id;

    /**
     * @var array|string
     */
    private $name;

    /**
     * @var int
     */
    private $parentId;

    /**
     * @var CategoryStruct[]
     */
    private $children = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return CategoryStruct[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param CategoryStruct[] $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }
}
