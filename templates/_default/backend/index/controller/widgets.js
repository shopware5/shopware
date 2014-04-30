/**
 * Shopware 4
 * Copyright © shopware AG
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
 * Shopware Widget Controller
 *
 * This controller handles the widget window, its widgets, settings and events.
 */

//{namespace name=backend/index/controller/widgets}
//{block name="backend/index/controller/widgets"}

Ext.define('Shopware.apps.Index.controller.Widgets', {

    extend: 'Enlight.app.Controller',

    /**
     * @default null
     * @Ext.container.Viewport
     */
    viewport: null,

    /**
     * @default null
     * @Ext.container.Container
     */
    desktop: null,

    /**
     * Store of all available widgets
     */
    widgetStore: null,

    /**
     * Store of all user widget settings
     */
    widgetSettingsStore: null,

    /**
     * Store of the current users widget settings
     */
    widgetSettings: null,

    /**
     * The main widget window, will be initialized when the widget and widget settings stores are loaded
     */
    widgetWindow: null,

    /**
     * Initializes the widget controller.
     * Creates the widget store and binds all needed events.
     */
    init: function() {
        var me = this;

        me.viewport = Shopware.app.Application.viewport;

        if(!me.viewport) {
            Ext.Error.raise('Viewport is not loaded');
        }

        me.desktop = me.viewport.getActiveDesktop();

        me.widgetStore = me.getStore('Widget');
        me.widgetStore.load({
            callback: me.onWidgetStoreLoaded.bind(me)
        });

        me.taskBarBtn = Ext.getCmp('widgetTaskBarBtn');
        me.taskBarBtn.on('click', me.onTaskBarBtnClick.bind(me));

        me.control({
            'widget-sidebar-window': {
                minimizeWindow: me.onMinimizeWindow,
                changePosition: me.onChangePosition,
                saveWidgetPosition: me.onSaveWidgetPosition,
                saveWidgetPositions: me.onSaveWidgetPositions,
                addWidget: me.onAddWidget,
                saveWindowSize: me.onSaveWindowSize
            },

            'widget-base': {
                closeWidget: me.onCloseWidget
            }
        });

        me.callParent(arguments);
    },

    /**
     * Called when the available widgets are loaded.
     * Creates the WidgetSettings store to get all user widget settings.
     */
    onWidgetStoreLoaded: function () {
        var me = this;

        me.widgetSettingsStore = me.getStore('WidgetSettings');

        me.widgetSettingsStore.load({
            callback: me.onWidgetSettingsLoaded.bind(me)
        });
    },

    /**
     * Called when the widget button in the task bar was clicked
     * Toggles the minimizing of the window
     */
    onTaskBarBtnClick: function() {
        var me = this,
            minimized = false,
            win = me.widgetWindow,
            taskBarBtn = me.taskBarBtn,
            taskBarBtnEl = taskBarBtn.getEl();

        taskBarBtn.disable();

        if (win.isVisible()) {
            win.hide(taskBarBtn, function() {
                taskBarBtn.enable();
            });

            taskBarBtnEl.removeCls('btn-over');
            minimized = true;
        } else {
            win.show(taskBarBtn, function() {
                taskBarBtn.enable();
                win.toFront();
            });

            taskBarBtnEl.addCls('btn-over');
        }

        me.widgetSettings.set('minimized', minimized);
        me.widgetSettingsStore.sync();
    },

    /**
     * Called when the widget settings were loaded.
     * Searches for the settings of the current user and if they could not be found, default settings will be created.
     * After the settings are available, the widget window will be created.
     */
    onWidgetSettingsLoaded: function() {
        var me = this,
            authId = ~~(me.widgetStore.getProxy().getReader().jsonData.authId),
            settings = me.getWidgetSettingsByAuthId(authId);

        if(!settings)  {
            me.widgetSettingsStore.add({
                authId: authId,
                height: 600,
                columnsShown: 1,
                dock: 'tl',
                minimized: false
            });

            me.widgetSettingsStore.sync();

            settings = me.getWidgetSettingsByAuthId(authId);
        }

        if(!settings) {
            Ext.Error.raise('Widget settings could not be initialized.');
        }

        me.widgetSettings = settings;

        me.widgetWindow = me.getView('widgets.Window').create({
            widgetStore: me.widgetStore,
            desktop: me.desktop,
            widgetSettings: me.widgetSettings
        });

        if (!settings.get('minimized')) {
            me.taskBarBtn.getEl().addCls('btn-over');
        }
    },

    /**
     * Returns widgetsettings that have the same authId as the user.
     * If no settings were found, null will be returned.
     *
     * @param authId
     * @returns { Object }|null
     */
    getWidgetSettingsByAuthId: function (authId) {
        var me = this,
            settings = null;

        me.widgetSettingsStore.each(function(record) {
            if(record.get('authId') === authId) {
                settings = record;
                return false;
            }
        });

        return settings;
    },

    /**
     * Minimizes the window and saves the change to the localStorage.
     *
     * @param sidebarWindow
     */
    onMinimizeWindow: function(sidebarWindow) {
        var me = this,
            btn = me.taskBarBtn;

        btn.disable();

        sidebarWindow.hide(btn, function() {
            btn.enable();
            btn.removeCls('btn-over');
        });

        me.widgetSettings.set('minimized', true);
        me.widgetSettingsStore.sync();
    },

    /**
     * Moves / aligns the window to a given corner.
     *
     * @param { Shopware.apps.Index.view.widgets.Window } win
     * @param { String } align - on what corner the window should be aligned (tl, tr, bl, br)
     *
     *        tl - top left
     *        tr - top right
     *        bl - bottom left
     *        br - bottom right
     *
     * @param { Boolean } animate - flag whether or not the position change should be animated
     */
    onChangePosition: function(win, align, animate) {
        var me = this,
            xOffset = 10,
            yOffset = 10,
            desktopEl = me.desktop.getEl(),
            x = xOffset,
            y = yOffset,
            verticalHandle = 's',
            horizontalHandle = 'e',
            handles = [],
            anim = animate !== false;

        if(align.indexOf('b') != -1) {
            y = desktopEl.getHeight() - win.getHeight() - yOffset;
            verticalHandle = 'n';
        }

        if (align.indexOf('r') != -1) {
            x = desktopEl.getWidth() - win.getWidth() - xOffset;
            horizontalHandle = 'w';
        }
        
        win.setPosition(x, y, anim);

        me.widgetSettings.set('dock', align);
        me.widgetSettingsStore.sync();

        if(win.resizer) {
            handles.push(verticalHandle);
            handles.push(horizontalHandle);
            handles.push(verticalHandle + horizontalHandle);

            win.handleResizer(handles);
        }
    },

    /**
     * Saves the position of a single widget.
     *
     * @param column
     * @param row
     * @param internalId
     */
    onSaveWidgetPosition: function(column, row, internalId) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=widgets action=saveWidgetPosition}',
            params: {
                column: column,
                position: row,
                id: internalId
            },

            callback: function() {
                me.widgetStore.load();
            }
        });
    },

    /**
     * Sends an ajax request to save to position changes of multiple widgets.
     * Expects an array of position informations which should should like the following:
     *
     * [
     *   {
     *     column: columnIndex,
     *     position: rowIndex,
     *     id: widgetId
     *   }
     * ]
     *
     * @param widgets
     */
    onSaveWidgetPositions: function(widgets) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=widgets action=saveWidgetPositions}',
            jsonData: {
                widgets: widgets
            },

            callback: function() {
                me.widgetStore.load();
            }
        });
    },

    /**
     * Add a new widget of the given type to the first (most left) column.
     * Sends an ajax request to save it server side.
     *
     * @param win
     * @param widgetName
     * @param menuItem
     */
    onAddWidget: function(win, widgetName, menuItem) {
        var me = this,
            container = win.containerCollection.getAt(0),
            widget = me.widgetStore.findRecord('name', widgetName);

        menuItem.disable();

        Ext.Ajax.request({
            url: '{url controller=widgets action=addWidgetView}',
            jsonData: {
                id: widget.get('id'),
                column: 0,
                position: container.items.getCount() - 1
            },
            callback: function(options, success, res) {
                if (!success) {
                    return;
                }

                var response = Ext.decode(res.responseText);

                me.widgetStore.load({
                    callback: function() {
                        widget = me.widgetStore.findRecord('name', widgetName);

                        var newWidget = win.createWidget(widgetName, widget.get('id'), me.getWidgetViewById(widget, response.viewId), widget.get('label'));

                        container.insert(newWidget.position.rowId, newWidget);

                        menuItem.enable();
                    }
                });
            }
        });
    },

    /**
     * Helper function to get the widget view by the view id in the widget model
     *
     * @param widget
     * @param id
     */
    getWidgetViewById: function(widget, id) {
        var views = widget.get('views'),
            widgetView = null;

        Ext.each(views, function(view) {
            if(view.id === id) {
                widgetView = view;
            }
        });

        return widgetView;
    },

    /**
     * Closes the widget which contained the close button.
     * Sends an ajax request to save the closing.
     */
    onCloseWidget: function(widget) {
        var me = this,
            container = me.widgetWindow.containerCollection.getAt(widget.position.columnId);

        Ext.Ajax.request({
            url: '{url controller=widgets action=removeWidgetView}',
            params: {
                id: widget.viewId
            },
            callback: function(options, success, response) {
                if (!success) {
                    return;
                }

                me.widgetStore.load({
                    callback: function() {
                        container.remove(widget, true);
                    }
                });
            }
        });
    },

    /**
     * Saves the new shown columns count and height in px to the localStorage
     *
     * @param columnsShown
     * @param height
     */
    onSaveWindowSize: function(columnsShown, height) {
        var me = this;

        me.widgetSettings.set('columnsShown', columnsShown);
        me.widgetSettings.set('height', height);
        me.widgetSettingsStore.sync();
    }
});

//{/block}