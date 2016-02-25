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
 * Shopware UI - Window
 *
 * This class overrides the default Ext.window.Window object
 * to add our necessary functionality.
 *
 * The class renders the Ext.window.Window in the active
 * desktop, renders the header tools and sets the needed
 * event listeners.
 */
Ext.define('Enlight.app.Window', {
    ui: 'default',
    width: 800,
    height: 600,
    maximizable: true,
    minimizable: true,
    stateful: true,
    border: false,
    minimized: false,
    focusable: true,

    closePopupTitle: 'Close module',
    closePopupMessage: 'This will close all windows of the "__MODULE__" module. Do you want to continue?',

    /**
     * Property which indicates that the window should first just set to hidden before destroying it.
     * @boolean
     */
    hideOnClose: true,

    /**
     * Forces the window to be on front at start up
     * @boolean
     */
    forceToFront: false,

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.window.Window',

    /**
     * Requires classes which needs to be initialized before the initComponent
     * of this component is fired.
     * @array
     */
    requires: [ 'Ext.WindowManager' ],

    /**
     * Should a footer button be created
     *
     * @boolean
     */
    footerButton: true,

    /**
     * Property which indicates that the window is on the front.
     * @boolean
     */
    isWindowOnFront: false,

    /**
     * CSS class if the window is active
     * @string
     */
    activeCls: Ext.baseCSSPrefix + 'window-active',

    /**
     * Truthy when this component is the main window of the sub application.
     * @boolean
     */
    isMainWindow: false,

    /**
     * Truthy when this component is the sub window of the sub application.
     * @boolean
     */
    isSubWindow: false,

    /**
     * Whether or not the initial window position should be centered in the current desktop.
     * @boolean
     */
    centerOnStart: true,

    /**
     * Provides the window management functionality for
     * the new event bus.
     *
     * This method registers the window into the window manager
     * for the asssociated sub application and terminates the
     * main window and it's sub windows.
     *
     * @private
     * @return void
     */
    onAfterRenderComponent: function() {
        var me = this,
            subApp = me.subApplication || me.subApp, windowManager, windowCount;

        if(!subApp) {
            return;
        }

        windowManager = subApp.windowManager;
        windowCount = windowManager.subWindows.getCount();

        windowManager.register(me, true, true);
        if(windowManager.zIndexStack.length == 1) {
            var mainWindow = me;
            mainWindow.isMainWindow = true;
            windowManager.mainWindow = mainWindow;

            mainWindow.on({
                beforeclose: me.onBeforeDestroyMainWindow,
                scope: me
            });
        } else {
            if(!windowManager.multipleSubWindows && windowCount == 1) {

                // Just throw a warning, not a error to prevent layout issues at further opened windows.
                if(Ext.isDefined(Ext.global.console)) {
                    Ext.global.console.warn('Enlight.app.Window: The sub application is configured to only support one opened sub window at once.');
                }
            }
            this.$subWindowId = Ext.id();
            windowManager.subWindows.add(this.$subWindowId, this);
            me.isSubWindow = true;

            // Remove the sub windows from the window manager before destroying
            me.on({
                scope: me,
                beforedestroy: function() {
                    windowManager.subWindows.removeAtKey(this.$subWindowId);
                }
            });
        }

        windowManager.bringToFront(me);
    },

    /**
     * Will be called when the main application window will be destroyed. The method
     * checks if one or more sub windows are opened and throws an confirm dialog. If
     * the user accepts that with a click on the button "yes", all sub windows
     * and the main application window will be destroyed.
     *
     * @event beforeclose
     * @privaate
     * @return [boolean]
     */
    onBeforeDestroyMainWindow: function () {
        var me = this,
            subApp = me.subApplication,
            windowManager, count, subWindows,
            subWindowConfirmationBlackList = [ 'Shopware.apps.Category', 'Shopware.apps.Voucher' ];

        // we don't have the window manager, so just return true to resume the `destroy` event
        if (!subApp.hasOwnProperty('windowManager') || !subApp.windowManager) {
            return true;
        }
        windowManager = subApp.windowManager;
        count = windowManager.subWindows.getCount();

        if (!count) {
            // Hide the window before destroy to increase the visual closing of the window
            // only when the window has no subWindows
            if (Ext.isFunction(me.hide)) {
                me.hide();
                return true;
            }
        }
        subWindows = windowManager.subWindows.items;

        if (Ext.Array.contains(subWindowConfirmationBlackList, me.subApplication.$className)) {

            //if the subApp is in the black list don't ask just close the sub windows
            me.closeSubWindows(subWindows, windowManager);
            return true;
        }


        Ext.Msg.confirm(
            me.closePopupTitle,
            me.closePopupMessage.replace('__MODULE__', me.title),
            function (button) {
                if (button == 'yes') {
                    me.closeSubWindows(subWindows, windowManager);
                    me.destroy();
                }
            }
        );

        // Prevent the event to continue to the the fact that we're triggering the destroying programatically...
        return false;
    },

	/**
	 * Initialize the Ext.window.Window and defines the necessary
	 * default configuration
     *
     * @return void
	 */
	initComponent: function() {
		var me = this;

        me.subApplication = me.initialConfig.subApp || this.subApp;
        delete this.subApp;

		// Set the rendering options
        if(!me.preventHeader) {
            me.constrain = true;
            me.isWindow = true;
        }

        // Add additional events
        me.on({
            dragstart: me.onMoveStart,
            dragend: me.onMoveEnd
        }, me);

        // Define the render area of the window
        var viewport = Shopware.app.Application.viewport;
        if(viewport && Shopware.apps.Index && me.forceToFront == false) {
            var activeDesktop = viewport.getActiveDesktop(),
                activeEl = activeDesktop.getEl();

            me.desktop = activeDesktop;
            me.desktopPosition =  viewport.getActiveDesktopPosition();
            me.renderTo = activeEl;
            me.constrainTo = activeEl;
        } else {
            /** We're in the development mode */
            me.renderTo = Ext.getBody();
        }

        // Prevent windows with no footerButton from getting lost
        // after minimizing. This way windows with no footerButton
        // will be neither minimizable nor maximizable but modal
        if(!me.footerButton) {
            me.maximizable = false;
            me.minimizable = false;
        }

        if(me.forceToFront) {
            me.minimizable = false;
        }

        me.callParent(arguments);

        if(me.centerOnStart) {
            me.center();
        }
        me.isWindowOnFront = true;
	},

    /**
     * Special ExtJS method which will be called
     * after the window is shown.
     *
     * @public
     * @return void
     */
    afterShow: function() {
        var me = this;

        me.callParent(arguments);
        Ext.Function.defer(function() {
            window.scrollTo(0, 0);
        }, 10);

        if(me.forceToFront) {
            var el = me.getEl(), elDom;

            // If we're not having a window, don't try to set the style(s)
            if(!el) {
                return false;
            }
            elDom = el.dom;

            // Setting the style with vanilla js to prevent issues with the Ext.ZIndexManager
            elDom.style.zIndex = "999999";
        }
    },

    /**
     * Sets the title of the header.
     *
     * @param { String } title - The title to be set
     */
    setTitle: function(title) {
        var me = this;

        me.callParent(arguments);

        if(me.footerButton && me._toolbarBtn) {
            me._toolbarBtn.setText(title);
        }
    },

    /**
     * Special ExtJS method which will be called
     * after the window is rendered.
     *
     * @public
     * @return void
     */
    afterRender: function() {
        var me = this;
        me.callParent(arguments);
        me.onAfterRenderComponent.call(me);

        // Create footer item for this window
        if(me.footerButton) {
            Shopware.WindowManagement.addItem(me.title, me);
        }
    },

	/**
	 * Event listener which minimizes the Ext.window.Window
     *
     * @return void
	 */
	minimize: function() {
		this.fireEvent('minimize', this);

		this.minimized = true;
		this.hide();

        // Toggle toolbar button
        if(this._toolbarBtn) {
            this._toolbarBtn.toggle(false, true);
        }
	},

    // private
    doClose: function() {
        var me = this;

        if(me.hideOnClose) {
            me.hideOnClose = false;
            me.hide(me.animateTarget, me.doClose, me);
        }

        // Being called as callback after going through the hide call below
        if (me.hidden) {
            me.fireEvent('close', me);
            if (me.closeAction == 'destroy') {
                this.destroy();
            }
        } else {
            // close after hiding
            me.hide(me.animateTarget, me.doClose, me);
        }

        if(this._toolbarBtn) {
            Shopware.WindowManagement.removeItem(this._toolbarBtn);
        }
    },

    // private
    onMoveStart: function() {
        var me = this, activeWindows = Shopware.app.Application.getActiveWindows(), viewport = Shopware.app.Application.viewport;

        if(viewport) {
            me.hiddenLayer = viewport.getHiddenLayer();
            me.hiddenLayer.setStyle('z-index', '9999999');
            me.hiddenLayer.appendTo(Ext.getBody());
        }
        Ext.each(activeWindows, function(window) {
            if(window != me) {
                if(window.$className !== 'Shopware.apps.Deprecated.view.main.Window') {
                    window.ghost('', true);
                }
            }
        });
    },

    // private
    onMoveEnd: function() {
        var me = this, activeWindows = Shopware.app.Application.getActiveWindows(), viewport = Shopware.app.Application.viewport;

        Ext.each(activeWindows, function(window) {
            if(!window.minimized && window != me) {
                if(window.$className !== 'Shopware.apps.Deprecated.view.main.Window') {
                    window.unghost(true, true, true);
                }
            }
        });

        if(viewport) {
            viewport.jumpTo(me.desktopPosition, true);
            me.hiddenLayer.setStyle('z-index', null);
            Ext.removeNode(me.hiddenLayer.dom);
        }
    },

    /**
     * Event listener method which will be called when the user clicks somewhere
     * in a window.
     *
     * This method syncs the zseed (z-index) with the globally availble Ext.WindowManager
     * to set the clicked window to front.
     *
     * @event mousedown
     * @private
     * @return void
     */
    onMouseDown: function() {
        var me = this,
            subApp = me.subApplication || me.subApp;

        if (!subApp) {
            return;
        }

        var windowManager = subApp.windowManager;

        // We need a try & catch here to prevent errors if the will be activated and
        // destroyed immediately after that.
        try {
            windowManager.bringToFront(me);
        } catch(e) {}

        me.callParent(arguments);
    },

    // private
    fitContainer: function() {
        var me = this,
            parent = me.floatParent,
            container = parent ? parent.getTargetEl() : me.container,
            size = container.getViewSize(false);

        me.setSize(size);
        me.setPosition(0, 0);
    },

    maximize: function() {
        var me = this;

        if (!me.maximized) {
            me.expand(false);
            if (!me.hasSavedRestore) {
                me.restoreSize = me.getSize();
                me.restorePos = me.getPosition(true);
            }
            if (me.maximizable) {
                me.header.tools.maximize.hide();
                me.header.tools.restore.show();
            }
            me.maximized = true;
            me.el.disableShadow();

            if (me.dd) {
                me.dd.disable();
            }
            if (me.resizer) {
                me.resizer.disable();
            }
            if (me.collapseTool) {
                me.collapseTool.hide();
            }
            me.el.addCls(Ext.baseCSSPrefix + 'window-maximized');
            me.container.addCls(Ext.baseCSSPrefix + 'window-maximized-ct');

            me.syncMonitorWindowResize();
            me.fitContainer();
            me.fireEvent('maximize', me);
        }
        return me;
    },

    restore: function() {
        var me = this,
            header = me.header,
            tools = header.tools;

        if (me.maximized) {
            delete me.hasSavedRestore;
            me.removeCls(Ext.baseCSSPrefix + 'window-maximized');

            // Toggle tool visibility
            if (tools.restore) {
                tools.restore.hide();
            }
            if (tools.maximize) {
                tools.maximize.show();
            }
            if (me.collapseTool) {
                me.collapseTool.show();
            }

            me.maximized = false;

            // Restore the position/sizing
            me.setPosition(me.restorePos);
            me.setSize(me.restoreSize);

            // Unset old position/sizing
            delete me.restorePos;
            delete me.restoreSize;

            me.el.enableShadow(true);

            // Allow users to drag and drop again
            if (me.dd) {
                me.dd.enable();
                if (header) {
                    header.addCls(header.indicateDragCls)
                }
            }

            if (me.resizer) {
                me.resizer.enable();
            }

            me.container.removeCls(Ext.baseCSSPrefix + 'window-maximized-ct');

            me.syncMonitorWindowResize();
            me.doConstrain();
            me.fireEvent('restore', me);
        }
        return me;
    },

    /**
     * helper function to close all subwindows
     */
    closeSubWindows: function(subWindows, windowManager) {
        Ext.each(subWindows, function(subWindow) {
            if(subWindow) {
                windowManager.subWindows.removeAtKey(subWindow.$subWindowId);
                subWindow.destroy();
            }
        });
    }
});
