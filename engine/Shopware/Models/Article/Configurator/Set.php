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
 * @ORM\Entity()
 * @ORM\Table(name="s_article_configurator_sets")
 */
class Set extends ModelEntity
{
    /**
     * @var ArrayCollection<\Shopware\Models\Article\Configurator\Group>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Configurator\Group", inversedBy="sets", cascade={"persist"})
     * @ORM\JoinTable(name="s_article_configurator_set_group_relations",
     *     joinColumns={
     *         @ORM\JoinColumn(name="set_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $groups;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Configurator\Option>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Configurator\Option", inversedBy="sets", cascade={"persist"})
     * @ORM\JoinTable(name="s_article_configurator_set_option_relations",
     *     joinColumns={
     *         @ORM\JoinColumn(name="set_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="option_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $options;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Article>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Article", mappedBy="configuratorSet")
     */
    protected $articles;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Configurator\Dependency>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Configurator\Dependency", mappedBy="configuratorSet", orphanRemoval=true, cascade={"persist"})
     */
    protected $dependencies;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Configurator\PriceVariation>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Configurator\PriceVariation", mappedBy="configuratorSet", orphanRemoval=true, cascade={"persist"})
     */
    protected $priceVariations;

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
     * @var bool
     *
     * @ORM\Column(name="public", type="boolean", nullable=false)
     */
    private $public = false;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type = 0;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->dependencies = new ArrayCollection();
        $this->priceVariations = new ArrayCollection();
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
     * @return bool
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * @param bool $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Article\Configurator\Group>|null
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param Group[] $groups
     *
     * @return \Shopware\Models\Article\Configurator\Set
     */
    public function setGroups($groups)
    {
        $this->setOneToMany($groups, Group::class, 'groups');

        return $this;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Article\Article>
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Article\Article> $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Article\Configurator\Dependency>
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Article\Configurator\Dependency> $dependencies
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Article\Configurator\Option>|null
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param Option[] $options
     *
     * @return \Shopware\Models\Article\Configurator\Set
     */
    public function setOptions($options)
    {
        $this->setOneToMany($options, Option::class, 'options');

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
