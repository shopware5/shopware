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

namespace Shopware\Models\Price;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware Price Model
 *
 * @ORM\Table(name="s_core_pricegroups")
 * @ORM\Entity
 */
class Group extends ModelEntity
{
    /**
     * @ORM\OneToMany(targetEntity="Discount", mappedBy="group", orphanRemoval=true, cascade={"all"})
     *
     * @var Discount[]|\Doctrine\Common\Collections\ArrayCollection
     */
    protected $discounts;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=30, nullable=false)
     */
    private $name;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->discounts = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Discount[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * @param Discount[]|\Doctrine\Common\Collections\ArrayCollection $discounts
     *
     * @return Group
     */
    public function setDiscounts($discounts)
    {
        return $this->setOneToMany($discounts, '\Shopware\Models\Price\Discount', 'discounts', 'group');
    }
}
