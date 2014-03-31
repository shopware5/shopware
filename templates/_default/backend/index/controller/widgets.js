/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Index
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

//{namespace name=backend/index/view/widgets}
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

    widgetView: false,

    snippets: {},

    init: function() {
        var me = this;

        me.viewport = Shopware.app.Application.viewport;

        if(!me.viewport) {
            Ext.Error.raise('Viewport is not loaded');
        }

        me.desktop = me.viewport.getActiveDesktop();

        me.widgetStore = me.getStore('Widgets').load({
            callback: function() {
                me.renderWidgetBar();
            }
        });

        me.control({
            'widget-sidebar-window': {
                minimizeWindow: me.onMinimizeWindow,
                fixWindow: me.onFixWindow,
                changePosition: me.onChangePosition,
                saveWidgetPosition: me.onSaveWidgetPosition,
                addWidget: me.onAddWidget,
                removeWidget: me.onRemoveWidget
            }
        });

        me.callParent(arguments);

        me.sideBarBtn = Ext.getCmp('widgetSidebarBtn');
        me.sideBarBtn.on({
            click: function() {
                me.onSideBarBtn();
            }
        });
    },

    renderWidgetBar: function() {
        var me = this;

        if (!me.widgetView) {
            me.widgetView = me.getView('widgets.Window').create({
                renderTo: me.desktop.getEl(),
                widgetStore: me.widgetStore
            }).toBack().show(me.sideBarBtn);
        }
    },

    onMinimizeWindow: function(sidebarWindow) {
        var me = this;

        sidebarWindow.hide(me.sideBarBtn);
    },

    onFixWindow: function(sidebarWindow, pinButton) {
        var me = this,
            window = sidebarWindow.getEl();

        if(!window) {
            return false;
        }

        if (me.widgetView.pinnedOnTop) {
            me.widgetView.pinnedOnTop = false;
            me.widgetView.toBack();
            pinButton.removeCls('active');
        } else {
            me.widgetView.pinnedOnTop = true;
            me.widgetView.toFront();
            pinButton.addCls('active');
        }
    },

    onChangePosition: function(sidebarWindow, position) {
        var me = this,
            x, y;

        switch (position) {
            case 'tl':
                sidebarWindow.setPosition(10, 10, true);
                break;
            case 'bl':
                x = 10;
                y = me.desktop.getHeight() - sidebarWindow.getHeight();
                sidebarWindow.setPosition(x, y, true);
                break;
            case 'tr':
                x = me.desktop.getWidth() - sidebarWindow.getWidth() - 10;
                y = 10;
                sidebarWindow.setPosition(x, y, true);
                break;
            case 'br':
                x = me.desktop.getWidth() - sidebarWindow.getWidth() - 10;
                y = me.desktop.getHeight() - sidebarWindow.getHeight();
                sidebarWindow.setPosition(x, y, true);
                break;
            default:
                sidebarWindow.setPosition(10, 10, true);
                break;
        }
    },

    onSaveWidgetPosition: function() {
        var me = this,
            data = [],
            panels = me.widgetView.grid.items.items,
            panelCount = panels.length,
            i = 0;

        for (panelCount; i < panelCount; i++) {
            if (panels[i].viewId) {
                data.push({
                    viewId: panels[i].viewId,
                    position: me.widgetView.grid.items.indexOf(panels[i])
                });
            }
        }

        Ext.Ajax.request({
            url: '{url controller=widgets action=saveWidgetPosition}',
            jsonData: {
                data: data
            }
        });
    },

    onAddWidget: function(widgetName) {
        var me = this,
            widget = me.widgetStore.findRecord('name', widgetName);

        Ext.Ajax.request({
            url: '{url controller=widgets action=addWidgetView}',
            jsonData: {
                id: widget.get('id'),
                label: widget.get('label'),
                column: 1,
                position: me.widgetView.grid.items.items.length + 1
            },
            callback: function(options, success, response) {
                if (success) {
                    var data = Ext.JSON.decode(response.responseText);

                    me.widgetStore.reload();

                    me.widgetView.grid.add(
                        Ext.widget(widget.get('name'), {
                            id: widget.get('name'),
                            widgetId: widget.get('id'),
                            viewId: data.viewId,
                            title: widget.get('label')
                        })
                    );
                }
            }
        });
    },

    onRemoveWidget: function(widgetName) {
        var me = this,
            widget = me.widgetStore.findRecord('name', widgetName);

        Ext.Ajax.request({
            url: '{url controller=widgets action=removeWidgetView}',
            jsonData: {
                views: widget.get('views')
            },
            callback: function(options, success, response) {
                if (success) {
                    me.widgetStore.reload();
                    me.widgetView.grid.remove(Ext.getCmp(widget.get('name')));
                }
            }
        });
    },

    onSideBarBtn: function() {
        var me = this;

        if (me.widgetView.isVisible()) {
            me.widgetView.hide(me.sideBarBtn);
        } else {
            me.widgetView.show(me.sideBarBtn).toFront();
        }
    }
});

//{/block}