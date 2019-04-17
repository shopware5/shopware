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

namespace Shopware\Models\Tax;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The Shopware Model represents the Taxes.
 * <br>
 * Tax codes and there percentages
 *
 * Relations and Associations
 * <code>
 *
 * </code>
 * The s_media_album table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 * </code>
 *
 * @ORM\Table(name="s_core_tax")
 * @ORM\Entity(repositoryClass="Repository")
 */
class Tax extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="tax", type="decimal", nullable=false)
     */
    private $tax;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $name;

    /**
     * The rules property is the inverse side of the association between a tax rule and tax group.
     * The association is joined over the id field and the groupID field of the tax rule.
     *
     * @var ArrayCollection<Rule>
     *
     * @ORM\OneToMany(targetEntity="Rule", mappedBy="group", orphanRemoval=true, cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="groupID")
     */
    private $rules;

    public function __construct()
    {
        $this->rules = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param float $tax
     *
     * @return Tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param string $name
     *
     * @return Tax
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
     * @return ArrayCollection<Rule>
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param ArrayCollection<Rule> $rules
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
    }
}
