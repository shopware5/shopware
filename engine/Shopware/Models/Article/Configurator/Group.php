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

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_article_configurator_groups")
 */
class Group extends ModelEntity
{
    /**
     * @var integer $id
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
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description = null;

    /**
     * @var integer
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * @var \Shopware\Models\Article\Configurator\Set
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Configurator\Set", mappedBy="groups")
     */
    protected $sets;

    /**
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Configurator\Option", mappedBy="group", orphanRemoval=true, cascade={"persist"})
     * @var ArrayCollection
     */
    protected $options;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ConfiguratorGroup", mappedBy="configuratorGroup", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Attribute\ConfiguratorGroup
     */
    protected $attribute;

    /**
     * Class constructor, initials the array collections for the associations.
     */
    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->sets = new ArrayCollection();
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * @return \Shopware\Models\Article\Configurator\Set
     */
    public function getSets()
    {
        return $this->sets;
    }

    /**
     * @param \Shopware\Models\Article\Configurator\Set $sets
     */
    public function setSets($sets)
    {
        $this->sets = $sets;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $options
     * @return Group
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return \Shopware\Models\Attribute\ConfiguratorGroup
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\ConfiguratorGroup|array|null $attribute
     * @return \Shopware\Models\Attribute\ConfiguratorGroup
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\ConfiguratorGroup', 'attribute', 'configuratorGroup');
    }
}
