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
 * @package    Enlight_Site
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Manager for the registered site components.
 *
 * The Enlight_Components_Site_Manager managed all registered sites and provides
 * interfaces for reading a page available
 *
 * @category   Enlight
 * @package    Enlight_Site
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Site_Manager
{
    /**
     * @var mixed|Enlight_Config Contains the adapter config, which can set in the constructor
     */
    protected $adapter;

    /**
     * The options parameter will be used to set the config adapter.
     * If the options parameter will be passed as an array, the array element with the key "adapter"
     * must be an instance of the Enlight_Config.
     *
     * @param   array|Enlight_Config $options
     */
    public function __construct($options = null)
    {
        if (!is_array($options)) {
            $options = array('adapter' => $options);
        }

        if (isset($options['adapter']) && $options['adapter'] instanceof Enlight_Config) {
            $this->adapter = $options['adapter'];
        }
    }

    /**
     * Returns a child page matching $property == $value, or null if not found
     *
     * @param   $property
     * @param   $value
     * @return  Enlight_Components_Site|null
     */
    public function findOneBy($property, $value)
    {
        if (isset($this->adapter->sites)) {
            foreach ($this->adapter->sites as $site) {
                if ($site->$property === $value) {
                    return new Enlight_Components_Site($site);
                }
            }
        }
        return null;
    }

    /**
     * Returns a default site instance.
     *
     * @return  Enlight_Components_Site|null
     */
    public function getDefault()
    {
        if (!isset($this->adapter->sites)) {
            return null;
        }
        reset($this->adapter->sites);
        $default = current($this->adapter->sites);
        foreach ($this->adapter->sites as $site) {
            if (!empty($site->default)) {
                $default = $site;
                break;
            }
        }
        $default = new Enlight_Components_Site($default);
        return $default;
    }
}
