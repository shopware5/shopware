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

namespace Shopware\Models\Price;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware Price Model
 *
 * @ORM\Table(name="s_core_pricegroups")
 * @ORM\Entity()
 */
class Group extends ModelEntity
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<Discount>
     *
     * @ORM\OneToMany(targetEntity="Discount", mappedBy="group", orphanRemoval=true, cascade={"all"})
     */
    protected $discounts;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=30, nullable=false)
     */
    private $name;

    public function __construct()
    {
        $this->discounts = new ArrayCollection();
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
     * @return Group
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
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Price\Discount>
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * @param Discount[]|null $discounts
     *
     * @return Group
     */
    public function setDiscounts($discounts)
    {
        return $this->setOneToMany($discounts, \Shopware\Models\Price\Discount::class, 'discounts', 'group');
    }
}
