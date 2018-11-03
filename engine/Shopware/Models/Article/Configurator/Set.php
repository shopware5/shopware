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
 * @ORM\Table(name="s_article_configurator_sets")
 */
class Set extends ModelEntity
{
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Configurator\Group", inversedBy="sets", cascade={"persist"})
     * @ORM\JoinTable(name="s_article_configurator_set_group_relations",
     *      joinColumns={
     *          @ORM\JoinColumn(name="set_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     *      }
     * )
     */
    protected $groups;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Configurator\Option", inversedBy="sets", cascade={"persist"})
     * @ORM\JoinTable(name="s_article_configurator_set_option_relations",
     *      joinColumns={
     *          @ORM\JoinColumn(name="set_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="option_id", referencedColumnName="id")
     *      }
     * )
     */
    protected $options;

    /**
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Article", mappedBy="configuratorSet")
     *
     * @var ArrayCollection
     */
    protected $articles;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Configurator\Dependency", mappedBy="configuratorSet", orphanRemoval=true, cascade={"persist"})
     */
    protected $dependencies;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Configurator\PriceVariation", mappedBy="configuratorSet", orphanRemoval=true, cascade={"persist"})
     */
    protected $priceVariations;
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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var int
     * @ORM\Column(name="public", type="boolean", nullable=false)
     */
    private $public = false;

    /**
     * @var int
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type = 0;

    /**
     * Class constructor, initials the array collections for the associations.
     */
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
     * @return int
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * @param int $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $groups
     *
     * @return \Shopware\Models\Article\Configurator\Set
     */
    public function setGroups($groups)
    {
        $this->setOneToMany($groups, '\Shopware\Models\Article\Configurator\Group', 'groups');

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $dependencies
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $options
     *
     * @return \Shopware\Models\Article\Configurator\Set
     */
    public function setOptions($options)
    {
        $this->setOneToMany($options, '\Shopware\Models\Article\Configurator\Option', 'options');

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
