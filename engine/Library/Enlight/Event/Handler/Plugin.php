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
 * Event handler for Enlight plugins.
 *
 * The Enlight_Event_Handler_Plugin is the basic class for plugin events. It extends the default enlight event
 * handler with plugin specified methods.
 *
 * @category   Enlight
 * @package    Enlight_Event
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Event_Handler_Plugin extends Enlight_Event_Handler
{
    /**
     * @var string contains the event listener
     */
    protected $listener;

    /**
     * @var Enlight_Plugin_Namespace contains an instance of the Enlight_Plugin_Namespace
     */
    protected $namespace;

    /**
     * @deprecated Will be only `string` starting from Shopware 5.8
     *
     * @var Enlight_Plugin_Bootstrap|string contains an instance
     *                                      of the Enlight_Plugin_Bootstrap or the plugin name
     */
    protected $plugin;

    /**
     * The Enlight_Event_Handler_Plugin class constructor expects the event name.
     * All parameters are set in the internal properties.
     *
     * @deprecated The parameter $plugin will only accept `string` starting from Shopware 5.8 and all parameters will be strongly typed will not be nullable anymore
     *
     * @param string                               $event
     * @param ?Enlight_Plugin_Namespace            $namespace
     * @param Enlight_Plugin_Bootstrap|string|null $plugin
     * @param ?string                              $listener
     * @param ?int                                 $position
     *
     * @throws Enlight_Event_Exception
     */
    public function __construct($event, $namespace = null, $plugin = null, $listener = null, $position = null)
    {
        if ($namespace !== null) {
            $this->setNamespace($namespace);
        }
        if ($plugin !== null) {
            $this->setPlugin($plugin);
        }
        if ($listener !== null) {
            $this->setListener($listener);
        }
        parent::__construct($event);
        $this->setPosition((int) $position);
    }

    /**
     * Setter method for the internal plugin property.
     *
     * @param mixed $plugin Enlight_Plugin_Bootstrap|string
     *
     * @return Enlight_Event_Handler_Plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * Getter method for the internal plugin property. If the plugin property is a string,
     * enlight determines the plugin object over the namespace.
     *
     * @return ?Enlight_Plugin_Bootstrap
     */
    public function Plugin()
    {
        // In the past always `null` was returned if $this->plugin was of instance `Enlight_Plugin_Bootstrap`
        // therefore this bug is replicated here, can be removed once the type has been fixed to `string`
        if ($this->plugin instanceof Enlight_Plugin_Bootstrap) {
            return null;
        }

        $plugin = $this->namespace->get($this->plugin);
        if (!$plugin instanceof Enlight_Plugin_Bootstrap) {
            return null;
        }

        return $plugin;
    }

    /**
     * Setter method for the internal listener property.
     *
     * @param string $listener
     *
     * @return Enlight_Event_Handler_Plugin
     */
    public function setListener($listener)
    {
        $this->listener = $listener;

        return $this;
    }

    /**
     * @return string
     */
    public function getListener()
    {
        return $this->listener;
    }

    /**
     * Setter method for the internal namespace property.
     *
     * @return Enlight_Event_Handler_Plugin
     */
    public function setNamespace(Enlight_Plugin_Namespace $namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Executes the listener on the plugin with the given Enlight_Event_EventArgs.
     *
     * @throws Enlight_Exception
     */
    public function execute(Enlight_Event_EventArgs $args)
    {
        $plugin = $this->Plugin();
        if ($plugin === null || !method_exists($plugin, $this->listener)) {
            $name = $this->plugin instanceof Enlight_Plugin_Bootstrap ? $this->plugin->getName() : $this->plugin;
            trigger_error('Listener "' . $this->listener . '" in "' . $name . '" is not callable.', E_USER_ERROR);
            // throw new Enlight_Exception('Listener "' . $this->listener . '" in "' . $name . '" is not callable.');
            return;
        }

        return $this->Plugin()->{$this->listener}($args);
    }

    /**
     * Returns the plugin handler properties as an array.
     *
     * @return array{name: string, position: int, plugin: ?string, listener: string}
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'position' => $this->position,
            'plugin' => $this->plugin instanceof Enlight_Plugin_Bootstrap ? $this->plugin->getName() : $this->plugin,
            'listener' => $this->listener,
        ];
    }
}
