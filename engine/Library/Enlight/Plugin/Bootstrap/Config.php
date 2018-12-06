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
 * The Enlight_Plugin_Bootstrap_Config contains the configs of a single plugin.
 *
 * The Enlight_Plugin_Bootstrap_Config is the configuration class for a single plugin.
 * The Enlight_Config will be loaded by the Enlight_Plugin_Namespace_Config.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Plugin_Bootstrap_Config extends Enlight_Plugin_Bootstrap
{
    /**
     * @var Enlight_Config instance of the Enlight_Config, loaded by the Enlight_Plugin_Namespace_Config
     */
    protected $config;

    /**
     * @var Enlight_Plugin_Namespace_Config instance of the Enlight_Plugin_Namespace_Config,
     *                                      which was passed to the class constructor
     */
    protected $collection;

    /**
     * Returns the instance of the Enlight_Config.
     *
     * @return Enlight_Config
     */
    public function Config()
    {
        if ($this->config === null
          && $this->collection instanceof Enlight_Plugin_Namespace_Config) {
            $this->config = $this->collection->getConfig($this->getName());
        }

        return $this->config;
    }

    /**
     * Getter method for the collection property. Contains an instance of the Enlight_Plugin_Namespace_Config.
     *
     * @return Enlight_Plugin_Namespace_Config
     */
    public function Collection()
    {
        return $this->collection;
    }

    /**
     * Subscribes a plugin event.
     *
     * The given parameters and the internal instance of the Enlight_Plugin_Namespace_Config
     * are used to instantiate a new Enlight_Event_Handler_Plugin.
     * This Enlight_Event_Handler_Plugin is subscribed over the namespace subscriber.
     *
     * @param string   $event
     * @param callable $listener
     * @param int      $position
     *
     * @return Enlight_Plugin_Bootstrap_Config
     */
    public function subscribeEvent($event, $listener, $position = null)
    {
        $namespace = $this->Collection();
        $handler = new Enlight_Event_Handler_Plugin(
            $event, $namespace, $this, $listener, $position
        );
        $namespace->Subscriber()->registerListener($handler);

        return $this;
    }

    /**
     * This function installs the plugin.
     *
     * @return bool
     */
    public function install()
    {
    }
}
