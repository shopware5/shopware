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

namespace Shopware\Models\Article\Configurator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\ConfiguratorGroup as ConfiguratorGroupAttribute;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_article_configurator_groups")
 */
class Group extends ModelEntity
{
    /**
     * @var ArrayCollection<Set>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Configurator\Set", mappedBy="groups")
     */
    protected $sets;

    /**
     * @var ArrayCollection<array-key, Option>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Configurator\Option", mappedBy="group", orphanRemoval=true, cascade={"persist"})
     */
    protected $options;

    /**
     * INVERSE SIDE
     *
     * @var ConfiguratorGroupAttribute|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ConfiguratorGroup", mappedBy="configuratorGroup", orphanRemoval=true, cascade={"persist"})
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
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

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
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
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
     * @return ArrayCollection<Set>
     */
    public function getSets()
    {
        return $this->sets;
    }

    /**
     * @param ArrayCollection<Set> $sets
     */
    public function setSets($sets)
    {
        $this->sets = $sets;
    }

    /**
     * @return ArrayCollection<array-key, Option>
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param ArrayCollection<array-key, Option> $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return ConfiguratorGroupAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param ConfiguratorGroupAttribute|array|null $attribute
     *
     * @return Group
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, ConfiguratorGroupAttribute::class, 'attribute', 'configuratorGroup');
    }
}
