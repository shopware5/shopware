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
use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Models\Plugin\Plugin;

/**
 *
 * Associations:
 * <code>
 *
 * </code>
 *
 *
 * Indices:
 * <code>
 *
 * </code>
 *
 * @category   Shopware
 * @package    Models
 * @subpackage Emotion
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_library_component")
 */
class Component extends ModelEntity
{
    /**
     * Unique identifier field of the grid model.
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the name of the grid which can be configured in the
     * backend emotion module.
     *
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;
    /**
     *
     *
     * @var string $convertFunction
     *
     * @ORM\Column(name="convert_function", type="string", length=255, nullable=true)
     */
    private $convertFunction = null;
    /**
     * Contains the component description which displayed in the backend
     * module of
     * @var
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * Contains the template file which used to display the component data.
     *
     * @var string $template
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=false)
     */
    private $template;

    /**
     * Contains the css class for the component
     * @var string $cls
     * @ORM\Column(name="cls", type="string", length=255, nullable=false)
     */
    private $cls;

    /**
     * The xType for the backend module.
     *
     * @var string $xType
     *
     * @ORM\Column(name="x_type", type="string", length=255, nullable=false)
     */
    private $xType;

    /**
     * Contains the plugin id which added this component to the library
     * @var integer $pluginId
     * @ORM\Column(name="pluginID", type="integer", nullable=true)
     */
    private $pluginId = null;

    /**
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Plugin\Plugin", inversedBy="emotionComponents")
     * @ORM\JoinColumn(name="pluginID", referencedColumnName="id")
     * @var Plugin
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
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Library\Field", mappedBy="component", orphanRemoval=true, cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $fields;

    /**
     * Private var that holds the max position value of the form fields
     * The value is kept up to date on a "best effort" policy
     *
     * @var int
     */
    private $maxFieldPositionValue = null;

    /**
     * Class constructor.
     * Initials all array collections and date time properties.
     */
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
     * @return \Doctrine\Common\Collections\ArrayCollection
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
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $fields
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setFields($fields)
    {
        return $this->setOneToMany($fields, '\Shopware\Models\Emotion\Library\Field', 'fields', 'component');
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
     * @return
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Contains the component description which displayed in the backend
     * module of
     * @param  $description
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
     * @param array $data
     *
     * @return Field
     */
    public function createField(array $data)
    {
        $data += array(
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
            'position' => $this->getMaxPositionValue()
        );

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
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createCheckboxField(array $options)
    {
        $options += array(
            'xtype' => 'checkboxfield'
        );

        return $this->createField($options);
    }


    /**
     *
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
     *     @type string $name               Required; Logical name of the component field
     *     @type string $store              Required; Store class which used for the combo class
     *     @type string $displayField       Required; Field name of the model which displays as text
     *     @type string $valueField         Required; Identifier field of the combo box
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createComboBoxField(array $options)
    {
        $options += array(
            'xtype' => 'combobox'
        );

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.Date element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Date
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @param array $options
     *
     * @return Field
     */
    public function createDateField(array $options)
    {
        $options += array(
            'xtype' => 'datefield'
        );

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.Display element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Display
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @param array $options
     *
     * @return Field
     */
    public function createDisplayField(array $options)
    {
        $options += array(
            'xtype' => 'displayfield'
        );

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.Hidden element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Hidden
     *
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createHiddenField(array $options)
    {
        $options += array(
            'xtype' => 'hiddenfield'
        );

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.HtmlEditor element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.HtmlEditor
     *
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createHtmlEditorField(array $options)
    {
        $options += array(
            'xtype' => 'htmleditor'
        );

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.Number element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Number
     *
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createNumberField(array $options)
    {
        $options += array(
            'xtype' => 'numberfield'
        );

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.Radio element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Radio
     *
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createRadioField(array $options)
    {
        $options += array(
            'xtype' => 'radiofield'
        );

        return $this->createField($options);
    }

    /**
     *
     * Creates a Ext.form.field.Text element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Text
     *
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createTextField(array $options)
    {
        $options += array(
            'xtype' => 'textfield'
        );

        return $this->createField($options);
    }


    /**
     * Creates a Ext.form.field.TextArea element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.TextArea
     *
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createTextAreaField(array $options)
    {
        $options += array(
            'xtype' => 'textareafield'
        );

        return $this->createField($options);
    }


    /**
     * Creates a Ext.form.field.Time element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Time
     *
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createTimeField(array $options)
    {
        $options += array(
            'xtype' => 'timefield'
        );

        return $this->createField($options);
    }

    /**
     * Creates a code mirror component field.
     *
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createCodeMirrorField(array $options)
    {
        $options += array(
            'xtype' => 'codemirrorfield'
        );

        return $this->createField($options);
    }


    /**
     * Creates a tiny mce component field.
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createTinyMceField(array $options)
    {
        $options += array(
            'xtype' => 'tinymce'
        );

        return $this->createField($options);
    }

    /**
     * Creates a media selection component field.
     * @param array $options {
     *     @type string $name               Required; Logical name of the component field
     *     @type string $fieldLabel         Optional; Ext JS form field label.
     *     @type string $allowBlank         Optional; Defines if the value can contains null
     * }
     *
     * @return Field
     */
    public function createMediaField(array $options)
    {
        $options += array(
            'xtype' => 'mediafield'
        );

        return $this->createField($options);
    }

    public function getMaxPositionValue()
    {
        if (is_null($this->maxFieldPositionValue)) {
            $this->maxFieldPositionValue = 0;

            $positions = array_map(
                function ($field) {return $field->getPosition();},
                $this->getFields()->toArray()
            );
            $this->maxFieldPositionValue = max($positions) ? : 0;
        }

        return $this->maxFieldPositionValue;
    }
}
