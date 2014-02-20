<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

use Shopware\Models\Shop\Template\ConfigElement;

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
     * Constant for the color picker field
     * which can be used as template config element
     */
    const TYPE_COLOR_PICKER = 'theme-color-picker';

    /**
     * Constant for the em input field
     * which can be used as template config element
     */
    const TYPE_EM_FIELD = 'theme-em-field';

    /**
     * Constant for the percent input field
     * which can be used as template config element
     */
    const TYPE_PERCENT_FIELD = 'theme-percent-field';

    /**
     * Constant for the date input field
     * which can be used as template config element
     */
    const TYPE_DATE_FIELD = 'theme-date-field';

    /**
     * Constant for the text input field
     * which can be used as template config element
     */
    const TYPE_TEXT_FIELD = 'theme-text-field';

    /**
     * Constant for the text area input field
     * which can be used as template config element
     */
    const TYPE_TEXT_AREA_FIELD = 'theme-text-area-field';

    /**
     * Constant for the media selection field
     * which can be used as template config element
     */
    const TYPE_MEDIA = 'theme-media-selection';

    /**
     * Constant for the checkbox field
     * which can be used as template config element
     */
    const TYPE_CHECKBOX = 'theme-checkbox-field';

    /**
     * Constant for the checkbox field
     * which can be used as template config element
     */
    const TYPE_PIXEL = 'theme-pixel-field';

    /**
     * Constant for the checkbox field
     * which can be used as template config element
     */
    const TYPE_SELECT = 'theme-select-field';

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
    public function createConfig()
    {
    }


    /**
     * Creates a color picker field which displayed in the theme configuration
     * window of the theme manager module.
     *
     * @param array $options {
     *      @type string $name          Required; Logical name which used as template variable name
     *      @type string $fieldLabel    Optional; Ext JS form field label.
     *      @type string $allowBlank    Optional; Defines if the value can contains null
     *      @type string $defaultValue  Optional; Default value of this config element
     *      @type string $position      Optional; Position which can be used to define the field positions
     *      @type string $supportText   Optional; Text which displayed below the input field.
     * }
     *
     * 
     */
    protected function createColorPicker(array $options)
    {
        $options += array('type' => self::TYPE_COLOR_PICKER);
        $this->createConfigElement($options);
    }

    /**
     * Creates a color picker field which displayed in the theme configuration
     * window of the theme manager module.
     *
     * @param array $options {
     *      @type string $name          Required; Logical name which used as template variable name
     *      @type string $fieldLabel    Optional; Ext JS form field label.
     *      @type string $allowBlank    Optional; Defines if the value can contains null
     *      @type string $defaultValue  Optional; Default value of this config element
     *      @type string $position      Optional; Position which can be used to define the field positions
     *      @type string $supportText   Optional; Text which displayed below the input field.
     * }
     *
     * 
     */
    protected function createEmField(array $options)
    {
        $options += array('type' => self::TYPE_EM_FIELD);
        $this->createConfigElement($options);
    }

    /**
     * Creates a color picker field which displayed in the theme configuration
     * window of the theme manager module.
     *
     * @param array $options {
     *      @type string $name          Required; Logical name which used as template variable name
     *      @type string $fieldLabel    Optional; Ext JS form field label.
     *      @type string $allowBlank    Optional; Defines if the value can contains null
     *      @type string $defaultValue  Optional; Default value of this config element
     *      @type string $position      Optional; Position which can be used to define the field positions
     *      @type string $supportText   Optional; Text which displayed below the input field.
     * }
     *
     * 
     */
    protected function createPercentField(array $options)
    {
        $options += array('type' => self::TYPE_PERCENT_FIELD);
        $this->createConfigElement($options);
    }

    /**
     * Creates a color picker field which displayed in the theme configuration
     * window of the theme manager module.
     *
     * @param array $options {
     *      @type string $name          Required; Logical name which used as template variable name
     *      @type string $fieldLabel    Optional; Ext JS form field label.
     *      @type string $allowBlank    Optional; Defines if the value can contains null
     *      @type string $defaultValue  Optional; Default value of this config element
     *      @type string $position      Optional; Position which can be used to define the field positions
     *      @type string $supportText   Optional; Text which displayed below the input field.
     * }
     *
     * 
     */
    protected function createDateField(array $options)
    {
        $options += array('type' => self::TYPE_DATE_FIELD);
        $this->createConfigElement($options);
    }

    /**
     * Creates a color picker field which displayed in the theme configuration
     * window of the theme manager module.
     *
     * @param array $options {
     *      @type string $name          Required; Logical name which used as template variable name
     *      @type string $fieldLabel    Optional; Ext JS form field label.
     *      @type string $allowBlank    Optional; Defines if the value can contains null
     *      @type string $defaultValue  Optional; Default value of this config element
     *      @type string $position      Optional; Position which can be used to define the field positions
     *      @type string $supportText   Optional; Text which displayed below the input field.
     * }
     *
     * 
     */
    protected function createMediaSelection(array $options)
    {
        $options += array('type' => self::TYPE_MEDIA);
        $this->createConfigElement($options);
    }

    /**
     * Creates a color picker field which displayed in the theme configuration
     * window of the theme manager module.
     *
     * @param array $options {
     *      @type string $name          Required; Logical name which used as template variable name
     *      @type string $fieldLabel    Optional; Ext JS form field label.
     *      @type string $allowBlank    Optional; Defines if the value can contains null
     *      @type string $defaultValue  Optional; Default value of this config element
     *      @type string $position      Optional; Position which can be used to define the field positions
     *      @type string $supportText   Optional; Text which displayed below the input field.
     * }
     *
     * 
     */
    protected function createTextField(array $options)
    {
        $options += array('type' => self::TYPE_TEXT_FIELD);
        $this->createConfigElement($options);
    }

    /**
     * Creates a color picker field which displayed in the theme configuration
     * window of the theme manager module.
     *
     * @param array $options {
     *      @type string $name          Required; Logical name which used as template variable name
     *      @type string $fieldLabel    Optional; Ext JS form field label.
     *      @type string $allowBlank    Optional; Defines if the value can contains null
     *      @type string $defaultValue  Optional; Default value of this config element
     *      @type string $position      Optional; Position which can be used to define the field positions
     *      @type string $supportText   Optional; Text which displayed below the input field.
     * }
     *
     * 
     */
    protected function createTextAreaField(array $options)
    {
        $options += array('type' => self::TYPE_TEXT_AREA_FIELD);
        $this->createConfigElement($options);
    }

    /**
     * Creates a color picker field which displayed in the theme configuration
     * window of the theme manager module.
     *
     * @param array $options {
     *      @type string $name          Required; Logical name which used as template variable name
     *      @type string $fieldLabel    Optional; Ext JS form field label.
     *      @type string $allowBlank    Optional; Defines if the value can contains null
     *      @type string $defaultValue  Optional; Default value of this config element
     *      @type string $position      Optional; Position which can be used to define the field positions
     *      @type string $supportText   Optional; Text which displayed below the input field.
     * }
     */
    protected function createCheckboxField(array $options)
    {
        $options += array('type' => self::TYPE_CHECKBOX);
        $this->createConfigElement($options);
    }

    /**
     * Creates a pixel field which displayed in the theme configuration
     * window of the theme manager module.
     *
     * @param array $options {
     *      @type string $name          Required; Logical name which used as template variable name
     *      @type string $fieldLabel    Optional; Ext JS form field label.
     *      @type string $allowBlank    Optional; Defines if the value can contains null
     *      @type string $defaultValue  Optional; Default value of this config element
     *      @type string $position      Optional; Position which can be used to define the field positions
     *      @type string $supportText   Optional; Text which displayed below the input field.
     * }
     */
    protected function createPixelField(array $options)
    {
        $options += array('type' => self::TYPE_PIXEL);
        $this->createConfigElement($options);
    }


    /**
     * Creates a select field which displayed in the theme configuration
     * window of the theme manager module.
     *
     * @param array $options {
     *      @type string $name          Required; Logical name which used as template variable name
     *      @type string $fieldLabel    Optional; Ext JS form field label.
     *      @type string $allowBlank    Optional; Defines if the value can contains null
     *      @type string $defaultValue  Optional; Default value of this config element
     *      @type string $position      Optional; Position which can be used to define the field positions
     *      @type string $supportText   Optional; Text which displayed below the input field.
     * }
     */
    protected function createSelectField(array $options)
    {
        $options += array('type' => self::TYPE_SELECT);
        $this->createConfigElement($options);
    }

    /**
     * Helper function which creates a new template config element.
     * Requires that the $data parameter contains a $name property.
     *
     * @param array $data
     * @throws \Exception
     */
    private function createConfigElement(array $data)
    {
        //name is the only requirement for a config field.
        if (!isset($data['name'])) {
            throw new \Exception(sprintf(
                'Theme %s tries to create a config element without a name!',
                $this->name
            ));
        }

        $element = new ConfigElement();

        $element->fromArray($data);

        //name has to be unique, otherwise throw exception
        if (in_array($element->getName(), $this->config)) {
            throw new \Exception(sprintf(
                'Theme %s configured duplicate name %s in different config elements',
                $this->name, $element->getName()
            ));
        }

        $this->config[$element->getName()] = $element;
    }

    /**
     * Getter of the generated config.
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getLess()
    {
        return $this->less;
    }

    public function getJavascript()
    {
        return $this->javascript;
    }

}