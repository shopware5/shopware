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

namespace Shopware\Models\Article\Configurator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_article_configurator_options")
 */
class Option extends ModelEntity
{
    /**
     * @var ArrayCollection<\Shopware\Models\Article\Detail>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Detail", mappedBy="configuratorOptions")
     * @ORM\JoinTable(name="s_article_configurator_option_relations",
     *      joinColumns={
     *          @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="option_id", referencedColumnName="id")
     *      }
     * )
     */
    protected $articles;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Configurator\Set>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Configurator\Set", mappedBy="options")
     */
    protected $sets;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Attribute\ConfiguratorOption
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ConfiguratorOption", mappedBy="configuratorOption", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="group_id", type="integer", nullable=true)
     */
    private $groupId;

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
     * @var \Shopware\Models\Article\Configurator\Group
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Group", inversedBy="options")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $group;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Configurator\Dependency>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Configurator\Dependency", mappedBy="parentOption", orphanRemoval=true)
     */
    private $dependencyParents;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Configurator\Dependency>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Configurator\Dependency", mappedBy="childOption", orphanRemoval=true)
     */
    private $dependencyChildren;

    /**
     * @var int
     *
     * @ORM\Column(name="media_id", type="integer", nullable=true)
     */
    private $mediaId;

    public function __construct()
    {
        $this->dependencyChildren = new ArrayCollection();
        $this->dependencyParents = new ArrayCollection();
    }

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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return \Shopware\Models\Article\Configurator\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param \Shopware\Models\Article\Configurator\Group $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Configurator\Dependency>
     */
    public function getDependencyParents()
    {
        return $this->dependencyParents;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Configurator\Dependency> $dependencyParents
     */
    public function setDependencyParents($dependencyParents)
    {
        $this->dependencyParents = $dependencyParents;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Configurator\Dependency>
     */
    public function getDependencyChildren()
    {
        return $this->dependencyChildren;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Configurator\Dependency> $dependencyChildren
     */
    public function setDependencyChildren($dependencyChildren)
    {
        $this->dependencyChildren = $dependencyChildren;
    }

    /**
     * @return \Shopware\Models\Attribute\ConfiguratorOption
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\ConfiguratorOption|array|null $attribute
     *
     * @return Option
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, \Shopware\Models\Attribute\ConfiguratorOption::class, 'attribute', 'configuratorOption');
    }

    /**
     * @return int
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * @param int $mediaId
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;
    }
}
