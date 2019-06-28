<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

class Shopware_Components_Modules extends Enlight_Class implements ArrayAccess
{
    /**
     * @var \sSystem
     */
    protected $system;

    /**
     * Container that hold references to all modules already loaded
     *
     * @var array
     */
    protected $modules_container = [];

    /**
     * @param string $name
     */
    public function __call($name, $value = null)
    {
        return $this->getModule($name);
    }

    /**
     * Set class property
     *
     * @param \sSystem $system
     */
    public function setSystem($system)
    {
        $this->system = $system;
    }

    /**
     * Reformat module name and return reference to module
     *
     * @param string $name
     */
    public function getModule($name)
    {
        if (strpos($name, 's') === 0) {
            $name = substr($name, 1);
        }
        if ($name !== 'RewriteTable') {
            $name = 's' . ucfirst(strtolower($name));
        } else {
            $name = 's' . $name;
        }

        if (!isset($this->modules_container[$name])) {
            $this->loadModule($name);
        }

        return $this->modules_container[$name];
    }

    /**
     * @param string $offset
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return (bool) $this->getModule($offset);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * @param string $offset
     */
    public function offsetGet($offset)
    {
        return $this->getModule($offset);
    }

    /**
     * @return sArticles
     */
    public function Articles()
    {
        return $this->getModule('Articles');
    }

    /**
     * @return sCategories
     */
    public function Categories()
    {
        return $this->getModule('Categories');
    }

    /**
     * @return sBasket
     */
    public function Basket()
    {
        return $this->getModule('Basket');
    }

    /**
     * @return sMarketing
     */
    public function Marketing()
    {
        return $this->getModule('Marketing');
    }

    /**
     * @return \sSystem
     */
    public function System()
    {
        return $this->getModule('System');
    }

    /**
     * @return sAdmin
     */
    public function Admin()
    {
        return $this->getModule('Admin');
    }

    /**
     * @return sOrder
     */
    public function Order()
    {
        return $this->getModule('Order');
    }

    /**
     * @return sCms
     */
    public function Cms()
    {
        return $this->getModule('Cms');
    }

    /**
     * @return sCore
     */
    public function Core()
    {
        return $this->getModule('Core');
    }

    /**
     * @return sRewriteTable
     */
    public function RewriteTable()
    {
        return $this->getModule('RewriteTable');
    }

    /**
     * @return sExport
     */
    public function Export()
    {
        return $this->getModule('Export');
    }

    /**
     * Load a module defined by $name
     * Possible values for $name - sBasket, sAdmin etc.
     *
     * @param string $name
     */
    private function loadModule($name)
    {
        if (isset($this->modules_container[$name])) {
            return;
        }

        $this->modules_container[$name] = null;
        $name = basename($name);

        if ($name === 'sSystem') {
            $this->modules_container[$name] = $this->system;

            return;
        }

        Shopware()->Hooks()->setAlias($name, $name);
        $proxy = Shopware()->Hooks()->getProxy($name);
        $this->modules_container[$name] = new $proxy();
        $this->modules_container[$name]->sSYSTEM = $this->system;
    }
}
