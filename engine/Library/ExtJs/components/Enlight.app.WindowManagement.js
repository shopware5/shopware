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
 * Shopware UI - Index - Window Management Controller
 *
 * This controller handles the whole window management.
 * It contains all neccessary methods to create a new
 * button in the footer and the assignment of the windows.
 *
 * @class
 * @singleton
 */
Ext.define('Enlight.app.WindowManagement', {

    /**
     * Alternate class names for class
     *
     * @array
     */
    alternateClassName: [ 'Shopware.app.WindowManagement', 'Shopware.WindowManagement' ],

    /**
     * Implement the controller as a singleton
     *
     * @boolean
     */
    singleton: true,

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Requires class object for is necessary for this
     * class to work
     *
     * @array
     */
    requires: [ 'Ext.WindowManager' ],

    /**
     * CSS class for the window buttons in the footer
     *
     * @string
     */
    defaultCls: 'footer-btn',

    /**
     * CSS icon clas for the window buttons in the footer
     *
     * @string
     */
    defaultIconCls: 'closeable',

    /**
     * Indicates if the controller initializes the necessary view
     *
     * @private
     * @boolean
     */
    initial: true,

    /**
     * Holder property which holds the holder view
     *
     * @private
     * @null
     */
    view: null,

    /**
     * Holder property which holds the global footer view
     *
     * @private
     * @null
     */
    footer: null,

    /**
     * Holder property which holds the global backend holder
     *
     * @private
     * @null
     */
    viewport: null,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function(footer) {
        var me = this;

        if(me.initial) {
            me.view = Ext.create('Ext.container.Container', {
                cls: 'window-management-holder',
                autoScroll: true
            });
            me.footer = footer;
            me.viewport = footer.ownerCt;
            me.footer.add(me.view);
            me.inital = false;
        }
        footer.on('afterrender', me.onFooterRendered, me);

        me.callParent(arguments);
    },

    /**
     * Adds a new button to the window management
     *
     * @param [string] text - The button text
     * @param [object] view - The view which is responsible for the button
     * @return [object] btn - Created Ext.button.Button
     */
    addItem: function(text, view) {
        var btn, me = this;
        if(!me.view) {
            return btn;
        }
        view = view || {};

        btn = Ext.create('Ext.button.Button', {
            cls: me.defaultCls,
            iconCls: me.defaultIconCls,
            text: Ext.util.Format.htmlEncode(text || 'Window'),
            responsibleView: view,
            handler: me.onButtonClick
        });

        view._toolbarBtn = btn;
        me.view.add(btn);
        me.setActiveItem(btn);

        view.on('destroy', function() {
            if(view._toolbarBtn) {
                view._toolbarBtn.destroy();
            }
        }, me);

        return btn;
    },

    /**
     * Set the passed Ext.button.Button active and remove the active
     * state from all other buttons
     *
     * @param [object] btn - active Ext.button.Button
     * @return [object] btn - passed Ext.button.Button
     */
    setActiveItem: function(btn) {
        if(this.view.items.items && this.view.items.items.length > 0) {
            Ext.each(this.view.items.items, function(item) {
                item.toggle(false, true);
            });
        }
        btn.toggle(true, true);
        return btn;
    },

    /**
     * Removes the passed Ext.button.Button from the
     * window management
     *
     * @param [object] btn - Ext.button.Button which will be removed
     * @return [boolean]
     */
    removeItem: function(btn) {
        this.view.remove(btn, true);
        return true;
    },

    /**
     * Removes the component with the passed key
     * from the window management
     *
     * @param [integer] key - ID of the component which will be removed
     * @return [boolean]
     */
    removeAt: function(key) {
        this.view.remove(key, true);
        return true;
    },

    /**
     * Returns the button which is associated to
     * the passed key
     *
     * @param [integer] key - ID of the button
     * @return [object] Ext.button.Button
     */
    getAt: function(key) {
        return this.view.items.items[key];
    },

    /**
     * Returns all buttons in the window management
     *
     * @param void
     * @return [array] Array of the found Ext.button.Button
     */
    getAllItems: function() {
        return this.view.items.items;
    },

    /**
     * Removes all items from the window
     * management
     *
     * @param void
     * @return [boolean]
     */
    removeAllItems: function() {
        this.view.removeAll(true);
        return true;
    },

    /**
     * Event listener method which handles the click on the button.
     *
     * Note: That this method has a special behavior which handles the click
     * on the button icon.
     *
     * @param [object] btn - clicked Ext.button.Button
     * @param [object] e - thrown Event as an Ext.EventObject
     * @return void
     */
    onButtonClick: function(btn, e) {
        if(!btn.responsibleView) {
            return false;
        }
        var view = btn.responsibleView,
            viewport = Shopware.app.Application.viewport,
            icn = btn.btnIconEl,
            minHeight = icn.getY(),
            maxHeight = icn.getY() + icn.getHeight(),
            minWidth = icn.getX(),
            maxWidth = icn.getX() + icn.getWidth();

        // Special behavior which listen if the icon on the button was clicked
        if(e.getY() >= minHeight && e.getY() <= maxHeight &&
           e.getX() >= minWidth && e.getX() <= maxWidth) {

            if(view.closeAction == 'destroy') {
                view.destroy();
            } else {
                view.hide();
            }
            return false;
        }
        if(viewport.getActiveDesktop() !== view.desktop) {
            viewport.jumpTo(view.desktopPosition);
        }

        if(view.minimized) {
            view.show();
            btn.toggle(true, true);
            view.minimized = false;
        }

        var isModal = false;
        Ext.each(Ext.WindowManager.zIndexStack, function(item) {
            if(item && item.modal && item.modal === true && item.getEl().isVisible()) {
                isModal = true;
            }
        });

        if(!isModal) {
            Ext.WindowManager.bringToFront(view);
        }

        Shopware.app.WindowManagement.setActiveItem(btn);
    },

    /**
     * Event listener method which will be called when the footer's "afterrender"-Event.
     * The method saves the instance of the footer's owner component (our backend holder panel)
     *
     * @param [object] cmp - Shopware.apps.Index.view.main.Footer
     * @return void
     */
    onFooterRendered: function(cmp) {
        this.viewport = cmp.ownerCt;
    },

    /**
     * Minimizes all opened windows based on the Ext.WindowManager.
     *
     * Note that the Ext.window.Window and the Ext.Window objects are the only components
     * which will be minimized.
     *
     * @return [boolean]
     */
    minimizeAll: function() {
        var wins = this.getActiveWindows();

        Ext.each(wins, function(win) {
            win.minimize();
        });

        return true;
    },

    /**
     * Closes all opened window based on the Ext.WindowManager.
     *
     * Note that the Ext.window.Window and the Ext.Window objects are the only components
     * which will be closed.
     *
     * @return [boolean]
     */
    closeAll: function() {
        var wins = this.getActiveWindows();

        Ext.each(wins, function(win) {
            if (win.xtype === 'widget-sidebar-window') {
                return true;
            }

            win.destroy();
        });

        Shopware.app.WindowManagement.removeAllItems();

        return true;
    },

    /**
     * Stacks all opened windows vertically under each other
     *
     * @return [boolean]
     */
    stackVertical: function() {
        var activeWindows = this.getActiveWindows(),
            viewport = Shopware.app.WindowManagement.viewport,
            footer = Shopware.app.WindowManagement.footer,
            size = viewport.getSize(), count, windowHeight,
            footerSize = footer.getSize();

        count = activeWindows.length;

        windowHeight = (Ext.Element.getViewportHeight() - (footerSize.height * 2)) / count;

        Ext.each(activeWindows, function(window, index) {
            window.setSize({ width: size.width, height: windowHeight });
            window.setPosition(0, windowHeight * index);
        });

        return true;
    },

    /**
     * Stacks all opened windows horizontal side by side.
     *
     * @return [boolean]
     */
    stackHorizontal: function() {
        var activeWindows = this.getActiveWindows(),
            viewport = Shopware.app.WindowManagement.viewport,
            footer = Shopware.app.WindowManagement.footer,
            size = viewport.getSize(), count, windowWidth,
            footerSize = footer.getSize();

        count = activeWindows.length;

        windowWidth = (Ext.Element.getViewportWidth()) / count;

        Ext.each(activeWindows, function(window, index) {
            window.setSize({ width: windowWidth, height: size.height - (footerSize.height * 2) });
            window.setPosition(windowWidth * index, 0);
            window.show();
        });

        return true;
    },

    /**
     * Proxy method which returns all open windows.
     *
     * @private
     * @return [array] active windows
     */
    getActiveWindows: function () {
        var activeWindows = [];

        Ext.each(Shopware.app.Application.subApplications.items, function (subApp) {

            // Check if the subapplication is complete, so we can iterate over the z-index stack manager
            if(!subApp.windowManager || !subApp.windowManager.hasOwnProperty('zIndexStack')) {
                return;
            }

            Ext.each(subApp.windowManager.zIndexStack, function (item) {
                if (typeof item !== 'undefined' && item.$className === 'Ext.window.Window' || item.$className === 'Shopware.apps.Deprecated.view.main.Window' || item.$className === 'Enlight.app.Window' || item.$className === 'Ext.Window' && item.$className !== "Ext.window.MessageBox") {
                    activeWindows.push(item);
                }

                if (item.alternateClassName === 'Ext.window.Window' || item.alternateClassName === 'Shopware.apps.Deprecated.view.main.Window' || item.alternateClassName === 'Enlight.app.Window' || item.alternateClassName === 'Ext.Window' && item.$className !== "Ext.window.MessageBox") {
                    activeWindows.push(item);
                }
            });
        });

        return activeWindows;
    }
});
