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
//{block name="backend/base/component/preloader"}
Ext.define('Shopware.component.Preloader', {

    /**
     * The active priority. The priority will be needed to
     * support priority preloading
     *
     * @default 0
     * @integer
     */
    activePrio: 0,

    /**
     * Component which fires the events to start the preloading
     *
     * @default undefined
     * @object
     */
    eventProvider: undefined,

    /**
     * Is the preloading process finished
     *
     * @default false
     * @boolean
     */
    finished: false,

    /**
     * Required components which needs to be preloaded.
     * The components / modules are loaded based on the priority.
     *
     * @array
     */
    requiredComponents: [{
        /*{if {acl_is_allowed resource=article privilege=read}}*/
        'Shopware.apps.Article': false,
        /*{/if}*/
        /*{if {acl_is_allowed resource=articlelist privilege=read}}*/
        'Shopware.apps.ArticleList': false,
        /*{/if}*/
        /*{if {acl_is_allowed resource=order privilege=read}}*/
        'Shopware.apps.Order': false,
        /*{/if}*/
        /*{if {acl_is_allowed resource=customer privilege=read}}*/
        'Shopware.apps.Customer': false,
        /*{/if}*/

        'Shopware.apps.ProductStream': false
    }, {
        'Shopware.apps.Emotion': false,
        'Shopware.apps.MediaManager': false
    }, {
        'Shopware.apps.Blog': false
    }],

    /**
     * Binds the necessary event listener to trigger the preloading.
     *
     * @public
     * @param cmp - Component which provides the events
     * @return void
     */
    bindEvents: function(cmp) {
        var me = this;
        me.eventProvider = cmp;
        me.eventProvider.on('baseComponentsReady', me.onBaseComponentsReady, me, { single: true });
        me.eventProvider.on('subAppLoaded', me.onSubAppLoaded, me);
    },

    /**
     * Event listener method which will be fired when the [eventProvider]
     * fires the "baseComponentsReady" event.
     *
     * The method triggers the preloading.
     *
     * @event baseComponentsReady
     * @return void
     */
    onBaseComponentsReady: function() {
        var me = this;
        me.triggerLoadPrio();
    },

    /**
     * Event listener method which will be fired when the [eventProvider]
     * triggers that a new sub application was loaded successfully.
     *
     * Handles the priority management and removes the event listener
     * if all required components / modules are loaded.
     *
     * @event subAppLoaded
     * @param subApp - Instance of the loaded sub application
     * @return void
     */
    onSubAppLoaded: function(subApp) {
        var me = this,
            clsName = subApp.$className;

        if(me.finished) {
            return;
        }

        if(!me.requiredComponents[me.activePrio].hasOwnProperty(clsName)) {
            return;
        }
        me.requiredComponents[me.activePrio][clsName] = true;

        if(me.isPrioLoaded(me.activePrio)) {
            me.activePrio += 1;
            if(!me.requiredComponents[me.activePrio]) {
                me.finished = true;
                me.eventProvider.removeListener('subAppLoaded', me.onSubAppLoaded, me);
                return;
            }
            me.triggerLoadPrio();
        }
    },

    /**
     * Checks if the given priority group key are loaded.
     *
     * @param prio - key of the priority group
     * @return boolean
     */
    isPrioLoaded: function(prio) {
        var me = this,
            prioGroup = me.requiredComponents[prio],
            allLoaded = true;

        Ext.iterate(prioGroup, function(index, item) {
            if(!item) {
                allLoaded = false;
                return false;
            }
        });
        return allLoaded;
    },

    /**
     * Iterates through a list of modules and preloads
     * them based on the priority list.
     *
     * @return void
     */
    triggerLoadPrio: function() {
        var me = this,
            prioGroup = me.requiredComponents[me.activePrio];

        Ext.iterate(prioGroup, function(name, item) {
            if(!Ext.ClassManager.isCreated(name)) {
                Ext.require(name);
            }
        });
    }
});
//{/block}
