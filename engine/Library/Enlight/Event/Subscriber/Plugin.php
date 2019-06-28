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
 * Enlight plugin event subscriber.
 *
 * The Enlight_Event_Subscriber_Plugin is a collection to manage multiple event handlers within a plugin.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Event_Subscriber_Plugin extends Enlight_Event_Subscriber_Config
{
    /**
     * @var Enlight_Plugin_Namespace Contains an instance of the Enlight_Plugin_Namespace.
     *                               Will be set in the class constructor.
     */
    protected $namespace;

    /**
     * The Enlight_Event_Subscriber_Plugin class constructor expects an instance of the Enlight_Plugin_Namespace.
     *
     * @param      $namespace
     * @param null $options
     */
    public function __construct($namespace, $options = null)
    {
        $this->namespace = $namespace;
        parent::__construct($options);
    }

    /**
     * Writes all listeners to the storage.
     *
     * @return Enlight_Event_Subscriber_Config
     */
    public function write()
    {
        $this->storage->listeners = $this->toArray();
        $this->storage->write();

        return $this;
    }

    /**
     * Loads the event listener from storage.
     *
     * @return Enlight_Event_Subscriber_Config
     */
    public function read()
    {
        $this->listeners = [];

        if ($this->storage->listeners !== null) {
            foreach ($this->storage->listeners as $entry) {
                if (!$entry instanceof Enlight_Config) {
                    continue;
                }
                $this->listeners[] = new Enlight_Event_Handler_Plugin(
                    $entry->name,
                    $this->namespace,
                    $entry->plugin,
                    $entry->listener,
                    $entry->position
                );
            }
        }

        return $this;
    }

    /**
     * Returns all listeners as array.
     *
     * @return array
     */
    public function toArray()
    {
        $listeners = [];
        /** @var Enlight_Event_Handler_Plugin $handler */
        foreach ($this->listeners as $handler) {
            $listeners[] = $handler->toArray();
        }

        return $listeners;
    }
}
