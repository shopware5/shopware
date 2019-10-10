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
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Event_Handler_Plugin extends Enlight_Event_Handler
{
    /**
     * @var string Contains the event listener.
     */
    protected $listener;

    /**
     * @var Enlight_Plugin_Namespace Contains an instance of the Enlight_Plugin_Namespace.
     */
    protected $namespace;

    /**
     * @var Enlight_Plugin_Bootstrap|string Contains an instance
     * of the Enlight_Plugin_Bootstrap or the plugin name.
     */
    protected $plugin;

    /**
     * The Enlight_Event_Handler_Plugin class constructor expects the event name.
     * All parameters are set in the internal properties.
     *
     * @throws  Enlight_Event_Exception
     * @param   string                   $event
     * @param   Enlight_Plugin_Namespace $namespace
     * @param   Enlight_Plugin_Bootstrap $plugin
     * @param   string                   $listener
     * @param   integer                  $position
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
        $this->setPosition($position);
    }

    /**
     * Setter method for the internal plugin property.
     * @param   $plugin Enlight_Plugin_Bootstrap|string
     * @return  Enlight_Event_Handler_Plugin
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
     * @return  Enlight_Plugin_Bootstrap
     */
    public function Plugin()
    {
        if (!$this->plugin instanceof Enlight_Plugin_Bootstrap) {
            $plugin = $this->namespace->get($this->plugin);
        }
        return $plugin;
    }

    /**
     * Setter method for the internal listener property.
     * @param   string $listener
     * @return  Enlight_Event_Handler_Plugin
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
     * @param   Enlight_Plugin_Namespace $namespace
     * @return  Enlight_Event_Handler_Plugin
     */
    public function setNamespace(Enlight_Plugin_Namespace $namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Executes the listener on the plugin with the given Enlight_Event_EventArgs.
     * @param   Enlight_Event_EventArgs $args
     * @throws  Enlight_Exception
     * @return  mixed
     */
    public function execute(Enlight_Event_EventArgs $args)
    {
        $plugin = $this->Plugin();
        if (!method_exists($plugin, $this->listener)) {
            $name = $this->plugin instanceof Enlight_Plugin_Bootstrap ? $this->plugin->getName() : $this->plugin;
            trigger_error('Listener "' . $this->listener . '" in "' . $name . '" is not callable.', E_USER_ERROR);
            //throw new Enlight_Exception('Listener "' . $this->listener . '" in "' . $name . '" is not callable.');
            return;
        }
        return $this->Plugin()->{$this->listener}($args);
    }

    /**
     * Returns the plugin handler properties as an array.
     * @return array
     */
    public function toArray()
    {
        return array(
            'name' => $this->name,
            'position' => $this->position,
            'plugin' => $this->plugin instanceof Enlight_Plugin_Bootstrap ? $this->plugin->getName() : $this->plugin,
            'listener' => $this->listener
        );
    }
}
