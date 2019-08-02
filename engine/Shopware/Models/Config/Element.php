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
 * @ORM\Table(name="s_core_config_elements")
 * @ORM\Entity()
 */
class Element extends ModelEntity
{
    const SCOPE_LOCALE = 0;
    const SCOPE_SHOP = 1;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<ElementTranslation>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Config\ElementTranslation", mappedBy="element", cascade={"all"})
     */
    protected $translations;

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
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="object", nullable=true)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", nullable=true)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=true)
     */
    private $type;

    /**
     * @var bool
     *
     * @ORM\Column(name="required", type="boolean")
     */
    private $required = false;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="scope", type="integer", nullable=false)
     */
    private $scope = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="form_id", type="integer", nullable=true)
     */
    private $formId = 0;

    /**
     * @var array
     *
     * @ORM\Column(name="options", type="array")
     */
    private $options;

    /**
     * @var Form
     *
     * @ORM\ManyToOne(targetEntity="Form", inversedBy="elements")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id")
     */
    private $form;

    /**
     * @var ArrayCollection<Value>
     *
     * @ORM\OneToMany(targetEntity="Value", mappedBy="element", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="element_id")
     */
    private $values;

    /**
     * @param string     $type
     * @param string     $name
     * @param array|null $options
     */
    public function __construct($type, $name, $options = null)
    {
        $this->type = $type;
        $this->name = $name;
        $this->setOptions($options);

        $this->translations = new ArrayCollection();
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
     * @return Element
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
     * @return Element
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $description
     *
     * @return Element
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $label
     *
     * @return Element
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
     * @param \Shopware\Models\Config\Form $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function setOptions(array $options)
    {
        $fields = ['label', 'value', 'description', 'required', 'scope', 'position'];
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
     * @param int $position
     *
     * @return Element
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
     * @param bool $required
     *
     * @return Element
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param int $scope
     *
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
     *
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
     * @param ArrayCollection<Value> $values
     *
     * @return Element
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return ArrayCollection<Value>
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return ArrayCollection<ElementTranslation>
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param ArrayCollection<ElementTranslation> $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * @param ElementTranslation $translation
     *
     * @return Element
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

    public function getFormId(): int
    {
        return $this->formId;
    }

    public function setFormId(int $formId): self
    {
        $this->formId = $formId;

        return $this;
    }
}
