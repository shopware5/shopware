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

namespace Shopware\Models\Search;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_search_custom_facet")
 * @ORM\Entity()
 */
class CustomFacet extends ModelEntity
{
    /**
     * @var string
     *
     * @ORM\Column(nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="unique_key", nullable=true)
     */
    protected $uniqueKey;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $deletable;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\Column(nullable=false)
     */
    protected $facet;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_in_categories", type="boolean")
     */
    protected $displayInCategories;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getFacet()
    {
        return $this->facet;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param string $facet
     */
    public function setFacet($facet)
    {
        $this->facet = $facet;
    }

    /**
     * @return bool
     */
    public function isDisplayInCategories()
    {
        return $this->displayInCategories;
    }

    /**
     * @param bool $displayInCategories
     */
    public function setDisplayInCategories($displayInCategories)
    {
        $this->displayInCategories = $displayInCategories;
    }

    /**
     * @return bool
     */
    public function isDeletable()
    {
        return $this->deletable;
    }

    /**
     * @param bool $deletable
     */
    public function setDeletable($deletable)
    {
        $this->deletable = $deletable;
    }

    /**
     * @return string
     */
    public function getUniqueKey()
    {
        return $this->uniqueKey;
    }

    /**
     * @param string $uniqueKey
     */
    public function setUniqueKey($uniqueKey)
    {
        $this->uniqueKey = $uniqueKey;
    }
}
