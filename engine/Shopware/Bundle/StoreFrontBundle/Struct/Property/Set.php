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

namespace Shopware\Bundle\StoreFrontBundle\Struct\Property;

use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class Set extends Extendable
{
    /**
     * Constant for the alphanumeric sort configuration of the category filters
     */
    const SORT_ALPHANUMERIC = 0;

    /**
     * Constant for the numeric sort configuration of the category filters
     */
    const SORT_NUMERIC = 1;

    /**
     * Constant for the position sort configuration of the category filters
     */
    const SORT_POSITION = 3;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $comparable;

    /**
     * @var Group[]
     */
    protected $groups = [];

    /**
     * @var int
     */
    protected $sortMode;

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Property\Group[] $groups
     *
     * @return $this
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Property\Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param bool $comparable
     *
     * @return $this
     */
    public function setComparable($comparable)
    {
        $this->comparable = $comparable;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortMode()
    {
        return $this->sortMode;
    }

    /**
     * @param int $sortMode
     */
    public function setSortMode($sortMode)
    {
        $this->sortMode = $sortMode;
    }

    /**
     * @return bool
     */
    public function isComparable()
    {
        return $this->comparable;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
