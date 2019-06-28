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
 * Ext.app.Controller
 *
 * Override the default ext application
 * to add our sub application functionality
 */
//{block name="extjs/overrides/controller"}
Ext.override(Ext.app.Controller, {

    /**
     * Returns instance of a { @link Ext.app.Controller controller } with the given name.
     * When controller doesn't exist yet, it's created.
     * @param { String } name
     * @return { Ext.app.Controller } a controller instance.
     */
    getController: function(name) {
        if(this.subApplication) {
            return this.subApplication.getController(name);
        }
        return this.callParent(arguments);
    },

    /**
     * Returns instance of a { @link Ext.data.Store Store } with the given name.
     * When store doesn't exist yet, it's created.
     * @param { String } name
     * @return { Ext.data.Store } a store instance.
     */
    getStore: function(name) {
        if(this.subApplication) {
            return this.subApplication.getStore(name);
        }
        return this.callParent(arguments);
    },

    /**
     * Returns a { @link Ext.data.Model Model } class with the given name.
     * A shorthand for using { @link Ext.ModelManager#getModel }.
     * @param { String } name
     * @return { Ext.data.Model } a model class.
     */
    getModel: function(model) {
        if(this.subApplication) {
            return this.subApplication.getModel(model);
        }
        return this.callParent(arguments);
    },

    /**
     * Returns a View class with the given name.  To create an instance of the view,
     * you can use it like it's used by Application to create the Viewport:
     *
     *     this.getView('Viewport').create();
     *
     * @param { String } name
     * @return { Ext.Base } a view class.
     */
    getView: function(view) {
        if(this.subApplication) {
            return this.subApplication.getView(view);
        }
        return this.callParent(arguments);
    },

    /**
     * Adds listeners to components selected via Ext.ComponentQuery. Accepts an
     * object containing component paths mapped to a hash of listener functions.
     *
     * @param [string|object] selectors If a String, the second argument is used as the
     * listeners, otherwise an object of selectors -> listeners is assumed
     * @param [object] listeners
     */
    control: function(selectors, listeners) {
        var me = this;

        /**
         * If the controller is associated with a sub application (e.g. it's a module)
         * we're using the event bus which is associated with the sub application.
         *
         * Otherwise we're using the default behavior and bind the events globally
         */
        if(me.subApplication) {
            me.subApplication.control(selectors, listeners, me);
        } else {
            me.application.control(selectors, listeners, me);
        }
    },

    /**
     * Gets the component based on the given reference name.
     *
     * Note that this method returns always the reference from
     * the active window. If there's no active window, it's returns
     * the first founded reference.
     *
     * @private
     * @param [string] ref - Reference to found
     * @param [object] info - Informations about the reference
     * @param [object] config - reference config
     * @return [object] founded refernce
     */
    getRef: function(ref, info, config) {
        this.refCache = this.refCache || {};
        info = info || {};
        config = config || {};

        Ext.apply(info, config);

        if (info.forceCreate) {
            return Ext.ComponentManager.create(info, 'component');
        }

        var me = this,
            cached;

        // Disble caching of the references
        me.refCache[ref] = cached = me.getActiveReference(info.selector);
        if (!cached && info.autoCreate) {
            me.refCache[ref] = cached = Ext.ComponentManager.create(info, 'component');
        }
        if (cached) {
            cached.on('beforedestroy', function() {
                me.refCache[ref] = null;
            });
        }
        return cached;
    },

    /**
     * Helper method which terminates the active refernce based on
     * the active window.
     *
     * @private
     * @param [string] selector - Simple selector to test
     * @return [object] founded component for the given selector
     */
    getActiveReference: function(selector) {
        var me = this,
            subApp = me.subApplication,
            refs = Ext.ComponentQuery.query(selector),
            windowManager, activeRef;

        // Controller is not part of a sub application
        if(!subApp) {
            return refs[0];
        }

        // If the window manager exists. If not we're returning the first founded reference
        windowManager = subApp.windowManager;
        if(!windowManager) {
            return refs[0];
        }
        activeRef = windowManager.getActive();

        // If the ref is a window, get the active one and return this window
        var returnRef = me.getActiveWindowReference(refs, activeRef);
        if(returnRef) {
            return returnRef;
        }

        // The ref isn't a window, so we need to terminate the active window
        // and return the associated ref
        Ext.each(refs, function(ref) {
            if(returnRef) return false;

            var win = ref.up('window');
            if(!win) return false;

            var foundedWindow = me.getActiveWindowReference(win, activeRef);
            if(foundedWindow) {
                returnRef = ref;
                return false;
            }
        });

        return returnRef || refs[0];
    },

    /**
     * Helper method which gets the refernce which is in the active window.
     *
     * @private
     * @param [object] refs - founded refernces
     * @param [object] activeRef - the active refernce (e.g. the window)
     * @return [boolean]
     */
    getActiveWindowReference: function(refs, activeRef) {
        var returnRef = false;
        Ext.each(refs, function(ref) {
            if(returnRef) {
                return false;
            }
            if(ref === activeRef) {
                returnRef = ref;
                return false;
            }
        });

        return returnRef;
    }
});
//{/block}