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

namespace Shopware\Models\Emotion\Library;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_library_component_field")
 */
class Field extends ModelEntity
{
    /**
     * Unique identifier field of the element model.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Id of the associated \Shopware\Models\Library\Component
     * which will be displayed in the shopware backend component library.
     *
     * @var int
     *
     * @ORM\Column(name="componentID", type="integer", nullable=false)
     */
    private $componentId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="field_label", type="string", length=255, nullable=false)
     */
    private $fieldLabel;

    /**
     * The xType for the backend module.
     *
     * @var string
     *
     * @ORM\Column(name="x_type", type="string", length=255, nullable=false)
     */
    private $xType;

    /**
     * The valueType for the database
     *
     * @var string
     *
     * @ORM\Column(name="value_type", type="string", length=255, nullable=false)
     */
    private $valueType;

    /**
     * Contains the support text for the extJs field.
     *
     * @var string
     *
     * @ORM\Column(name="support_text", type="string", length=255, nullable=false)
     */
    private $supportText;

    /**
     * Contains the store name for a component field.
     *
     * @var string
     *
     * @ORM\Column(name="store", type="string", length=255, nullable=false)
     */
    private $store;

    /**
     * Contains the field name which used as display for a combo box field
     *
     * @var string
     *
     * @ORM\Column(name="display_field", type="string", length=255, nullable=false)
     */
    private $displayField;

    /**
     * Contains the field name which used as value for a combo box field
     *
     * @var string
     *
     * @ORM\Column(name="value_field", type="string", length=255, nullable=false)
     */
    private $valueField;

    /**
     * Contains the default-value for the field
     *
     * @var string
     *
     * @ORM\Column(name="default_value", type="string", length=255, nullable=false)
     */
    private $defaultValue;

    /**
     * Could this field be let unfilled
     *
     * @var int
     *
     * @ORM\Column(name="allow_blank", type="integer", length=1, nullable=false)
     */
    private $allowBlank;

    /**
     * Contains the help title for the extJs field.
     *
     * @var string
     *
     * @ORM\Column(name="help_title", type="string", length=255, nullable=false)
     */
    private $helpTitle;

    /**
     * Contains the help title for the extJs field.
     *
     * @var string
     *
     * @ORM\Column(name="help_text", type="text",  nullable=false)
     */
    private $helpText;

    /**
     * @var int
     *
     * @ORM\Column(name="translatable", type="integer", length=1, nullable=false)
     */
    private $translatable;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * Contains the assigned \Shopware\Models\Emotion\Library\Component
     * which can be configured in the backend emotion module.
     * The assigned library component contains the data definition for the grid element.
     *
     * @var \Shopware\Models\Emotion\Library\Component
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Emotion\Library\Component", inversedBy="fields")
     * @ORM\JoinColumn(name="componentID", referencedColumnName="id")
     */
    private $component;

    /**
     * Unique identifier field of the element model.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Id of the associated \Shopware\Models\Library\Component
     * which will be displayed in the shopware backend component library.
     *
     * @return int
     */
    public function getComponentId()
    {
        return $this->componentId;
    }

    /**
     * Id of the associated \Shopware\Models\Library\Component
     * which will be displayed in the shopware backend component library.
     *
     * @param int $componentId
     */
    public function setComponentId($componentId)
    {
        $this->componentId = $componentId;
    }

    /**
     * The xType for the backend module.
     *
     * @return string
     */
    public function getXType()
    {
        return $this->xType;
    }

    /**
     * The xType for the backend module.
     *
     * @param string $xType
     */
    public function setXType($xType)
    {
        $this->xType = $xType;
    }

    /**
     * Contains the support text for the extJs field.
     *
     * @return string
     */
    public function getSupportText()
    {
        return $this->supportText;
    }

    /**
     * Contains the support text for the extJs field.
     *
     * @param string $supportText
     */
    public function setSupportText($supportText)
    {
        $this->supportText = $supportText;
    }

    /**
     * Contains the help title for the extJs field.
     *
     * @return string
     */
    public function getHelpTitle()
    {
        return $this->helpTitle;
    }

    /**
     * Contains the help title for the extJs field.
     *
     * @param string $helpTitle
     */
    public function setHelpTitle($helpTitle)
    {
        $this->helpTitle = $helpTitle;
    }

    /**
     * Contains the help title for the extJs field.
     *
     * @return string
     */
    public function getHelpText()
    {
        return $this->helpText;
    }

    /**
     * Contains the help title for the extJs field.
     *
     * @param string $helpText
     */
    public function setHelpText($helpText)
    {
        $this->helpText = $helpText;
    }

    /**
     * Contains the assigned \Shopware\Models\Emotion\Library\Component
     * which can be configured in the backend emotion module.
     * The assigned library component contains the data definition for the grid element.
     *
     * @return \Shopware\Models\Emotion\Library\Component
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Contains the assigned \Shopware\Models\Emotion\Library\Component
     * which can be configured in the backend emotion module.
     * The assigned library component contains the data definition for the grid element.
     *
     * @param \Shopware\Models\Emotion\Library\Component $component
     */
    public function setComponent($component)
    {
        $this->component = $component;
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
    public function getFieldLabel()
    {
        return $this->fieldLabel;
    }

    /**
     * @param string $fieldLabel
     */
    public function setFieldLabel($fieldLabel)
    {
        $this->fieldLabel = $fieldLabel;
    }

    /**
     * @return string
     */
    public function getValueType()
    {
        return $this->valueType;
    }

    /**
     * @param string $valueType
     */
    public function setValueType($valueType)
    {
        $this->valueType = $valueType;
    }

    /**
     * @return string
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param string $store
     */
    public function setStore($store)
    {
        $this->store = $store;
    }

    /**
     * @return string
     */
    public function getDisplayField()
    {
        return $this->displayField;
    }

    /**
     * @param string $displayField
     */
    public function setDisplayField($displayField)
    {
        $this->displayField = $displayField;
    }

    /**
     * @return string
     */
    public function getValueField()
    {
        return $this->valueField;
    }

    /**
     * @param string $valueField
     */
    public function setValueField($valueField)
    {
        $this->valueField = $valueField;
    }

    /**
     * @param int $allowBlank
     */
    public function setAllowBlank($allowBlank)
    {
        $this->allowBlank = $allowBlank;
    }

    /**
     * @return int
     */
    public function getAllowBlank()
    {
        return $this->allowBlank;
    }

    /**
     * @param string $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * @param int $translatable
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;
    }
}
