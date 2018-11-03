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
 * Enlight.app.SubApplication
 *
 * Override the default ext application
 * to add our sub application functionality
 */
Ext.define('Enlight.app.SubApplication', {
    extend: 'Ext.app.Controller',

    requires: [
        'Ext.ModelManager',
        'Ext.data.Model',
        'Ext.data.StoreManager',
        'Ext.ComponentManager',
        'Ext.app.EventBus',
        'Ext.ZIndexManager',
        'Enlight.app.Window'
    ],

    /**
      * @cfg { Ext.app.Application } app Reference to the global Ext.app.Application instance
      */

    /**
     * Scope which will be passed to the "beforeLaunch" and "launch" method
     */
    scope             : undefined,

    /**
       * Function that will be called before the launch function but after all dependencies
       * have been loaded and Controllers created.
       * @property beforelaunch
       * @type Function
       */
    beforeLaunch : Ext.emptyFn,
     /**
       * Function that will be called after everything has been set up. Use this function
       * to create the SubApplication views.
       * NOTE: Return the main view as SubApplication will listen for it's destroy event to destroy the SubApplication.
       * @property launch
       * @type Function
       * @return { Ext.Component } Return the main view as SubApplication will listen for it's destroy event to destroy
       * the SubApplication.
       */
    launch       : Ext.emptyFn,

    /**
     * Should the sub application automatically create a instance for it's controllers.
     * @boolean
     */
    initControllers: true,

    /**
     * Property which holds off the event bus for this sub application
     * @default null
     * @object [Ext.app.EventBus]
     */
    eventbus: null,

    /**
     * Truthy to allow multiple sub windows for the sub application. Falsy to just allow one sub window opened at once.
     * @boolean
     */
    multipleSubWindows: true,

    /**
     * Sets up the sub application and merges the user configuration
     * with the default settings.
     *
     * @constructor
     * @public
     * @param [object] config - Instantiation parameters
     * @return void
     */
    constructor: function(config){
        config = config || {};

        var me          = this,
            controllers = Ext.Array.from(config.controllers);

        me.eventbus = Ext.create('Ext.app.EventBus');
        me.windowManager = Ext.create('Ext.ZIndexManager');
        me.windowManager.multipleSubWindows = me.multipleSubWindows;

        Ext.apply(config, {
            documentHead : Ext.getHead(),
            id           : config.id
        });

        Ext.apply(me, {
            appControllers : (controllers.length) ? controllers : me.controllers,
            controllers    : Ext.create('Ext.util.MixedCollection'),
            stores         : Ext.create('Ext.util.MixedCollection'),
            eventbus       : me.eventbus,
            windowManager  : me.windowManager
        });
        me.callParent(arguments);
    },

    /**
     * Proxy method which initializes the sub application if all dependency are loaded.
     *
     * @private
     * @retrun void
     */
    init: function() {
        var me = this;

        Shopware.app.Application.fireEvent('subAppLoaded', me);
        me.onBeforeLaunch();
    },

    /**
     * Adds a new controller to the sub application.
     *
     * @public
     * @param [string] controller - Name of the controller
     * @param [boolean] skipInit - Should the controller be initialized on load
     * @return [object] instance of the added controller.
     */
    addController: function(controller, skipInit) {
        var me = this,
            app = me.app,
            controllers = me.controllers,
            prefix = Ext.Loader.getPrefix(controller.name);

        controller.application = app;
        controller.subApplication = me;
        controller.id = controller.id || controller.name;

		if (prefix === '' || prefix === controller.name) {
			controller.name = this.name + '.controller.' + controller.name;
		}

		if (Ext.isDefined(controller.name)) {
            var name = controller.name;
            delete controller.name;
            controller = Ext.create(name, controller);
        }

        controller.$controllerId = Ext.id()
        controllers.add(controller.$controllerId, controller);

        if (!skipInit) {
            controller.init();
        }
        return controller;
    },

    /**
     * Removes a controller from the sub application.
     *
     * @public
     * @param [object] controller - Instance of the controller which should be removed.
     * @param [boolean] removeListeners - Truthy to remove the associated event listeners.
     */
    removeController: function(controller, removeListeners) {
        removeListeners = removeListeners || true;
        var me          = this,
            controllers = me.controllers;

        var key = controllers.indexOf(controller);
        controllers.removeAt(key);

        if (removeListeners) {
            var bus = me.eventbus;

            bus.uncontrol([controller.id]);
        }
    },

    /**
     * Adds a new sub application to the sub application stack.
     *
     * @public
     * @param [object] subapp - Instance of the sub application controller
     * @return [object] added sub application
     */
    addSubApplication: function(subapp) {
        var me      = this,
            app     = me.app,
            subapps = app.subApplications;

        subapp.$subAppId = Ext.id();
        subapps.add(subapp.$subAppId, subapp);
        return subapp;
    },

    /**
     * Removes a existing sub application to the sub application stack.
     *
     * @public
     * @param [object] subapp - Instance of the sub application controller.
     * @return void
     */
    removeSubApplication: function(subapp) {
        var me      = this,
            app     = me.app,
            subapps = app.subApplications;

        var key = subapps.indexOf(subapp);
        subapps.removeAt(key);
    },

    /**
     * Sets up the necessary objects for the current sub application,
     * builds up the window manager and binds an event listener to
     * destroy the sub application correctly if the main application
     * window will be destroyed before the sub application will be launched.
     *
     * If you want to add additional logic before the sub application will
     * be started please use the "beforeLaunch"-method which will be called
     * after the set up of the sub application is completed.
     *
     * @return void
     */
    onBeforeLaunch: function() {
        var me          = this,
            app         = me.app,
            controllers = me.appControllers,
            windowManager = me.windowManager,
            controller, cmp;

        // Check if the window manager has the "mainWindow" property
        if(!windowManager.hasOwnProperty('mainWindow')) {
            windowManager.mainWindow = null;
        }

        // Add register for all sub windows
        if(!windowManager.hasOwnProperty('subWindows')) {
            windowManager.subWindows = Ext.create('Ext.util.MixedCollection');
        }

        if (app) {
            Ext.each(controllers, function(controlName) {
                controller = me.addController({
                    name: controlName
                }, !me.initControllers);
            });
            delete me.appControllers;

            Ext.applyIf(app, {
                subApplications : Ext.create('Ext.util.MixedCollection')
            });

            me.addSubApplication(me);
        }

        if(Shopware.app.Application.moduleLoadMask) {
            Shopware.app.Application.moduleLoadMask.hide();
        }

        me.beforeLaunch.call(me.scope || me);

        cmp = me.launch.call(me.scope || me);
        if (cmp) {
            me.cmp = cmp;
            me.cmp.on('destroy', me.handleSubAppDestroy, me, { single: true });
        }
    },

    /**
     * Event listener method which will be called when the main
     * application window will be destroyed.
     *
     * This method destroyes the sub application.
     *
     * @event destroy
     * @public
     * @param [object] cmp - Enlight.app.Window which represents the main application window.
     * @return void.
     */
    handleSubAppDestroy: function(cmp) {
        var me             = this,
            controllers    = me.controllers;

        controllers.each(function(controller) {
            me.removeController(controller);
        });

        me.removeSubApplication(me);
        me.eventbus = null;
        me.windowManager = null;
        me = null;
    },

    /**
     * Returns the class name in the sub application based
     * on the given paremeters.
     *
     * Note that this method will only be used for internal purpose.
     *
     * @private
     * @param [string] name - Name of the component or resp. module
     * @param [string] type - Type of the module (e.g. store, model, view, controller)
     * @return [string] Full namespace of the module
     */
	getModuleClassName: function(name, type) {
        var namespace = Ext.Loader.getPrefix(name);

        if (namespace.length > 0 && namespace !== name) {
            return name;
        }

        return this.name + '.' + type + '.' + name;
    },

    /**
     * Returns the controller based on the given parameter. If the requested controller
     * isn't initialized, it will be initialized and returned.
     *
     * @public
     * @param [string] name - Name of the controller.
     * @return [object] instance of the controller
     */
    getController: function(name) {
        var controller = this.controllers.findBy(function(item) {
            if(item.id === name) {
                return true;
            }
        }, this);

        if (!controller) {
            return this.addController({ name: name});
        }

        return controller;
    },

    /**
     * Returns the associated store based on the given parameter. If the store
     * isn't created, it will be created on demand.
     *
     * @public
     * @param [string] name - Name of the store
     * @return [object] instance of the requested store
     */
    getStore: function(name) {
        var store = this.stores.get(name);

        if (!store) {
            store = Ext.StoreManager.get(name);
            if(store && !store.autoLoad) {
                store = null;
            }
        }

        if (!store) {
            store = Ext.create(this.getModuleClassName(name, 'store'), {
                application: this,
                storeId: name,
                id: name
            });

            this.stores.add(store);
        }
        return store;
    },

    /**
     * Returns the associated model based on the given parameter.
     *
     * @public
     * @param [string] model - Name of the model
     * @return [object] instance of the requested model
     */
    getModel: function(model) {
        model = this.getModuleClassName(model, 'model');
        return Ext.ModelManager.getModel(model);
    },

    /**
     * Returns the associated view based on the given parameter.
     *
     * @public
     * @param [string] view - Name of the view
     * @return [object] instance of the requested view
     */
    getView: function(view) {
        view = this.getModuleClassName(view, 'view');
        var cls = Ext.ClassManager.get(view);
        cls.prototype.subApp = this;
        return cls;
    },

    /**
     * Binds event listeners in the controller instead of binding
     * the event directly to the component (or it's HTML DOM elements).
     *
     * @public
     * @param { Object } selectors - Selectors to bind events on it
     * @param { Object } listeners - Associated event listeners for the selectors
     * @param { String } controller - Name of the associated controller to catch the events there
     * @return { void|Boolean }
     */
    control: function(selectors, listeners, controller) {
        if(this.hasOwnProperty('eventbus') && this.eventbus) {
            this.eventbus.control(selectors, listeners, controller);
        } else {
            return false;
        }
    },

    /**
     * Helper method which sets the main window for this sub application.
     *
     * @param [object] win - Enlight.app.Window, Ext.app.Window
     * @return [object] setted Enlight.app.Window
     */
    setAppWindow: function(win) {
        var me = this;

        // Remove all sub window specific settings from the window instance
        if(win.isSubWindow) {
            win.isSubWindow = false;
            me.windowManager.subWindows.removeAtKey(win.$subWindowId);
            delete win.$subWindowId;
        }

        // Remove the old event listener from the old window
        if(me.cmp) {
            me.un('beforeclose', me.cmp.onBeforeDestroyMainWindow, me);
            me.un('destory', me.handleSubAppDestroy, me);
        }

        // Splitting up the binding of the event in to calls, due to the different scope
        me.cmp = win;
        me.cmp.on({
            destroy: me.handleSubAppDestroy,
            scope: me
        });
        me.cmp.on({
            beforeclose: me.cmp.onBeforeDestroyMainWindow,
            scope: me.cmp
        });

        win.mainWindow = true;
        me.windowManager.bringToFront(win);
        return me.windowManager.mainWindow = win;
    }
});

Ext.Class.registerPreprocessor('shopware.subappLoader', function(cls, data, hooks, fn) {
    var className = Ext.getClassName(cls),
        match = className.match(/^(Shopware|Enlight)\.controller\.|(.*)\.apps\./),
        requires = [],
        modules = ['model', 'view', 'store', 'controller'],
        prefix;

    if (!data.hasOwnProperty('extend') || data.extend.prototype.$className != 'Enlight.app.SubApplication' || match === null) {
        return true;
    }

    var i, ln, module,
        items, j, subLn, item;

    if(data.name === undefined) {
        data.name = className;
    }

    if(data.loadPath !== undefined) {
        Ext.Loader.setPath(data.name, data.loadPath, '', data.bulkLoad);
    }

    for (i = 0,ln = modules.length; i < ln; i++) {
        module = modules[i];


        items = Ext.Array.from(data[module + 's']);

        for (j = 0,subLn = items.length; j < subLn; j++) {
            item = items[j];

            prefix = Ext.Loader.getPrefix(item);
            if (prefix === '' || prefix === item) {
                requires.push(data.name + '.' + module + '.' + item);
            } else {
                requires.push(item);
            }
        }
    }
    Ext.require(requires, Ext.pass(fn, [cls, data, hooks], this));
    return false;
}, true, 'after', 'loader');
