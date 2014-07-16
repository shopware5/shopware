<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_Form
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     $Author$
 */

/**
 * Enlight component for a form component.
 *
 * The Enlight_Components_Form is a component to present and validate a form. It extends the zend form class with
 * an adapter ability
 *
 * @category   Enlight
 * @package    Enlight_Form
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Form extends Zend_Form
{
    /**
     * Configuration for the adapter
     * @var Enlight_Config
     */
    protected $_adapter;

    /**
     * Default display group class
     * @var string
     */
    protected $_defaultDisplayGroupClass = 'Enlight_Components_Form_DisplayGroup';

    /**
     * Saves the Form using an Enlight_Config_Adapter to do so.
     * This is a rudimentary implementation and should be considered as beta
     *
     * @return Enlight_Components_Form
     */
    public function write()
    {
        $this->_adapter->setData($this->toArray());
        $this->_adapter->write();
        return $this;
    }

    /**
     * Render form
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if (null === $view && $this->_view === null) {
            $view = new Zend_View();
        }
        return parent::render($view);
    }

    /**
     * Sets the write and read adapter
     *
     * @param Enlight_Config $adapter
     * @return Enlight_Components_Form
     */
    public function setAdapter(Enlight_Config $adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * Retrieves all decorators assigned to the given element
     *
     * @return array|null
     */
    public function getElementDecorators()
    {
        return $this->_elementDecorators;
    }

    /**
     * Sets an new form element. This method will try to delete the form element first to avoid
     * conflicts
     *
     * @param      $element
     * @param      $name
     * @param null $options
     * @return Zend_Form
     */
    public function setElement($element, $name, $options = null)
    {
        $this->removeElement($name);
        return $this->addElement($element, $name, $options);
    }

    /**
     * Setter method for the id property.
     *
     * @param   int $id
     * @return  Enlight_Components_Form
     */
    public function setId($id)
    {
        $this->_attribs['id'] = $id;
        return $this;
    }

    /**
     * Converts the form to an array - so this array can be saved as a config object.
     * The optional parameter section can be uses to segment the form
     *
     * @return array
     */
    public function toArray()
    {
        $data = array();

        // Get Form Header Files
        $data = array_merge($data, $this->getAttribs());

        if ($this->_legend !== null) {
            $data['legend'] = $this->_legend;
        }
        if ($this->_description !== null) {
            $data['description'] = $this->_description;
        }
        if (!empty($this->_disableLoadDefaultDecorators)) {
            $data['disableLoadDefaultDecorators'] = $this->_disableLoadDefaultDecorators;
        }

        // Get Form Elements
        $elements = $this->getElements();
        foreach ($elements as $key => $element) {
            $data['elements'][$key] = $this->toArrayElement($element);
        }

        //if (($decorators = $this->getElementDecorators()) !== null) {
        //    $data['elementDecorators'] = $this->convertElementDecorators($decorators);
        //}

        //if (($decorators = $this->getDecorators()) !== null) {
        //    $data['decorators'] = $this->convertFormDecorators($decorators);
        //}

        return $data;
    }

    /**
     * Small helper method to clean up some reflection action on the form
     *
     * @param $name
     * @return string
     */
    protected function getShortName($name)
    {
        if ($name instanceof Zend_Form_Element) {
            /** @var $name Zend_Form_Element */
            $name = $name->getType();
        }
        if (is_object($name)) {
            $name = get_class($name);
        }

        $namespaces = array(
            'Zend_Filter_',
            'Zend_Validate_',
            'Zend_Form_Element_',
            'Zend_Form_Decorator_',
            'Enlight_Components_Form_Element_',
            'Enlight_Components_Form_Decorator_'
        );

        $name = str_replace($namespaces, '', $name);
        $name = lcfirst($name);
        return $name;
    }

    /**
     * Transforms a Zend_Form_Element to an array
     *
     * @param $element Zend_Form_Element
     * @return array
     */
    protected function toArrayElement($element)
    {
        $arrayElement = array(
            'type' => $this->getShortName($element),
            'options' => $element->getAttribs()
        );

        $options = array(
            'id', 'label', 'value', 'name', 'description',
            'allowEmpty', 'ignore', 'order', 'belongsTo',
        );

        // Handle options
        foreach ($options as $option) {
            $method = 'get' . ucwords($option);
            if (($value = $element->$method()) !== null) {
                $arrayElement['options'][$option] = $value;
            }
        }

        // Handle requirement
        if ($element->isRequired()) {
            $arrayElement['options']['required'] = true;
        }

        // Handle validators
        if (($validators = $element->getValidators()) !== null) {
            $arrayElement['options']['validators'] = $this->convertValidators($validators);
        }

        // Handle filters
        if (($filters = $element->getFilters()) !== null) {
            $arrayElement['options']['filters'] = $this->convertFilters($filters);
        }

        return $arrayElement;
    }

    /**
     * Converts elements decorators to an array
     *
     * @param array $elementDecorators
     * @return array
     */
    protected function convertElementDecorators(array $elementDecorators)
    {
        $arrayDecorators = array();
        foreach ($elementDecorators as $decorKey => $decorator) {
            if (!is_array($decorator)) {
                $arrayDecorators[$decorKey] = $decorator;
            } else {
                foreach ($decorator as $key => $value) {
                    $arrayDecorators[$decorKey][$key] = $value;
                }
            }
        }
        return $arrayDecorators;
    }

    /**
     * Converts form decorators to an array
     *
     * @param array $decorators
     * @return array
     */
    protected function convertFormDecorators(array $decorators)
    {
        $arrayDecorator = array();
        foreach ($decorators as $decorator) {
            $arrayDecorator[] = array('decorator' => $this->getShortName($decorator));
        }
        return $arrayDecorator;
    }

    /**
     * Converts form filters to an array
     * @param array $filters
     * @return array
     */
    protected function convertFilters(array $filters)
    {
        $arrayFilters = array();
        foreach ($filters as $filter) {
            $arrayFilters[] = array('filter' => $this->getShortName($filter));
        }
        return $arrayFilters;
    }

    /**
     * Converts form validators to an array
     * @param array $validators
     * @return array
     */
    protected function convertValidators(array $validators)
    {
        $arrayValidators = array();
        /** @var Zend_Validate_Interface $validator */
        foreach ($validators as $validator) {
            $arrayValidator = array('validator' => $this->getShortName($validator));
            $validator_options = $validator->getMessageVariables();
            if ($validator_options) {
                $arrayValidator['options'] = array();
                if (($messages = $validator->getMessageTemplates()) !== null) {
                    $arrayValidator['options']['messages'] = $messages;
                }
                if (!empty($validator->zfBreakChainOnFailure)) {
                    $arrayValidator['options']['breakChainOnFailure'] = true;
                }
                foreach ($validator_options as $validator_option) {
                    if (($value = $validator->$validator_option) !== null) {
                        $arrayValidator['options'][$validator_option] = $validator->$validator_option;
                    }
                }
            }
            $arrayValidators[] = $arrayValidator;
        }
        return $arrayValidators;
    }

    /**
     * Load the default decorators
     *
     * @return Zend_Form
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormElements')
            //     ->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form'))
                 ->addDecorator('Form');
        }
        return $this;
    }

    /**
     * Retrieve plugin loader for given type
     *
     * $type may be one of:
     * - decorator
     * - element
     *
     * If a plugin loader does not exist for the given type, defaults are
     * created.
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader($type = null)
    {
        $type = strtoupper($type);
        if (!isset($this->_loaders[$type])) {
            switch ($type) {
                case self::DECORATOR:
                    $prefixSegment = 'Form_Decorator';
                    $pathSegment = 'Form/Decorator';
                    break;
                case self::ELEMENT:
                    $prefixSegment = 'Form_Element';
                    $pathSegment = 'Form/Element';
                    break;
                default:
                    throw new Zend_Form_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
            }
            $this->_loaders[$type] = new Zend_Loader_PluginLoader(array(
                'Zend_' . $prefixSegment . '_' => 'Zend/' . $pathSegment . '/',
                'Enlight_Components_' . $prefixSegment . '_' => 'Enlight/Components/' . $pathSegment . '/')
            );
        }
        return $this->_loaders[$type];
    }
}
