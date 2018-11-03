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
 * The Enlight_Plugin_PluginCollection provides an array containing each registered plugin.
 *
 * The Enlight_Plugin_PluginCollection is an array for each registered plugin.
 * If a Enlight_Plugin_Bootstrap is registered over the Enlight_Plugin_PluginCollection, references are
 * added to the Enlight_Plugin_Bootstrap.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Plugin_PluginCollection extends Enlight_Class implements IteratorAggregate
{
    /**
     * Property which contains all registered plugins.
     *
     * @var ArrayObject
     */
    protected $plugins;

    /**
     * The Enlight_Plugin_PluginCollection class constructor initials the internal plugin list.
     */
    public function __construct()
    {
        $this->plugins = new ArrayObject();
        parent::__construct();
    }

    /**
     * Magic caller
     *
     * @param string     $name
     * @param array|null $args
     *
     * @return \Enlight_Plugin_Bootstrap|\Enlight_Plugin_Namespace
     */
    public function __call($name, $args = null)
    {
        return $this->get($name, true);
    }

    /**
     * Returns the application instance.
     *
     * @return Shopware
     */
    abstract public function Application();

    /**
     * Registers the given plugin bootstrap. The Enlight_Plugin_PluginCollection instance is
     * set into the plugin by using the Enlight_Plugin_Bootstrap::setCollection() method.
     * The name of the plugin is used as array key.
     *
     * @param Enlight_Plugin_Bootstrap $plugin
     *
     * @return Enlight_Plugin_PluginManager
     */
    public function registerPlugin(Enlight_Plugin_Bootstrap $plugin)
    {
        $plugin->setCollection($this);
        $this->plugins[$plugin->getName()] = $plugin;

        return $this;
    }

    /**
     * Getter method for the plugin list.
     *
     * @return ArrayObject
     */
    public function getIterator()
    {
        return $this->plugins;
    }

    /**
     * Returns a plugin by name. If the plugin isn't registered, the Enlight_Plugin_PluginCollection
     * loads it automatically.
     *
     * @param      $name
     * @param bool $throwException
     *
     * @return \Enlight_Plugin_Bootstrap|\Enlight_Plugin_Namespace
     */
    public function get($name, $throwException = false)
    {
        if (!$this->plugins->offsetExists($name)) {
            $this->load($name, $throwException);
        }
        if ($this->plugins->offsetExists($name)) {
            return $this->plugins->offsetGet($name);
        }

        return null;
    }

    /**
     * Loads the plugin instance of the given plugin name.
     *
     * @param   $name
     * @param   $throwException
     *
     * @throws Enlight_Exception
     *
     * @return Enlight_Plugin_PluginCollection
     */
    public function load($name, $throwException = true)
    {
        if ($throwException && !$this->plugins->offsetExists($name)) {
            throw new Enlight_Exception('Plugin "' . $name . '" not found failure');
        }

        return $this;
    }

    /**
     * Removes all stored plugins.
     *
     * @return Enlight_Plugin_PluginManager
     */
    public function reset()
    {
        $this->plugins->exchangeArray([]);

        return $this;
    }
}
