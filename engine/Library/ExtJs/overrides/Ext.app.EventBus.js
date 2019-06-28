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
 * Ext.app.Eventbus - Override
 *
 * This overrides adds our sub application logic into
 * the event bus and terminates the currently active event
 * bus if a event will be fired.
 *
 * Please note that this class is part of framework and
 * don't need to be created manually.
 *
 * @private
 * @class Collects and handles all controller related events.
 * @category   Enlight
 * @package    Enlight_ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

//{block name="extjs/overrides/eventBus"}
Ext.override(Ext.app.EventBus, {

    /**
     * Dispatches all fired events to terminate if the event is binded to a controller.
     *
     * Note that we're working with multiple applications (so called sub application or sub app).
     * The method terminates which sub application is active (e.g. in focus) and just fires
     * events to it's associated event bus to prevent multiple firing of events and the thereof
     * produced errors.
     *
     * @private
     * @param [string] ev - Event name
     * @param [object] target - HTML DOM element which has fired the event
     * @param [object] args - additional event paramerters
     * @return [boolean] Truthy to indicate that the event wasn't found in the event bus or falsy if the event
     * was found in the active event bus.
     */
    dispatch: function(ev, target, args) {
       var bus = this.bus,
           selectors = bus[ev],
           selector, controllers, id, events, event, i, ln;

        /**
         * Terminates the current active window and the associated
         * sub application to get the correct event bus.
         *
         * We need to place that kind of nifty code into a try & catch
         * to prevent errors to be thrown.
         */
       if(target && typeof(target.up) === 'function') {
           try {
               var win = (target.isMainWindow || target.isSubWindow ? target : target.up('window'));
               if(win && win.subApplication) {
                   bus = win.subApplication.eventbus.bus;
                   selectors = bus[ev];
               } else {
                   var pnl = target.up('panel') || target;
                   if(pnl && pnl.subApplication) {
                       bus = pnl.subApplication.eventbus.bus;
                       selectors = bus[ev];
                   }
               }
           } catch(e) { }
       }

       if (selectors) {
           // Loop over all the selectors that are bound to this event
           for (selector in selectors) {
               // Check if the target matches the selector
               if (selectors.hasOwnProperty(selector) && target.is(selector)) {
                   // Loop over all the controllers that are bound to this selector
                   controllers = selectors[selector];
                   for (id in controllers) {
                       if (controllers.hasOwnProperty(id)) {
                           // Loop over all the events that are bound to this selector on this controller
                           events = controllers[id];
                           for (i = 0, ln = events.length; i < ln; i++) {
                               event = events[i];
                               // Fire the event!
                               if (event.fire.apply(event, Array.prototype.slice.call(args, 1)) === false) {
                                   return false;
                               }
                           }
                       }
                   }
               }
           }
       }
       return true;
   },

    /**
     * Unbind the events from the active event bus based on the passed
     * controller array.
     *
     * Please note that the sub application handles the destroying or
     * unbinding of controllers and events, so you don't need to call
     * this method manually.
     *
     * @private
     * @param [array] controllerArray - Array of controllers
     * @return void
     */
    uncontrol: function(controllerArray) {
        var me  = this,
            bus = me.bus,
            deleteThis, idx;

        Ext.iterate(bus, function(ev, controllers) {
            Ext.iterate(controllers, function(query, controller) {
                deleteThis = false;

                Ext.iterate(controller, function(controlName) {
                    idx = controllerArray.indexOf(controlName);

                    if (idx >= 0) {
                        deleteThis = true;
                    }
                });

                if (deleteThis) {
                    delete controllers[query];
                }
            });
        });
    }
});
//{/block}