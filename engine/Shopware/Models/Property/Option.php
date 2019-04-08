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

namespace Shopware\Models\Property;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\PropertyOption as PropertyOptionAttribute;

/**
 * Shopware Article Model
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_filter_options")
 */
class Option extends ModelEntity
{
    /**
     * @var ArrayCollection<Value>
     *
     * @ORM\OneToMany(targetEntity="Value", mappedBy="option", cascade={"remove"}))
     */
    protected $values;

    /**
     * INVERSE SIDE
     *
     * @var PropertyOptionAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\PropertyOption", mappedBy="propertyOption", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="filterable", type="boolean")
     */
    private $filterable;

    /**
     * ManyToMany to Group (Inverse Side)
     *
     * @var ArrayCollection<Group>
     *
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable(name="s_filter_relations",
     *     joinColumns={@ORM\JoinColumn(name="optionID", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="groupID", referencedColumnName="id")}
     * )
     */
    private $groups;

    /**
     * @var ArrayCollection<Relation>
     *
     * @ORM\OneToMany(targetEntity="Relation", mappedBy="option")
     */
    private $relations;

    /**
     * Constructor of Mail
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->values = new ArrayCollection();
        $this->relations = new ArrayCollection();
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
     * @return Option
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
     * @param bool $filterable
     *
     * @return Option
     */
    public function setFilterable($filterable)
    {
        $this->filterable = (bool) $filterable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFilterable()
    {
        return $this->filterable;
    }

    /**
     * @return ArrayCollection<Value>
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Property\Group>
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return ArrayCollection<Relation>
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @return PropertyOptionAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param PropertyOptionAttribute|array|null $attribute
     *
     * @return Option
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, PropertyOptionAttribute::class, 'attribute', 'propertyOption');
    }
}
