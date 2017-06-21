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

namespace Shopware\Models\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_core_config_forms")
 * @ORM\Entity
 */
class Form extends ModelEntity
{
    /**
     * @var \Shopware\Models\Plugin\Plugin
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Plugin\Plugin", inversedBy="configForms")
     * @ORM\JoinColumn(name="plugin_id", referencedColumnName="id")
     */
    protected $plugin;

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Config\FormTranslation", mappedBy="form", cascade={"all"})
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * @var Form
     * @ORM\ManyToOne(targetEntity="Form", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", nullable=true, referencedColumnName="id", onDelete="SET NULL")
     */
    private $parent;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="label", type="string", nullable=true)
     */
    private $label;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var int
     * @ORM\Column(name="plugin_id", type="integer", nullable=false)
     */
    private $pluginId;

    /**
     * @var int
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * @var Element[]
     * @ORM\OneToMany(targetEntity="Element", mappedBy="form", cascade={"all"})
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    private $elements;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Form", mappedBy="parent", cascade={"all"}))
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $children;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->elements = new ArrayCollection();
        $this->translations = new ArrayCollection();
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
     * @return Form
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
     * @param $label
     *
     * @return Form
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return string
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * @param string $name
     *
     * @return Element
     */
    public function getElement($name)
    {
        /* @var $value Element */
        foreach ($this->elements as $element) {
            if ($element->getName() === $name) {
                return $element;
            }
        }

        return null;
    }

    /**
     * @param string $type
     * @param string $name
     * @param array  $options
     *
     * @return Form
     */
    public function setElement($type, $name, $options = null)
    {
        /* @var $value Element */
        foreach ($this->elements as $element) {
            if ($element->getName() === $name) {
                $element->setType($type);
                if ($options !== null) {
                    $element->setOptions($options);
                }

                return $this;
            }
        }
        $this->addElement($type, $name, $options);

        return $this;
    }

    /**
     * @param string|Element $element
     *
     * @return Form
     */
    public function removeElement($element)
    {
        if (!$element instanceof Element) {
            $element = $this->getElement($element);
        }
        if ($element !== null) {
            $this->elements->removeElement($element);
        }

        return $this;
    }

    /**
     * @param Element|string $element
     * @param string         $name
     * @param array          $options
     *
     * @return \Shopware\Models\Config\Form
     */
    public function addElement($element, $name = null, $options = null)
    {
        if (!$element instanceof Element) {
            $element = new Element(
                $element, $name, $options
            );
        }
        $element->setForm($this);
        $this->elements->add($element);

        return $this;
    }

    /**
     * @return ArrayCollection|Element[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @return bool
     */
    public function hasElements()
    {
        return $this->elements->count() > 0;
    }

    /**
     * @deprecated
     *
     * @return Form
     */
    public function save()
    {
        return $this;
    }

    /**
     * @param int $pluginId
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = $pluginId;
    }

    /**
     * @return int
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * @return Form
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Form $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ArrayCollection $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param $translation FormTranslation
     *
     * @return \Shopware\Models\Config\Form
     */
    public function addTranslation($translation)
    {
        $this->translations->add($translation);
        $translation->setForm($this);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasTranslations()
    {
        return $this->translations->count() > 0;
    }
}
