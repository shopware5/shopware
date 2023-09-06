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

namespace Shopware\Components;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form\Container\FieldSet;
use Shopware\Components\Form\Container\Tab;
use Shopware\Components\Form\Container\TabContainer;
use Shopware\Components\Form\Field\Boolean as BooleanField;
use Shopware\Components\Form\Field\Color;
use Shopware\Components\Form\Field\Date;
use Shopware\Components\Form\Field\Em;
use Shopware\Components\Form\Field\Media;
use Shopware\Components\Form\Field\NoUrl;
use Shopware\Components\Form\Field\Number;
use Shopware\Components\Form\Field\Percent;
use Shopware\Components\Form\Field\Pixel;
use Shopware\Components\Form\Field\Selection;
use Shopware\Components\Form\Field\Text;
use Shopware\Components\Form\Field\TextArea;
use Shopware\Components\Theme\ConfigSet;

/**
 * Base class for the Shopware themes.
 * Used as meta information container for a theme.
 * Contains the inheritance and config definition of a theme.
 */
class Theme
{
    /**
     * Defines the parent theme
     *
     * @var string|null
     */
    protected $extend;

    /**
     * Defines for which themes the LESS definition should be discarded
     *
     * @var array
     */
    protected $discardedLessThemes = [];

    /**
     * Defines for which themes the JavaScript files should be discarded
     *
     * @var array
     */
    protected $discardedJavascriptThemes = [];

    /**
     * Defines the human readable theme name
     * which displayed in the backend
     *
     * @var string
     */
    protected $name = '';

    /**
     * Allows to define a description text
     * for the theme
     *
     * @var string|null
     */
    protected $description;

    /**
     * Name of the theme author.
     *
     * @var string|null
     */
    protected $author;

    /**
     * License of the theme source code.
     *
     * @var string|null
     */
    protected $license;

    /**
     * Flag for the inheritance configuration.
     * If this flag is set to true, the configuration
     * of extended themes will be copied to this theme.
     *
     * Example for inheritance config behavior:
     *
     * `Theme-A` extends `Theme-B`.
     * `Theme-B` contains a config field named `text1`.
     * `inheritanceConfig` of `Theme-A` is set to true.
     * `Theme-A` backend config form contains now the `text1` field as own field.
     * Notice: Changes of `text1` field won't be saved to `Theme-B` configuration!
     *
     * @var bool
     */
    protected $inheritanceConfig = true;

    /**
     * The javascript property allows to define .js files
     * which should be compressed into one small .js file for the frontend.
     * The shopware theme compiler expects that this files are
     * stored in the ../Themes/NAME/frontend/_public/ directory.
     *
     * @var array
     */
    protected $javascript = [];

    /**
     * The css property allows to define .css files
     * which should be compressed into one small .css file for the frontend.
     * The Shopware theme compiler expects that this files are
     * stored in the ../Themes/NAME/frontend/_public/ directory.
     *
     * @var array
     */
    protected $css = [];

    /**
     * Defines if theme assets should be injected before or after plugin assets.
     * This includes template directories for template inheritance and
     * less and javascript files for the theme compiler.
     *
     * @var bool
     */
    protected $injectBeforePlugins = false;

    /**
     * Don't override this function. Used
     * from the backend template module
     * to get the template hierarchy
     *
     * @return string|null
     */
    public function getExtend()
    {
        return $this->extend;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    final public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Helper function which returns the theme directory name
     */
    public function getTemplate()
    {
        $class = \get_class($this);
        $paths = explode('\\', $class);

        return $paths[\count($paths) - 2];
    }

    /**
     * Getter for the $inheritanceConfig property.
     *
     * @return bool
     */
    public function useInheritanceConfig()
    {
        return $this->inheritanceConfig;
    }

    /**
     * Returns the javascript files definition.
     *
     * @return array
     */
    public function getJavascript()
    {
        return $this->javascript;
    }

    /**
     * Returns the css files definition.
     *
     * @return array
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * Override this function to create an own theme configuration
     * Example:
     * <code>
     *  public function createConfig(Form\Container\TabContainer $container)
     *  {
     *      $tab = $this->createTab('tab_name', 'Tab title');
     *
     *      $fieldSet = $this->createFieldSet('field_set_name', 'Field set title');
     *
     *      $text = $this->createTextField('variable_name', 'Field label', 'Default value');
     *
     *      $fieldSet->addElement($text);
     *
     *      $tab->addElement($fieldSet);
     *      $container->addTab($tab);
     *  }
     * </code>
     *
     * @return void
     */
    public function createConfig(TabContainer $container)
    {
    }

    /**
     * Each theme can implement multiple configuration sets or also named color sets.
     * The shop owner has only read access on this sets.
     * The function parameter collection can be used to add new sets.
     *
     * Example:
     *   public function createConfigSets(ArrayCollection $collection)
     *   {
     *      $set = new ConfigSet();
     *      $set->setName('Set name');
     *      $set->setDescription('Set description');
     *      $set->setValues(array(
     *          'field1' => 'field1_value',
     *          'field2' => 'field2_value'
     *      ));
     *
     *      $collection->add($set);
     *   }
     *
     * @param ArrayCollection<int, ConfigSet> $collection
     *
     * @return void
     */
    public function createConfigSets(ArrayCollection $collection)
    {
    }

    /**
     * @return bool
     */
    public function injectBeforePlugins()
    {
        return $this->injectBeforePlugins;
    }

    /**
     * @return array
     */
    public function getDiscardedLessThemes()
    {
        return $this->discardedLessThemes;
    }

    /**
     * @param array $discardedLessThemes
     */
    public function setDiscardedLessThemes($discardedLessThemes)
    {
        $this->discardedLessThemes = $discardedLessThemes;
    }

    /**
     * @return array
     */
    public function getDiscardedJavascriptThemes()
    {
        return $this->discardedJavascriptThemes;
    }

    /**
     * @param array $discardedJavascriptThemes
     */
    public function setDiscardedJavascriptThemes($discardedJavascriptThemes)
    {
        $this->discardedJavascriptThemes = $discardedJavascriptThemes;
    }

    /**
     * Creates an Ext js tab panel.
     *
     * @param string $name
     *
     * @return TabContainer
     */
    protected function createTabPanel($name, array $options = [])
    {
        $element = new TabContainer($name);
        $element->fromArray($options);

        return $element;
    }

    /**
     * Creates an Ext js form field.
     *
     * @param string $name
     * @param string $title
     *
     * @return FieldSet
     */
    protected function createFieldSet($name, $title, array $options = [])
    {
        $element = new FieldSet($name, $title);
        $element->fromArray($options);

        return $element;
    }

    /**
     * Creates an Ext js container which can be used as tab panel element or as normal container.
     *
     * @param string $name
     * @param string $title
     *
     * @return Tab
     */
    protected function createTab($name, $title, array $options = [])
    {
        $element = new Tab($name, $title);
        $element->fromArray($options);

        return $element;
    }

    /**
     * Creates an Ext js text field.
     *
     * @param string $name
     * @param string $label
     *
     * @return Text
     */
    protected function createTextField($name, $label, $defaultValue, array $options = [])
    {
        $element = new Text($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    /**
     * Creates an Ext js number field.
     *
     * @param string $name
     * @param string $label
     *
     * @return Number
     */
    protected function createNumberField($name, $label, $defaultValue, array $options = [])
    {
        $element = new Number($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    /**
     * Creates an Ext js check box field.
     *
     * @param string $name
     * @param string $label
     *
     * @return BooleanField
     */
    protected function createCheckboxField($name, $label, $defaultValue, array $options = [])
    {
        $element = new BooleanField($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    /**
     * Creates a custom Shopware color picker field.
     *
     * @param string $name
     * @param string $label
     *
     * @return Color
     */
    protected function createColorPickerField($name, $label, $defaultValue, array $options = [])
    {
        $element = new Color($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    /**
     * Creates an Ext js date field.
     *
     * @param string $name
     * @param string $label
     *
     * @return Date
     */
    protected function createDateField($name, $label, $defaultValue, array $options = [])
    {
        $element = new Date($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    /**
     * Creates an Ext js text field with auto suffix `em`
     *
     * @param string $name
     * @param string $label
     *
     * @return Em
     */
    protected function createEmField($name, $label, $defaultValue, array $options = [])
    {
        $element = new Em($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    /**
     * Creates a single media selection field.
     *
     * @param string $name
     * @param string $label
     *
     * @return Media
     */
    protected function createMediaField($name, $label, $defaultValue, array $options = [])
    {
        $element = new Media($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    /**
     * Creates a text field with an auto suffix `%`
     *
     * @param string $name
     * @param string $label
     *
     * @return Percent
     */
    protected function createPercentField($name, $label, $defaultValue, array $options = [])
    {
        $element = new Percent($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    /**
     * Creates a text field with an auto suffix `px`
     *
     * @param string $name
     * @param string $label
     *
     * @return Pixel
     */
    protected function createPixelField($name, $label, $defaultValue, array $options = [])
    {
        $element = new Pixel($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    /**
     * Creates an Ext js combo box field.
     *
     * @param string  $name
     * @param string  $label
     * @param array[] $store [['text' => 'displayText', 'value'  => 10], ...]
     *
     * @return Selection
     */
    protected function createSelectField($name, $label, $defaultValue, array $store, array $options = [])
    {
        $element = new Selection($name, $store);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    /**
     * Creates an Ext js text area field.
     *
     * @param string $name
     * @param string $label
     *
     * @return TextArea
     */
    protected function createTextAreaField($name, $label, $defaultValue, array $options = [])
    {
        $element = new TextArea($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    /**
     * Creates a custom no URL text field.
     *
     * @param array<string, string> $options
     */
    protected function createNoUrlField(string $name, string $label, string $defaultValue, array $options = []): NoUrl
    {
        $element = new NoUrl($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }
}
