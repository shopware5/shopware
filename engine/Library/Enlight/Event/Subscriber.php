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
 * Basic class for each Enlight subscriber.
 *
 * The Enlight_Event_Subscriber is the basic class for each specified event subscriber (array, config, plugin)
 * To implement an own subscriber the Enlight_Event_Subscriber must be extended.
 *
 * @category   Enlight
 * @package    Enlight_Event
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 *
 * @deprecated in Shopware 5.7, will be internal in 5.8. Please use `SubscriberInterface::getSubscribedEvents` instead.
 */
abstract class Enlight_Event_Subscriber extends Enlight_Class
{
    /**
     * Retrieves a list of listeners registered to a given event.
     *
     * @return list<\Enlight_Event_Handler>
     */
    abstract public function getListeners();

    /**
     * Registers a listener to an event.
     *
     * @return Enlight_Event_Subscriber
     */
    abstract public function registerListener(Enlight_Event_Handler $handler);

    /**
     * Removes an event listener.
     *
     * @return Enlight_Event_Subscriber
     */
    abstract public function removeListener(Enlight_Event_Handler $handler);
}
