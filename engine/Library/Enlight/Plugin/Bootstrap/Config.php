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
     * @param string|Enlight_Event_Handler|Enlight_Hook_HookHandler $event
     * @param string|callable|null         $listener
     * @param int                          $position
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
