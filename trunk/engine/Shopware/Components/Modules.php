<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Components
 * @subpackage Modules
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     $Author$
 */

/**
 * Backend Controller for the backend user management
 * todo@all: Documentation
 * @changes
 * 2012-05-04 sth
 * - Remove support for inherit folders
 * - Fix bug in auto-loader that occurs on osx machines
 */
class Shopware_Components_Modules extends Enlight_Class implements ArrayAccess
{
    /**
     * Path to engine/core/class
     * @var string
     */
    protected $module_path;

    /**
     * Name of system class
     * @var string
     */
    protected $system;

    /**
     * List with all known module classes from database
     * @var array
     */
    protected $modules_list;

    /**
     * Container that hold references to all modules already loaded
     * @var array
     */
    protected $modules_container = array();

    /**
     * Initiate class parameters
     * @return void
     */
    public function init()
    {
        $this->module_path = Shopware()->OldPath() . 'engine/core/class/';
        $this->modules_list = Shopware()->Db()->fetchAssoc('
			SELECT basename, basefile, inheritname, inheritfile FROM s_core_factory
		');
    }

    /**
     * Set class property
     * @param $system
     */
    public function setSystem($system)
    {
        $this->system = $system;
    }

    /**
     * Load a module defined by $name
     * Possible values for $name - sBasket, sAdmin etc.
     * @param $name
     */
    public function loadModule($name)
    {
        if (!isset($this->modules_container[$name])) {
            $this->modules_container[$name] = null;
            $name = basename($name);
            $module = isset($this->modules_list[$name]) ? $this->modules_list[$name] : array();

            // This path will be uses in included files - DO NOT DELETE IT
            $path = $this->module_path;

            if (empty($module['basename'])) {
                $module['basename'] = $name;
            }
            if (empty($module['basefile'])) {
                $module['basefile'] = $module['basename'] . '.php';
            }

            if (file_exists($this->module_path . $module['basefile'])) {
                require_once($this->module_path . $module['basefile']);
            }

            $class_name = $module['basename'];

            if (!empty($class_name)) {
                Shopware()->Hooks()->setAlias($name, $class_name);
                $proxy = Shopware()->Hooks()->getProxy($class_name);
                $this->modules_container[$name] = new $proxy;
            }

            if (!empty($this->modules_container[$name])) {
                $this->modules_container[$name]->sSYSTEM = $this->system;
            }
        }
    }

    /**
     * Reformat module name and return reference to module
     * @param $name
     * @return mixed
     */
    public function getModule($name)
    {
        if (substr($name, 0, 1) == 's') {
            $name = substr($name, 1);
        }
        if (!in_array($name, array('RewriteTable', 'TicketSystem'))) {
            $name = "s" . ucfirst(strtolower($name));
        } else {
            $name = "s" . $name;
        }

        if (!isset($this->modules_container[$name])) {
            $this->loadModule($name);
        }
        return $this->modules_container[$name];
    }

    /**
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return (bool)$this->getModule($offset);
    }

    /**
     * @param $offset
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getModule($offset);
    }

    /**
     * @param string $name
     * @param null $value
     * @return mixed
     */
    public function __call($name, $value = null)
    {
        return $this->getModule($name);
    }

    /**
     * @return sArticles
     */
    public function Articles()
    {
        return $this->getModule("Articles");
    }

    /**
     * @return sCategories
     */
    public function Categories()
    {
        return $this->getModule("Categories");
    }

    /**
     * @return sBasket
     */
    public function Basket()
    {
        return $this->getModule("Basket");
    }

    /**
     * @return sMarketing
     */
    public function Marketing()
    {
        return $this->getModule("Marketing");
    }

    /**
     * @return sSystem
     */
    public function System()
    {
        return $this->getModule("System");
    }

    /**
     * @return sConfigurator
     */
    public function Configurator()
    {
        return $this->getModule("Configurator");
    }

    /**
     * @return sAdmin
     */
    public function Admin()
    {
        return $this->getModule("Admin");
    }

    /**
     * @return sOrder
     */
    public function Order()
    {
        return $this->getModule("Order");
    }
}