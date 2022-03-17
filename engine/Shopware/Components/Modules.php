<?php

declare(strict_types=1);
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
     * @var array<string, object|null>
     */
    protected $modules_container = [];

    /**
     * @param string     $name
     * @param mixed|null $value
     */
    public function __call($name, $value = null)
    {
        return $this->getModule($name);
    }

    /**
     * Set class property
     *
     * @param \sSystem $system
     *
     * @return void
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
        if (str_starts_with($name, 's')) {
            $name = substr($name, 1);
        }
        if ($name !== 'RewriteTable') {
            $className = 's' . ucfirst(strtolower($name));
        } else {
            $className = 's' . $name;
        }

        /** @var class-string $className */
        if (!isset($this->modules_container[$className])) {
            $this->loadModule($className);
        }

        return $this->modules_container[$className];
    }

    /**
     * @param string|mixed $offset module name
     * @param mixed        $value  module
     *
     * @return void
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
    }

    /**
     * @param string|mixed $offset module name
     *
     * @return bool
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return (bool) $this->getModule($offset);
    }

    /**
     * @param string|mixed $offset module name
     *
     * @return void
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
    }

    /**
     * @param string|mixed $offset module name
     *
     * @return mixed module
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[\ReturnTypeWillChange]
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
     */
    private function loadModule(string $name): void
    {
        if (isset($this->modules_container[$name])) {
            return;
        }

        $this->modules_container[$name] = null;
        /** @var class-string $name */
        $name = basename($name);

        if ($name === 'sSystem') {
            $this->modules_container[$name] = $this->system;

            return;
        }

        Shopware()->Hooks()->setAlias($name, $name);
        $proxy = Shopware()->Hooks()->getProxy($name);
        $this->modules_container[$name] = new $proxy();
        if (property_exists($this->modules_container[$name], 'sSYSTEM')) {
            $this->modules_container[$name]->sSYSTEM = $this->system;
        }
    }
}
