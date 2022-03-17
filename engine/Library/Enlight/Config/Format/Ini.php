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
 * @category   Enlight
 * @package    Enlight_Config_Format_Ini
 */
class Enlight_Config_Format_Ini extends Enlight_Config_BaseConfig
{
    private const EXTENDS_MARKER = ';extends';

    /**
     * String that separates nesting levels of configuration data identifiers
     *
     * @var non-empty-string
     */
    protected $_nestSeparator = '.';

    /**
     * String that separates the parent section name
     *
     * @var non-empty-string
     */
    protected $_sectionSeparator = ':';

    /**
     * Whether to skip extends or not
     *
     * @var bool
     */
    protected $_skipExtends = false;

    /**
     * Loads the section $section from the config file $filename for
     * access facilitated by nested object properties.
     *
     * If the section name contains a ":" then the section name to the right
     * is loaded and included into the properties. Note that the keys in
     * this $section will override any keys of the same
     * name in the sections that have been included via ":".
     *
     * If the $section is null, then all sections in the ini file are loaded.
     *
     * If any key includes a ".", then this will act as a separator to
     * create a sub-property.
     *
     * example ini file:
     *      [all]
     *      db.connection = database
     *      hostname = live
     *
     *      [staging : all]
     *      hostname = staging
     *
     * after calling $data = new Enlight_Config_Format_Ini($file, 'staging'); then
     *      $data->hostname === "staging"
     *      $data->db->connection === "database"
     *
     * The $options parameter may be provided as either a boolean or an array.
     * If provided as a boolean, this sets the $allowModifications option of
     * Enlight_Config_BaseConfig. If provided as an array, there are three configuration
     * directives that may be set. For example:
     *
     * $options = array(
     *     'allowModifications' => false,
     *     'nestSeparator'      => ':',
     *     'skipExtends'        => false,
     *      );
     *
     * @param string     $filename
     * @param bool|array $options
     *
     * @throws Enlight_Config_Exception
     *
     * @return void
     */
    public function __construct($filename, $section = null, $options = false)
    {
        if (empty($filename)) {
            throw new Enlight_Config_Exception('Filename is not set');
        }

        $allowModifications = false;
        if (\is_bool($options)) {
            $allowModifications = $options;
        } elseif (\is_array($options)) {
            if (isset($options['allowModifications'])) {
                $allowModifications = (bool) $options['allowModifications'];
            }
            if (isset($options['nestSeparator'])) {
                $nestSeparator = (string) $options['nestSeparator'];
                if ($nestSeparator === '') {
                    throw new UnexpectedValueException('nestSeparator must be a non-empty string');
                }
                $this->_nestSeparator = $nestSeparator;
            }
            if (isset($options['skipExtends'])) {
                $this->_skipExtends = (bool) $options['skipExtends'];
            }
        }

        $iniArray = $this->_loadIniFile($filename);

        if ($section === null) {
            // Load entire file
            $dataArray = [];
            foreach ($iniArray as $sectionName => $sectionData) {
                if (!\is_array($sectionData)) {
                    $dataArray = $this->_arrayMergeRecursive($dataArray, $this->_processKey([], $sectionName, $sectionData));
                } else {
                    $dataArray[$sectionName] = $this->_processSection($iniArray, $sectionName);
                }
            }
            parent::__construct($dataArray, $allowModifications);
        } else {
            // Load one or more sections
            if (!\is_array($section)) {
                $section = [$section];
            }
            $dataArray = [];
            foreach ($section as $sectionName) {
                if (!isset($iniArray[$sectionName])) {
                    throw new Enlight_Config_Exception(sprintf("Section '%s' cannot be found in %s", $sectionName, $filename));
                }
                $dataArray = $this->_arrayMergeRecursive($this->_processSection($iniArray, $sectionName), $dataArray);
            }
            parent::__construct($dataArray, $allowModifications);
        }

        $this->_loadedSection = $section;
    }

    /**
     * Load the INI file from disk using parse_ini_file(). Use a private error
     * handler to convert any loading errors into a Enlight_Config_Exception
     *
     * @param string $filename
     *
     * @throws Enlight_Config_Exception
     *
     * @return array
     */
    protected function _parseIniFile($filename)
    {
        set_error_handler([$this, '_loadFileErrorHandler']);
        $iniArray = parse_ini_file($filename, true); // Warnings and errors are suppressed
        restore_error_handler();

        // Check if there was a error while loading file
        if ($this->_loadFileErrorStr !== null) {
            throw new Enlight_Config_Exception($this->_loadFileErrorStr);
        }

        return $iniArray;
    }

    /**
     * Load the ini file and preprocess the section separator (':' in the
     * section name (that is used for section extension) so that the resultant
     * array has the correct section names and the extension information is
     * stored in a sub-key called ';extends'. We use ';extends' as this can
     * never be a valid key name in an INI file that has been loaded using
     * parse_ini_file().
     *
     * @param string $filename
     *
     * @throws Enlight_Config_Exception
     *
     * @return array
     */
    protected function _loadIniFile($filename)
    {
        $loaded = $this->_parseIniFile($filename);
        $iniArray = [];
        foreach ($loaded as $key => $data) {
            $pieces = explode($this->_sectionSeparator, $key);
            if (!\is_array($pieces)) {
                continue;
            }

            $thisSection = trim($pieces[0]);
            switch (\count($pieces)) {
                case 1:
                    $iniArray[$thisSection] = $data;
                    break;

                case 2:
                    $extendedSection = trim($pieces[1]);
                    $iniArray[$thisSection] = array_merge([self::EXTENDS_MARKER => $extendedSection], $data);
                    break;

                default:
                    throw new Enlight_Config_Exception(sprintf("Section '%s' may not extend multiple sections in %s", $thisSection, $filename));
            }
        }

        return $iniArray;
    }

    /**
     * Process each element in the section and handle the ";extends" inheritance
     * key. Passes control to _processKey() to handle the nest separator
     * sub-property syntax that may be used within the key name.
     *
     * @param array  $iniArray
     * @param string $section
     * @param array  $config
     *
     * @throws Enlight_Config_Exception
     *
     * @return array
     */
    protected function _processSection($iniArray, $section, $config = [])
    {
        $thisSection = $iniArray[$section];

        foreach ($thisSection as $key => $value) {
            if (strtolower($key) === self::EXTENDS_MARKER) {
                if (isset($iniArray[$value])) {
                    $this->_assertValidExtend($section, $value);

                    if (!$this->_skipExtends) {
                        $config = $this->_processSection($iniArray, $value, $config);
                    }
                } else {
                    throw new Enlight_Config_Exception(sprintf("Parent section '%s' cannot be found", $section));
                }
            } else {
                $config = $this->_processKey($config, $key, $value);
            }
        }

        return $config;
    }

    /**
     * Assign the key's value to the property list. Handles the
     * nest separator for sub-properties.
     *
     * @param array  $config
     * @param string $key
     * @param string $value
     *
     * @throws Enlight_Config_Exception
     *
     * @return array
     */
    protected function _processKey($config, $key, $value)
    {
        if (str_contains($key, $this->_nestSeparator)) {
            $pieces = explode($this->_nestSeparator, $key, 2);
            if ($pieces !== false && $pieces[0] !== '' && $pieces[1] !== '') {
                if (!isset($config[$pieces[0]])) {
                    if ($pieces[0] === '0' && !empty($config)) {
                        // convert the current values in $config into an array
                        $config = [$pieces[0] => $config];
                    } else {
                        $config[$pieces[0]] = [];
                    }
                } elseif (!\is_array($config[$pieces[0]])) {
                    throw new Enlight_Config_Exception(sprintf("Cannot create sub-key for '%s' as key already exists", $pieces[0]));
                }
                $config[$pieces[0]] = $this->_processKey($config[$pieces[0]], $pieces[1], $value);
            } else {
                throw new Enlight_Config_Exception(sprintf("Invalid key '%s'", $key));
            }
        } else {
            $config[$key] = $value;
        }

        return $config;
    }
}
