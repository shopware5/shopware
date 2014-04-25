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

    widgetStore: null,

    widgetSettingsStore: null,

    widgetSettings: null,

    widgetWindow: false,

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
                addWidget: me.onAddWidget,
                removeWidget: me.onRemoveWidget,
                saveWindowSize: me.onSaveWindowSize
            }
        });

        me.callParent(arguments);
    },

    onWidgetStoreLoaded: function () {
        var me = this;

        me.widgetSettingsStore = me.getStore('WidgetSettings');

        me.widgetSettingsStore.load({
            callback: me.onWidgetSettingsLoaded.bind(me)
        });
    },

    onTaskBarBtnClick: function() {
        var me = this,
            minimized = false,
            win = me.widgetWindow,
            taskBarBtn = me.taskBarBtn,
            taskBarBtnEl = taskBarBtn.getEl();

        if (win.isVisible()) {
            win.hide(taskBarBtn);
            taskBarBtnEl.removeCls('btn-over');
            minimized = true;
        } else {
            win.show(taskBarBtn).toFront();
            taskBarBtnEl.addCls('btn-over');
        }

        me.widgetSettings.set('minimized', minimized);
        me.widgetSettingsStore.sync();
    },

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

    onMinimizeWindow: function(sidebarWindow) {
        var me = this;

        sidebarWindow.hide(me.taskBarBtn);

        me.widgetSettings.set('minimized', true);
        me.widgetSettingsStore.sync();
    },

    onChangePosition: function(win, align, animate) {
        var me = this,
            xOffset = 10,
            yOffset = 10,
            desktopEl = me.desktop.el,
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

    onSaveWidgetPosition: function(column, row, widgetId, internalId) {
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

    onAddWidget: function(win, widgetName, menuItem) {
        var me = this,
            container = win.containerCollection.getAt(0),
            widget = me.widgetStore.findRecord('name', widgetName);

        menuItem.disable();

        Ext.Ajax.request({
            url: '{url controller=widgets action=addWidgetView}',
            jsonData: {
                id: widget.get('id'),
                label: widget.get('label'),
                column: 0,
                position: container.items.getCount() - 1
            },
            callback: function(options, success, response) {
                if (!success) {
                    return;
                }

                me.widgetStore.load({
                    callback: function() {
                        widget = me.widgetStore.findRecord('name', widgetName);

                        win.createWidgets(widget);

                        menuItem.enable();
                    }
                });
            }
        });
    },

    onRemoveWidget: function(win, widgetName) {
        var me = this,
            widget = me.widgetStore.findRecord('name', widgetName),
            views = widget.get('views'),
            column;

        Ext.Ajax.request({
            url: '{url controller=widgets action=removeWidgetView}',
            jsonData: {
                views: views
            },
            callback: function(options, success, response) {
                if (!success) {
                    return;
                }

                me.widgetStore.load();

                Ext.each(views, function(view) {
                    column = win.containerCollection.getAt(view.column);
                    
                    if (!column) {
                        column = win.containerCollection.getAt(0);
                    }

                    column.items.each(function(widget) {
                        if(widget.viewId === view.id) {
                            column.remove(widget);
                            return false;
                        }
                    });
                });
            }
        });
    },

    onSaveWindowSize: function(columnsShown, height) {
        var me = this;

        me.widgetSettings.set('columnsShown', columnsShown);
        me.widgetSettings.set('height', height);
        me.widgetSettingsStore.sync();
    }
});

//{/block}