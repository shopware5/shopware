<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Models
 * @subpackage Emotion
 * @copyright  Copyright (c) 2011-2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     $Author$
 */
namespace Shopware\Models\Emotion\Library;

use Doctrine\Common\Collections\ArrayCollection;
use         Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM;
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
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Library\Field", mappedBy="component", orphanRemoval=true, cascade={"persist", "update"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $fields;

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
        $data = array_merge(array(
            'fieldLabel' => '',
            'valueType' => '',
            'store' => '',
            'supportText' => '',
            'helpTitle' => '',
            'helpText' => '',
            'defaultValue' => '',
            'displayField' => '',
            'valueField' => '',
            'allowBlank' => false
        ), $data);

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
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createCheckboxField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'checkboxfield'
        ), $options);

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
     * @param array $options
     *
     * @return Field
     */
    public function createComboBoxField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'combobox'
        ), $options);

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.Date element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Date
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createDateField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'datefield'
        ), $options);

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.Display element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Display
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createDisplayField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'displayfield'
        ), $options);

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.Hidden element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Hidden
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createHiddenField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'hiddenfield'
        ), $options);

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.HtmlEditor element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.HtmlEditor
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createHtmlEditorField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'htmleditor'
        ), $options);

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.Number element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Number
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createNumberField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'numberfield'
        ), $options);

        return $this->createField($options);
    }


    /**
     *
     * Creates a Ext.form.field.Radio element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Radio
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createRadioField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'radiofield'
        ), $options);

        return $this->createField($options);
    }

    /**
     *
     * Creates a Ext.form.field.Text element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Text
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createTextField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'textfield'
        ), $options);

        return $this->createField($options);

    }


    /**
     * Creates a Ext.form.field.TextArea element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.TextArea
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createTextAreaField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'textareafield'
        ), $options);

        return $this->createField($options);

    }


    /**
     * Creates a Ext.form.field.Time element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Time
     *
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createTimeField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'timefield'
        ), $options);

        return $this->createField($options);

    }

    /**
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createCodeMirrorField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'codemirrorfield'
        ), $options);

        return $this->createField($options);
    }


    /**
     * This field type supports the following parameters which can be set
     * as options array value:
     *  - name
     *  - fieldLabel
     *  - allowBlank
     *
     * @param array $options
     *
     * @return Field
     */
    public function createTinyMceField(array $options)
    {
        $options = array_merge(array(
            'xtype' => 'tinymce'
        ), $options);

        return $this->createField($options);
    }
}
