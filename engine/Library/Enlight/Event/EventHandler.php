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
 * @category   Enlight
 * @package    Enlight_Event
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @deprecated in Shopware 5.6, will be removed in 5.8. Please use `Enlight_Event_Handler_Default` or `SubscriberInterface::getSubscribedEvents` instead.
 */
class Enlight_Event_EventHandler extends Enlight_Event_Handler_Default
{
    protected $plugin;

    public function __construct($event, $listener, $position = null, $plugin = null)
    {
        parent::__construct($event, $listener, $position);
        $this->setPlugin($plugin);
    }

    /**
     * @return  array
     */
    public function toArray()
    {
        $listener = $this->listener;
        if (is_array($listener)) {
            if ($listener[0] instanceof Enlight_Singleton) {
                $listener[0] = get_class($listener[0]);
            }
            $listener = implode('::', $listener);
        }
        return array(
            'name' => $this->name,
            'listener' => $listener,
            'position' => $this->position
        );
    }

    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
        return $this;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }
}
