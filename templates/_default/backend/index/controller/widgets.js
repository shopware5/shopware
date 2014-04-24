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
                fixWindow: me.onFixWindow,
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
                minimized = false;

        if (me.widgetWindow.isVisible()) {
            me.widgetWindow.hide(me.taskBarBtn);
            minimized = true;
        } else {
            me.widgetWindow.show(me.taskBarBtn).toFront();
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
                pinned: false,
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

    onFixWindow: function(win, pinButton) {
        var me = this,
            pinned = !win.pinnedOnTop,
            windowEl = win.getEl();

        if(!windowEl) {
            return;
        }

        win.pinnedOnTop = pinned;

        me.widgetSettings.set('pinned', pinned);
        me.widgetSettingsStore.sync();

        if (pinned) {
            win.toFront();
            pinButton.addCls('active');
            return;
        }

        win.toBack();
        pinButton.removeCls('active');
    },

    onChangePosition: function(win, align, animate) {
        var me = this,
            xOffset = 10,
            yOffset = 10,
            desktopEl = me.desktop.el,
            bottomOffset = (desktopEl.getBottom() - desktopEl.getHeight()) * -1,
            x = xOffset,
            y = yOffset,
            verticalHandle = 's',
            horizontalHandle = 'e',
            handles = [],
            resizer = win.resizer,
            anim = animate !== false,
            positions, pos, p, i;

        if(desktopEl.getBottom() !== window.innerHeight) {
            var domEl = win.toolbar.getEl().dom;
            bottomOffset = domEl.scrollHeight + domEl.firstChild.scrollHeight;
        }

        if(align.indexOf('b') != -1) {
            y = (desktopEl.getHeight() + bottomOffset) - win.getHeight() - yOffset;
            verticalHandle = 'n';
        }

        if (align.indexOf('r') != -1) {
            x = desktopEl.getWidth() - win.getWidth() - xOffset;
            horizontalHandle = 'w';
        }
        
        win.setPosition(x, y, anim);

        me.widgetSettings.set('dock', align);
        me.widgetSettingsStore.sync();

        if(!resizer) {
            return;
        }

        positions = resizer.possiblePositions;

        handles.push(verticalHandle);
        handles.push(horizontalHandle);
        handles.push(verticalHandle + horizontalHandle);

        /**
         * All resizer handles will be hidden and only the relevant ones will be shown
         * It's dirty but it works, the is no cleaner way provided by the resizer
         */
        Ext.iterate(positions, function(p) {
            pos = positions[p];

            resizer[pos].hide();
        });

        for(i = 0; i < handles.length; i++) {
            pos = positions[handles[i]];

            resizer[pos].show();
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

    onAddWidget: function(win, widgetName) {
        var me = this,
            container = win.containerCollection.getAt(0),
            widget = me.widgetStore.findRecord('name', widgetName);

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

                    column.remove(column.getComponent(view.position));
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