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
 * File adapter for the enlight config classes.
 *
 * The Enlight_Config_Adapter_File is an adapter to write the enlight configuration to a file and read this.
 * The adapter use the zend config writer.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Enlight_Config_Adapter_File extends Enlight_Config_Adapter
{
    /**
     * Whether to exclusively lock the file or not
     *
     * @var bool
     */
    protected $_exclusiveLock = false;

    /**
     * Whether to skip extends or not
     *
     * @var bool
     */
    protected $_skipExtends = false;

    /**
     * The config dir.
     *
     * @var array
     */
    protected $_configDir = [];

    /**
     * The config type.
     *
     * @var string
     */
    protected $_configType = 'ini';

    /**
     * The filename suffix.
     *
     * @var string
     */
    protected $_nameSuffix;

    /**
     * Sets the options from an array.
     *
     * @param array $options
     *
     * @return Enlight_Config_Adapter
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $option) {
            switch ($key) {
                case 'exclusiveLock':
                case 'skipExtends':
                    $this->{'_' . $key} = (bool) $option;
                    break;
                case 'configDir':
                    $this->{'_' . $key} = (array) $option;
                    break;
                case 'configType':
                    $this->{'_' . $key} = (string) $option;
                    break;
                default:
                    break;
            }
        }

        return parent::setOptions($options);
    }

    /**
     * Adds a config dir
     *
     * @param string $dir
     */
    public function addConfigDir($dir)
    {
        $this->_configDir[] = $dir;
    }

    /**
     * Reads a section from the data store.
     *
     * @param Enlight_Config $config
     *
     * @return Enlight_Config_Adapter_File
     */
    public function read(Enlight_Config $config)
    {
        $section = $config->getSection();
        $name = $this->getFilename($config->getName());
        if (file_exists($name)) {
            $reader = 'Enlight_Config_Format_' . ucfirst($this->_configType);
            while (true) {
                try {
                    /** @var Enlight_Config_Format_Ini $reader */
                    $reader = new $reader($name, $section, [
                            'skipExtends' => $this->_skipExtends, ]
                    );
                    $config->setData($reader->toArray());
                    break;
                    // Section is defect
                } catch (Exception $e) {
                    if (!empty($section) && is_array($section)) {
                        // Try next section
                        array_shift($section);
                    } else {
                        $config->setData([]);
                        break;
                    }
                }
            }
        } else {
            $config->setData([]);
        }

        return $this;
    }

    /**
     * Saves the data changes to the data store.
     *
     * @param Enlight_Config $config
     * @param bool           $forceWrite
     *
     * @return Enlight_Config_Adapter_File
     */
    public function write(Enlight_Config $config, $forceWrite = false)
    {
        if (!$this->_allowWrites) {
            return $this;
        }
        $section = $config->getSection();
        $filename = $this->getFilename($config->getName());

        if (!$config->isDirty() && !$forceWrite) {
            return $this;
        }

        if (!empty($section)) {
            $base = $this->readBase($filename);
            if (is_array($section)) {
                foreach (array_reverse($section) as $sectionName) {
                    if (!isset($base->$sectionName)) {
                        $base->$sectionName = [];
                    }
                }
                $sectionName = $extendingSection = array_shift($section);
                foreach ($section as $extendedSection) {
                    $base->setExtend($extendingSection, $extendedSection);
                    $extendingSection = $extendedSection;
                }
            } else {
                $sectionName = (string) $section;
            }
            $base->$sectionName = $config;
        } else {
            $base = $config;
        }

        $dir = dirname($filename);
        if (!file_exists($dir) || !is_writeable($dir)) {
            $old = umask(0);
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
            umask($old);
        }

        if (!is_writeable($dir)) {
            return $this;
        }

        $writer = 'Enlight_Config_Writer_' . ucfirst($this->_configType);
        /** @var Enlight_Config_Writer_Writer $writer */
        $writer = new $writer([
            'config' => $base,
            'filename' => $filename, ]
        );
        $writer->write();

        return $this;
    }

    /**
     * Returns the complete filename by config name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getFilename($name)
    {
        $suffix = $this->_nameSuffix !== null ? $this->_nameSuffix : '.' . $this->_configType;
        $indexed = false;

        foreach ($this->_configDir as $key => $dir) {
            if (is_string($key) && strpos($name, $key) === 0) {
                $indexed = true;
                $name = substr_replace($name, '', 0, strlen($key));

                $result = $dir . $this->_namePrefix . $name . $suffix;
                if (file_exists($result)) {
                    return $result;
                }
                break;
            }
        }

        if (!$indexed) {
            foreach ($this->_configDir as $dir) {
                $result = $dir . $this->_namePrefix . $name . $suffix;
                if (file_exists($result)) {
                    return $result;
                }
            }
        }
        $name = $this->_configDir[0] . $this->_namePrefix . $name . $suffix;

        return $name;
    }

    /**
     * Reads the base config from data store.
     *
     * @param string $filename
     *
     * @throws Enlight_Config_Exception
     *
     * @return Enlight_Config
     */
    protected function readBase($filename)
    {
        if (file_exists($filename)) {
            $reader = 'Enlight_Config_Format_' . ucfirst($this->_configType);
            $base = new $reader($filename, null, [
                    'skipExtends' => true,
                    'allowModifications' => true, ]
            );
        } else {
            $base = new Enlight_Config([], true);
        }

        return $base;
    }
}
