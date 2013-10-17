<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

namespace Shopware\Components\Emotion;

use Shopware\Models\Emotion\Library\Component;
use Shopware\Models\Emotion\Library\Field;

/**
 * Shopware Emotion Component Manager
 *
 * Create your own emotion components.
 * To create a new component in your plugin Bootstrap.php
 * for example you can call:
 *
 * $widget = $this->Emotion()->createComponent('MyWidget', 'my-widget', 'my_widget.tpl');
 *
 * Now after you created a new component,
 * you can create your own config fields for the component.
 * To create a text field for example you can call:
 *
 * $this->Emotion()->createTextField($widget, 'my-textfield', 'My Text Field Label');
 *
 *
 * @category  Shopware
 * @package   Shopware\Components\Emotion
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Manager
{
    /**
     * Allows to assign a plugin to the components.
     * @var int|null
     */
    protected $pluginId;

    /**
     * Returns the assigned plugin id.
     * @return int|null
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * Allows to inject a plugin id which saved
     * in the created components.
     *
     * @param int|null $pluginId
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = $pluginId;
    }

    /**
     * Class constructor required to inject the plugin id.
     * The plugin id is optional, so the emotion manager
     * can also used without the plugin bootstrap scope.
     *
     * @param int $pluginId
     */
    public function __construct($pluginId = null)
    {
        $this->pluginId = $pluginId;
    }

    /**
     * Creates a new component which can be used in the backend emotion
     * module. This function is required for the subsequent function like
     * the createEmotionComponentCheckboxField which expects a already
     * created emotion component.
     *
     * @param string $name
     * @param string $cls
     * @param string $template
     * @param string $xType
     * @param null $convertFunction
     * @param string $description
     *
     * @return Component
     */
    public function createComponent(
        $name,
        $cls,
        $template,
        $xType = '',
        $convertFunction = null,
        $description = ''
    ) {
        $component = new Component();
        $component->setName($name);
        $component->setXType($xType);
        $component->setCls($cls);
        $component->setTemplate($template);
        $component->setConvertFunction($convertFunction);
        $component->setDescription($description);
        $component->setPluginId($this->pluginId);

        return $component;
    }

    /**
     * Creates a checkbox field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.Checkbox element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Checkbox
     *
     * @param Component $component
     * @param string $name
     * @param string $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param bool $allowBlank
     *
     * @return Field
     */
    public function createCheckboxField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $allowBlank = false
    ) {
        return $this->createField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'checkboxfield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Creates a combobox field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.ComboBox element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.ComboBox
     *
     * @param Component $component
     * @param string $name
     * @param string $fieldLabel
     * @param string $store
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $displayField
     * @param string $valueField
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    public function createComboBoxField(
        Component $component,
        $name,
        $fieldLabel,
        $store = '',
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $displayField = '',
        $valueField = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'combobox',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'store' => $store,
            'valueField' => $valueField,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'displayField' => $displayField,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Creates a date field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.Date element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Date
     *
     * @param Component $component
     * @param string $name
     * @param string $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    public function createDateField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'datefield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Creates a display field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.Display element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Display
     *
     * @param Component $component
     * @param string $name
     * @param string $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    public function createDisplayField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'displayfield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText'=> $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Creates a hidden field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.Hidden element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Hidden
     *
     * @param Component $component
     * @param string $name
     * @param string $valueType
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    public function createHiddenField(
        Component $component,
        $name,
        $valueType = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'hiddenfield',
            'name' => $name,
            'valueType' => $valueType,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Creates a html editor field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.HtmlEditor element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.HtmlEditor
     *
     * @param Component $component
     * @param string $name
     * @param string $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    public function createEditorField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'htmleditor',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Creates a number field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.Number element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Number
     *
     * @param Component $component
     * @param string $name
     * @param string $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    public function createNumberField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'numberfield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Creates a radio field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.Radio element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Radio
     *
     * @param Component $component
     * @param string $name
     * @param string $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param bool $allowBlank
     *
     * @return Field
     */
    public function createRadioField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $allowBlank = false
    ) {
        return $this->createField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'radiofield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Creates a text field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.Text element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Text
     *
     * @param Component $component
     * @param string $name
     * @param string $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    public function createTextField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'textfield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Creates a text area field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.TextArea element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.TextArea
     *
     * @param Component $component
     * @param string $name
     * @param string $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    public function createTextAreaField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'textareafield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Creates a time field for the passed emotion component widget.
     *
     * Creates a Ext.form.field.Time element.
     * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.field.Time
     *
     * @param Component $component
     * @param string $name
     * @param string $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    public function createTimeField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'timefield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Internal helper function which creates a single emotion component field.
     *
     * @param Component $component
     * @param array $data
     *
     * @return Field
     * @throws \Exception
     */
    public function createField(Component $component, array $data)
    {
        if (!($component instanceof Component)) {
            throw new \Exception("The passed component object has to be an instance of \\Shopware\\Models\\Emotion\\Library\\Component");
        }

        $defaults = array(
            'fieldLabel' => '',
            'valueType' => '',
            'store' => '',
            'supportText' => '',
            'helpTitle' => '',
            'helpText' => '',
            'defaultValue' => '',
            'displayField' => '',
            'valueField' => '',
            'allowBlank' => ''
        );

        $data = array_merge($defaults, $data);

        $field = new Field();
        $field->fromArray($data);

        return $field;
    }


}