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

/**
 * Basic Enlight view class for each specified view component.
 *
 * The Enlight_View defines the interface for the view within a controller.
 * If you want to implement your own view class then you have to implement this interface.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_View
{
    /**
     * Magic setter
     *
     * @param       $name
     * @param mixed $value
     */
    public function __set($name, $value = null)
    {
        $this->assign($name, $value);
    }

    /**
     * Magic getter
     *
     * @param string $name
     *
     * @return array|mixed
     */
    public function __get($name)
    {
        return $this->getAssign($name);
    }

    /**
     * Magic isset
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->getAssign($name) !== null;
    }

    /**
     * Magic unset
     *
     * @param string $name
     */
    public function __unset($name)
    {
        $this->clearAssign($name);
    }

    /**
     * Sets the template path list.
     *
     * @param string|array $path
     *
     * @return Enlight_View
     */
    abstract public function setTemplateDir($path);

    /**
     * Adds a path to the template list.
     *
     * @param string|array $path
     *
     * @return Enlight_View
     */
    abstract public function addTemplateDir($path);

    /**
     * Checks if a template is stored.
     *
     * @return bool
     */
    abstract public function hasTemplate();

    /**
     * Assigns a specified value to the template.
     *
     * @param string $spec
     * @param mixed  $value
     * @param bool   $nocache
     * @param int    $scope
     *
     * @return Enlight_View
     */
    abstract public function assign($spec, $value = null, $nocache = false, $scope = null);

    /**
     * Resets a specified value or all values.
     *
     * @param string $spec
     *
     * @return Enlight_View
     */
    abstract public function clearAssign($spec = null);

    /**
     * Returns a specified value or all values.
     *
     * @param string|null $spec
     *
     * @return mixed|array
     */
    abstract public function getAssign($spec = null);

    /**
     * Renders the current template.
     *
     * @return string
     */
    abstract public function render();
}
