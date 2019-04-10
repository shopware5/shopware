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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Plugin\Plugin;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_library_component")
 */
class Component extends ModelEntity
{
    /**
     * @var Plugin
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Plugin\Plugin", inversedBy="emotionComponents")
     * @ORM\JoinColumn(name="pluginID", referencedColumnName="id")
     */
    protected $plugin;

    /**
     * INVERSE SIDE
     * Contains all the assigned \Shopware\Models\Emotion\Library\Field models.
     * Each component has a field configuration to configure the component data over the
     * backend module. For example: The "Article" component has an "id" field
     * with xtype: 'emotion-article-search' (the shopware article suggest search with a individual configuration for the
     * backend module) to configure which article has to been displayed.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Emotion\Library\Field>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Library\Field", mappedBy="component", orphanRemoval=true, cascade={"persist"})
     */
    protected $fields;

    /**
     * Unique identifier field of the grid model.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the name of the grid which can be configured in the
     * backend emotion module.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="convert_function", type="string", length=255, nullable=true)
     */
    private $convertFunction;

    /**
     * Contains the component description which displayed in the backend
     * module of
     *
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * Contains the template file which used to display the component data.
     *
     * @var string
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=false)
     */
    private $template;

    /**
     * Contains the css class for the component
     *
     * @var string
     *
     * @ORM\Column(name="cls", type="string", length=255, nullable=false)
     */
    private $cls;

    /**
     * The xType for the backend module.
     *
     * @var string
     *
     * @ORM\Column(name="x_type", type="string", length=255, nullable=false)
     */
    private $xType;

    /**
     * Contains the plugin id which added this component to the library
     *
     * @var int
     *
     * @ORM\Column(name="pluginID", type="integer", nullable=true)
     */
    private $pluginId;

    /**
     * Private var that holds the max position value of the form fields
     * The value is kept up to date on a "best effort" policy
     *
     * @var int
     */
    private $maxFieldPositionValue;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    /**
     * Contains all the assigned \Shopware\Models\Emotion\Library\Field models.
     * Each component has a field configuration to configure the component data over the
     * backend module. For example: The "Article" component has an "id" field
     * with xtype: 'emotion-article-search' (the shopware article suggest search with a individual configuration for the
     * backend module) to configure which article has to been displayed.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Emotion\Library\Field>
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Contains all the assigned \Shopware\Models\Emotion\Library\Field models.
     * Each component has a field configuration to configure the component data over the
     * backend module. For example: The "Article" component has an "id" field
     * with xtype: 'emotion-article-search' (the shopware article suggest search with a individual configuration for the
     * backend module) to configure which article has to been displayed.
     *
     * @param \Shopware\Models\Emotion\Library\Field[]|array|null $fields
     *
     * @return Component
     */
    public function setFields($fields)
    {
        return $this->setOneToMany($fields, \Shopware\Models\Emotion\Library\Field::class, 'fields', 'component');
    }

    /**
     * Unique identifier field of the grid model.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Contains the name of the grid which can be configured in the
     * backend emotion module.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Contains the name of the grid which can be configured in the
     * backend emotion module.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Contains the component description which displayed in the backend
     * module of
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Contains the component description which displayed in the backend
     * module of
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Contains the template file which used to display the component data.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Contains the template file which used to display the component data.
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getCls()
    {
        return $this->cls;
    }

    /**
     * @param string $cls
     */
    public function setCls($cls)
    {
        $this->cls = $cls;
    }

    /**
     * @return int
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * @param int $pluginId
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = $pluginId;
    }

    /**
     * @return string
     */
    public function getXType()
    {
        return $this->xType;
    }

    /**
     * @param string $xType
     */
    public function setXType($xType)
    {
        $this->xType = $xType;
    }

    /**
     * @return string
     */
    public function getConvertFunction()
    {
        return $this->convertFunction;
    }

    /**
     * @param string $convertFunction
     */
    public function setConvertFunction($convertFunction)
    {
        $this->convertFunction = $convertFunction;
    }

    /**
     * @return \Shopware\Models\Plugin\Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param \Shopware\Models\Plugin\Plugin $plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Generally function to create a new custom emotion component field.
     *
     * @return Field
     */
    public function createField(array $data)
    {
        $data += [
            'fieldLabel' => '',
            'valueType' => '',
            'store' => '',
            'supportText' => '',
            'helpTitle' => '',
            'helpText' => '',
            'defaultValue' => '',
            'displayField' => '',
            'valueField' => '',
            'allowBlank' => false,
            'translatable' => false,
            'position' => $this->getMaxPositionValue(),
        ];

        $this->maxFieldPositionValue = max($data['position'], $this->maxFieldPositionValue) + 1;

        $field = new Field();
        $field->fromArray($data);

        $field->setComponent($this);
        $this->fields->add($field);

        return $field;
    }

    /**
     * Creates a checkbox field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.Checkbox element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Checkbox
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createCheckboxField(array $options)
    {
        $options += [
            'xtype' => 'checkboxfield',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a Ext.form.field.ComboBox element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.ComboBox
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *  - store
     *  - displayField
     *  - valueField
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $store              Required; Store class which used for the combo class
     *     @var string $displayField       Required; Field name of the model which displays as text
     *     @var string $valueField         Required; Identifier field of the combo box
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createComboBoxField(array $options)
    {
        $options += [
            'xtype' => 'combobox',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a Ext.form.field.Date element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Date
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     *     @var string $defaultValue       Optional; date string in format Y-m-d
     * }
     *
     * @return Field
     */
    public function createDateField(array $options)
    {
        $options += [
            'xtype' => 'datefield',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a Ext.form.field.Display element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Display
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createDisplayField(array $options)
    {
        $options += [
            'xtype' => 'displayfield',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a Ext.form.field.Hidden element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Hidden
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createHiddenField(array $options)
    {
        $options += [
            'xtype' => 'hiddenfield',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a Ext.form.field.HtmlEditor element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.HtmlEditor
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createHtmlEditorField(array $options)
    {
        $options += [
            'xtype' => 'htmleditor',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a Ext.form.field.Number element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Number
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createNumberField(array $options)
    {
        $options += [
            'xtype' => 'numberfield',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a Ext.form.field.Radio element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Radio
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createRadioField(array $options)
    {
        $options += [
            'xtype' => 'radiofield',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a Ext.form.field.Text element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Text
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createTextField(array $options)
    {
        $options += [
            'xtype' => 'textfield',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a Ext.form.field.TextArea element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.TextArea
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createTextAreaField(array $options)
    {
        $options += [
            'xtype' => 'textareafield',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a Ext.form.field.Time element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Time
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     *     @var string $defaultValue       Optional; default value as string in format H:i
     * }
     *
     * @return Field
     */
    public function createTimeField(array $options)
    {
        $options += [
            'xtype' => 'timefield',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a code mirror component field.
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createCodeMirrorField(array $options)
    {
        $options += [
            'xtype' => 'codemirrorfield',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a tiny mce component field.
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createTinyMceField(array $options)
    {
        $options += [
            'xtype' => 'tinymce',
        ];

        return $this->createField($options);
    }

    /**
     * Creates a media selection component field.
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component field
     *     @var string $fieldLabel         optional; Ext JS form field label
     *     @var string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createMediaField(array $options)
    {
        $options += [
            'xtype' => 'mediafield',
        ];

        return $this->createField($options);
    }

    public function getMaxPositionValue()
    {
        if ($this->maxFieldPositionValue === null) {
            $this->maxFieldPositionValue = 0;

            $positions = array_map(
                function ($field) {
                    return $field->getPosition();
                },
                $this->getFields()->toArray()
            );

            $this->maxFieldPositionValue = !empty($positions) ? max($positions) : 0;
        }

        return $this->maxFieldPositionValue;
    }
}
