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
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * Enlight
 *
 * This file has been ported from Zend Framework 1 into the Enlight Framework,
 * to allow the removal of the original library from Shopware.
 *
 * This porting is in full compliance with the New BSD License
 * under which the original file is distributed.
 *
 *
 * @category   Enlight
 * @package    Enlight_Config
 */
class Enlight_Config_Writer_Ini extends Enlight_Config_Writer_FileAbstract
{
    /**
     * String that separates nesting levels of configuration data identifiers
     *
     * @var string
     */
    protected $_nestSeparator = '.';

    /**
     * If true the ini string is rendered in the global namespace without sections.
     *
     * @var bool
     */
    protected $_renderWithoutSections = false;

    /**
     * Set the nest separator
     *
     * @param  string $filename
     * @return Enlight_Config_Writer_Ini
     */
    public function setNestSeparator($separator)
    {
        $this->_nestSeparator = $separator;

        return $this;
    }

    /**
     * Set if rendering should occour without sections or not.
     *
     * If set to true, the INI file is rendered without sections completely
     * into the global namespace of the INI file.
     *
     * @param  bool $withoutSections
     * @return Enlight_Config_Writer_Ini
     */
    public function setRenderWithoutSections($withoutSections=true)
    {
        $this->_renderWithoutSections = (bool) $withoutSections;
        return $this;
    }

    /**
     * Render a Enlight_Config_BaseConfig into a INI config string.
     *
     * @since 1.10
     * @return string
     */
    public function render()
    {
        $iniString   = '';
        $extends     = $this->_config->getExtends();
        $sectionName = $this->_config->getSectionName();

        if ($this->_renderWithoutSections == true) {
            $iniString .= $this->_addBranch($this->_config);
        } elseif (is_string($sectionName)) {
            $iniString .= '[' . $sectionName . ']' . "\n"
                       .  $this->_addBranch($this->_config)
                       .  "\n";
        } else {
            $config = $this->_sortRootElements($this->_config);
            foreach ($config as $sectionName => $data) {
                if (!($data instanceof Enlight_Config_BaseConfig)) {
                    $iniString .= $sectionName
                               .  ' = '
                               .  $this->_prepareValue($data)
                               .  "\n";
                } else {
                    if (isset($extends[$sectionName])) {
                        $sectionName .= ' : ' . $extends[$sectionName];
                    }

                    $iniString .= '[' . $sectionName . ']' . "\n"
                               .  $this->_addBranch($data)
                               .  "\n";
                }
            }
        }

        return $iniString;
    }

    /**
     * Add a branch to an INI string recursively
     *
     * @param  Enlight_Config_BaseConfig $config
     * @return void
     */
    protected function _addBranch(Enlight_Config_BaseConfig $config, $parents = array())
    {
        $iniString = '';

        foreach ($config as $key => $value) {
            $group = array_merge($parents, array($key));

            if ($value instanceof Enlight_Config_BaseConfig) {
                $iniString .= $this->_addBranch($value, $group);
            } else {
                $iniString .= implode($this->_nestSeparator, $group)
                           .  ' = '
                           .  $this->_prepareValue($value)
                           .  "\n";
            }
        }

        return $iniString;
    }

    /**
     * Prepare a value for INI
     *
     * @param  mixed $value
     * @return string
     */
    protected function _prepareValue($value)
    {
        if (is_integer($value) || is_float($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return ($value ? 'true' : 'false');
        } elseif (strpos($value, '"') === false) {
            return '"' . $value .  '"';
        } else {
            return '"' . addslashes($value) .  '"';
        }
    }

    /**
     * Root elements that are not assigned to any section needs to be
     * on the top of config.
     *
     * @see    http://framework.zend.com/issues/browse/ZF-6289
     * @param  Enlight_Config_BaseConfig
     * @return Enlight_Config_BaseConfig
     */
    protected function _sortRootElements(Enlight_Config_BaseConfig $config)
    {
        $configArray = $config->toArray();
        $sections = array();

        // remove sections from config array
        foreach ($configArray as $key => $value) {
            if (is_array($value)) {
                $sections[$key] = $value;
                unset($configArray[$key]);
            }
        }

        // readd sections to the end
        foreach ($sections as $key => $value) {
            $configArray[$key] = $value;
        }

        return new Enlight_Config_BaseConfig($configArray);
    }
}
