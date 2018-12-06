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
