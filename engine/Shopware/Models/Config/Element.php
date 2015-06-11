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

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @ORM\Table(name="s_core_config_elements")
 * @ORM\Entity
 */
class Element extends ModelEntity
{
    const SCOPE_SHOP = 1;
    const SCOPE_LOCALE = 2;

    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string $value
     * @ORM\Column(name="value", type="object", nullable=true)
     */
    private $value;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

    /**
     * @var string $label
     * @ORM\Column(name="label", type="string", nullable=true)
     */
    private $label;

    /**
     * @var string $type
     * @ORM\Column(name="type", type="string", nullable=true)
     */
    private $type;

    /**
     * @var boolean $required
     * @ORM\Column(name="required", type="boolean")
     */
    private $required = false;

    /**
     * @var string $position
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * @var integer $scope
     * @ORM\Column(name="scope", type="integer", nullable=false)
     */
    private $scope = 0;

    /**
     * @var array $options
     * @ORM\Column(name="options", type="array")
     */
    private $options;

    /**
     * @var Form $form
     * @ORM\ManyToOne(targetEntity="Form", inversedBy="elements")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id")
     */
    private $form;

    /**
     * @var Value[] $values
     * @ORM\OneToMany(targetEntity="Value", mappedBy="element", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="element_id")
     */
    private $values;


    /**
     * INVERSE SIDE
     * @ORM\OneToMany(targetEntity="Shopware\Models\Config\ElementTranslation", mappedBy="element", cascade={"all"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * Class constructor.
     *
     * @param $type
     * @param $name
     * @param array $options
     */
    public function __construct($type, $name, $options = null)
    {
        $this->type = $type;
        $this->name = $name;
        $this->setOptions($options);

        $this->translations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Element
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
     * Set value
     *
     * @param mixed $value
     * @return Element
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Element
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
     * Set label
     *
     * @param string $label
     * @return Element
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param \Shopware\Models\Config\Form $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * @return \Shopware\Models\Config\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $fields = array('label', 'value', 'description', 'required', 'scope', 'position');
        foreach ($fields as $field) {
            if (array_key_exists($field, $options)) {
                $method = 'set' . ucfirst($field);
                $this->$method($options[$field]);
                unset($options[$field]);
            }
        }
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $position
     * @return Element
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param boolean $required
     * @return Element
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param int $scope
     * @return Element
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @return int
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $type
     * @return Element
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Value[] $values
     * @return Element
     */
    public function setValues($values)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @return Value[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * @param \Shopware\Models\Config\ElementTranslation $translation
     * @return \Shopware\Models\Config\Element
     */
    public function addTranslation($translation)
    {
        $this->translations->add($translation);
        $translation->setElement($this);
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
