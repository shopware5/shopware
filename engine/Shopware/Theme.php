<?php
/**
 * Shopware 4
 * Copyright Â© shopware AG
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

namespace Shopware;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form as Form;

/**
 * Base class for the shopware themes.
 * Used as meta information container for a theme.
 * Contains the inheritance and config definition of a theme.
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Theme
{
    /**
     * Defines the parent theme
     * @var null
     */
    protected $extend = null;

    /**
     * Defines the human readable theme name
     * which displayed in the backend
     * @var string
     */
    protected $name = '';

    /**
     * Allows to define a description text
     * for the theme
     * @var null
     */
    protected $description = null;

    /**
     * Name of the theme author.
     * @var null
     */
    protected $author = null;

    /**
     * License of the theme source code.
     *
     * @var null
     */
    protected $license = null;


    /**
     * @var array
     * Contains all field of the createConfig
     */
    private $config = array();


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


    protected $less = array();

    protected $javascript = array();


    /**
     * Don't override this function. Used
     * from the backend template module
     * to get the template hierarchy
     * @return null|string
     */
    public function getExtend()
    {
        return $this->extend;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return null
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Helper function which returns the theme
     * directory name
     *
     * @return mixed
     */
    public function getTemplate()
    {
        $class = get_class($this);
        $paths = explode("\\", $class);
        return $paths[count($paths) - 2];
    }

    /**
     * Override this function to create
     * an own theme configuration
     */
    public function createConfig(Form\Container\TabContainer $container)
    {
    }


    /**
     * Override this function to create
     * an pre sets of configuration
     */
    public function createConfigSets(ArrayCollection $collection)
    {

    }

    /**
     * Getter of the generated config.
     * @return array
     */
    final public function getConfig()
    {
        return $this->config;
    }

    /**
     * Getter for the $inheritanceConfig property.
     * @return bool
     */
    final public function useInheritanceConfig()
    {
        return $this->inheritanceConfig;
    }

    public function getLess()
    {
        return $this->less;
    }

    public function getJavascript()
    {
        return $this->javascript;
    }


    protected function createTabPanel($name, array $options = array())
    {
        $element = new Form\Container\TabContainer($name);
        $element->fromArray($options);
        return $element;
    }

    protected function createFieldSet($name, $title, array $options = array())
    {
        $element = new Form\Container\FieldSet($name, $title);
        $element->fromArray($options);
        return $element;
    }

    protected function createTab($name, $title, array $options = array())
    {
        $element = new Form\Container\Tab($name, $title);
        $element->fromArray($options);
        return $element;
    }

    protected function createTextField($name, $label, $defaultValue, array $options = array())
    {
        $element = new Form\Field\Text($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);

        return $element;
    }

    protected function createNumberField($name, $label, $defaultValue, array $options = array())
    {
        $element = new Form\Field\Number($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);
        return $element;
    }

    protected function createCheckboxField($name, $label, $defaultValue, array $options = array())
    {
        $element = new Form\Field\Boolean($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);
        return $element;
    }

    protected function createColorPickerField($name, $label, $defaultValue, array $options = array())
    {
        $element = new Form\Field\Color($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);
        return $element;
    }

    protected function createDateField($name, $label, $defaultValue, array $options = array())
    {
        $element = new Form\Field\Date($name);
        $element->fromArray($options);
        $element->setLabel($label);

        return $element;
    }

    protected function createEmField($name, $label, $defaultValue, array $options = array())
    {
        $element = new Form\Field\Em($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);
        return $element;
    }

    protected function createMediaField($name, $label, $defaultValue, array $options = array())
    {
        $element = new Form\Field\Media($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);
        return $element;
    }

    protected function createPercentField($name, $label, $defaultValue, array $options = array())
    {
        $element = new Form\Field\Percent($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);
        return $element;
    }

    protected function createPixelField($name, $label, $defaultValue, array $options = array())
    {
        $element = new Form\Field\Pixel($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);
        return $element;
    }

    protected function createSelectField($name, $label, $defaultValue, array $store, array $options = array())
    {
        $element = new Form\Field\Selection($name, $store);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);
        return $element;
    }

    protected function createTextAreaField($name, $label, $defaultValue, array $options = array())
    {
        $element = new Form\Field\TextArea($name);
        $element->fromArray($options);
        $element->setLabel($label);
        $element->setDefaultValue($defaultValue);
        return $element;
    }
}