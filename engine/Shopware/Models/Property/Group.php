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
use Shopware\Models\Article\Article;
use Shopware\Models\Attribute\PropertyGroup as PropertyGroupAttribute;

/**
 * Shopware Article Property Model
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_filter")
 */
class Group extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @var PropertyGroupAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\PropertyGroup", mappedBy="propertyGroup", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="comparable", type="boolean")
     */
    private $comparable;

    /**
     * @var int
     *
     * @ORM\Column(name="sortMode", type="integer", nullable=false)
     */
    private $sortMode;

    /**
     * @var ArrayCollection<Option>
     *
     * @ORM\ManyToMany(targetEntity="Option")
     * @ORM\JoinTable(name="s_filter_relations",
     *     joinColumns={@ORM\JoinColumn(name="groupID", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="optionID", referencedColumnName="id")}
     * )
     */
    private $options;

    /**
     * @var ArrayCollection<Relation>
     *
     * @ORM\OneToMany(targetEntity="Relation", mappedBy="group")
     */
    private $relations;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Article>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Article", mappedBy="propertyGroup", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="id", referencedColumnName="filtergroupID")
     */
    private $articles;

    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->articles = new ArrayCollection();
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
     * @param int $position
     *
     * @return Group
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $comparable
     *
     * @return Group
     */
    public function setComparable($comparable)
    {
        $this->comparable = $comparable;

        return $this;
    }

    /**
     * @return int
     */
    public function getComparable()
    {
        return $this->comparable;
    }

    /**
     * Set sort mode
     *
     * @param int $sortMode
     *
     * @return Group
     */
    public function setSortMode($sortMode)
    {
        $this->sortMode = $sortMode;

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
     * Returns Array of associated Options
     *
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->options->toArray();
    }

    /**
     * @return Group
     */
    public function removeOption(Option $option)
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * @return Group
     */
    public function addOption(Option $option)
    {
        $this->options->add($option);

        return $this;
    }

    /**
     * @param Option[] $options
     *
     * @return Group
     */
    public function setOptions(array $options)
    {
        $this->options->clear();

        foreach ($options as $option) {
            $this->addOption($option);
        }

        return $this;
    }

    /**
     * Return array of associated Articles
     *
     * @return Article[]
     */
    public function getArticles()
    {
        return $this->articles->toArray();
    }

    /**
     * @return PropertyGroupAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param PropertyGroupAttribute|array|null $attribute
     *
     * @return Group
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, PropertyGroupAttribute::class, 'attribute', 'propertyGroup');
    }
}
