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
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

//{namespace name=backend/index/view/widgets}
//{block name="backend/index/view/widgets/window"}

Ext.define('Shopware.apps.Index.view.widgets.Window', {

    extend: 'Enlight.app.Window',

    alias: 'widget.widget-sidebar-window',

    cls: Ext.baseCSSPrefix + 'widget-sidebar',

    layout: 'vbox',
    width: 330,
    maxWidth: 330,
    minWidth: 330,
    height: 500,
    maxHeight: 760,
    minHeight: 150,
    header: false,
    shadow: false,
    footerButton: false,
    centerOnStart: false,

    closable: false,
    maximizable: false,
    minimizable: true,
    collapsible: false,
    autoShow: false,
    draggable: {
        delegate: 'widget-toolbar'
    },

    x: 10,
    y: 10,

    widgetStore: null,

    toolbar: null,
    grid: null,

    pinnedOnTop: false,

    initComponent: function() {
        var me = this;

        me.items = [
            me.createToolbar(),
            me.createScrollContainer()
        ];

        me.createWidgets();

        me.addEvents(
            'minimizeWindow',
            'fixWindow',
            'changePosition',
            'saveWidgetPosition',
            'addWidget',
            'removeWidget'
        );

        me.on('resize', function(me, width, height, eOpts) {
            me.scrollContainer.setHeight(height - 40);
        });

        me.callParent(arguments);
    },

    createToolbar: function() {
        var me = this;

        return me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            width: 300,
            margin: '0 0 10px 0',
            id: 'widget-toolbar',
            cls: Ext.baseCSSPrefix + 'widget-toolbar',
            items: [
                {
                    xtype: 'button',
                    cls: 'btn-widget-add',
                    menu: {
                        xtype: 'menu',
                        cls: Ext.baseCSSPrefix + 'widget-menu',
                        plain: true,
                        margin: '5px 0 0 0',
                        defaultAlign: 'tr-br',
                        items: me.createWidgetMenuItems()
                    }
                },
                '->',
                {
                    xtype: 'button',
                    cls: (me.pinnedOnTop) ? 'btn-widget-pin active' : 'btn-widget-pin',
                    handler: function() {
                        me.fireEvent('fixWindow', me, this);
                    }
                },
                {
                    xtype: 'button',
                    cls: 'btn-widget-minimize',
                    handler: function() {
                        me.fireEvent('minimizeWindow', me);
                    }
                },
                {
                    xtype: 'button',
                    cls: 'btn-widget-position',
                    menu: {
                        xtype: 'menu',
                        cls: Ext.baseCSSPrefix + 'widget-position-selection-menu',
                        plain: true,
                        margin: '5px 0 0 0',
                        defaultAlign: 'tr-br',
                        items: [
                            {
                                text: 'links oben',
                                iconCls: 'sprite-application-dock-180',
                                handler: function() {
                                    me.fireEvent('changePosition', me, 'tl');
                                }
                            },
                            {
                                text: 'links unten',
                                iconCls: 'sprite-application-dock-180',
                                handler: function() {
                                    me.fireEvent('changePosition', me, 'bl');
                                }
                            },
                            {
                                text: 'rechts oben',
                                iconCls: 'sprite-application-dock',
                                handler: function() {
                                    me.fireEvent('changePosition', me, 'tr');
                                }
                            },
                            {
                                text: 'rechts unten',
                                iconCls: 'sprite-application-dock',
                                handler: function() {
                                    me.fireEvent('changePosition', me, 'br');
                                }
                            }
                        ]
                    }
                }
            ]
        });
    },

    createWidgetMenuItems: function() {
        var me = this,
            items = [];

        me.widgetStore.each(function(widget) {
             items.push({
                 xtype: 'menucheckitem',
                 text: widget.get('label'),
                 widgetId: widget.get('id'),
                 checked: (widget.get('views').length) ? true : false,
                 //iconCls: 'sprite-application-dock-180',
                 listeners: {
                     checkchange: function(checkbox, status, eOpts) {

                         if (status) {
                             me.fireEvent('addWidget', widget.get('name'));
                         } else {
                             me.fireEvent('removeWidget', widget.get('name'));
                         }
                     }
                 }
             });
        });

        return items;
    },

    unghost: function() {
        var me = this;

        me.callParent(arguments);

        if (me.pinnedOnTop) {
            me.toFront();
        }
    },

    afterRender: function() {
        var me = this;

        me.callParent(arguments);

        me.createCustomScrollbar();
    },

    createScrollContainer: function() {
        var me = this;

        return me.scrollContainer = Ext.create('Ext.container.Container', {
            cls: Ext.baseCSSPrefix + 'widget-scroll-container',
            layout: {
                type: 'column',
                align: 'stretch'
            },
            flex: 1,
            width: 300,
            overflowX: 'hidden',
            overflowY: 'hidden',
            items: [
                me.createGrid()
            ]
        });
    },

    createGrid: function() {
        var me = this;

        return me.grid = Ext.create('Ext.container.Container', {
            layout: 'anchor',
            width: 300,
            defaultType: 'widget',
            listeners: {
                afterrender: me.createDropZone,
                scope: me
            }
        });
    },

    createDropZone: function() {
        var me = this;

        me.dropPanel = false;

        me.dropProxyEl = Ext.create('Ext.Component', {
            width: 300,
            height: 100,
            cls: Ext.baseCSSPrefix + 'widget-proxy-element',
            hidden: true
        });
        me.grid.add(me.dropProxyEl);

        me.grid.dropZone = Ext.create('Ext.dd.DropZone', me.grid.getEl(), {

            ddGroup: 'widget-container',

            getTargetFromEvent: function() {
                return me.grid;
            },

            onNodeEnter: function(container, source, e, data) {
                var draggedPanel = source.panel,
                    height = draggedPanel.height;

                me.dropProxyEl.setHeight(height);
                me.dropProxyEl.addCls('active');

                container.move(
                    me.grid.items.indexOf(me.dropProxyEl),
                    me.grid.items.indexOf(draggedPanel)
                );

                me.dropProxyEl.show();
            },

            onNodeOver : function(container, source, e, data){
                var draggedPanel = source.panel,
                    position = e.getXY(),
                    moveIndex;

                me.dropPanel = me.getOverPanel(draggedPanel, position);

                if (me.dropPanel != false) {

                    container.move(
                        me.grid.items.indexOf(me.dropProxyEl),
                        me.grid.items.indexOf(me.dropPanel)
                    );

                    return Ext.dd.DropZone.prototype.dropAllowed;
                }
            },

            onNodeOut : function(container, source, e, data){

                me.dropProxyEl.removeCls('active');
                me.dropProxyEl.hide();
            },

            onNodeDrop : function(container, source, e, data){
                var droppedPanel = source.panel;

                container.move(
                    me.grid.items.indexOf(droppedPanel),
                    me.grid.items.indexOf(me.dropProxyEl)
                );

                me.dropProxyEl.removeCls('active');
                me.dropProxyEl.hide();

                me.fireEvent('saveWidgetPosition');

                Ext.defer(function() { me.doLayout(); }, 100);

                return true;
            }
        });
    },

    createWidgets: function() {
        var me = this;

        me.widgetStore.each(function(widget) {
            if (widget.get('views').length) {
                me.grid.add(
                    Ext.widget(widget.get('name'), {
                        id: widget.get('name'),
                        widgetId: widget.get('id'),
                        viewId: widget.data.views[0].id,
                        title: widget.get('label')
                    })
                );
            }
        });
    },

    getOverPanel: function(draggedPanel, position) {
        var me = this,
            overPanel = false,
            panels = me.grid.items.items,
            panelsCount = panels.length,
            panelHeight = 0,
            pos = 0;

        for(panelsCount; pos < panelsCount; pos++) {
            var panel = panels[pos];
            panelHeight = panel.el.getHeight();

            if ((panel.el.getY() + (panelHeight / 2)) > position[1]) {
                overPanel = panel;
                break;
            }
        }

        return overPanel;
    },

    createCustomScrollbar: function() {
        var me = this,
            containerEl = me.scrollContainer.getEl(),
            widgetContainerEl = me.grid.getEl(),
            mouseWheelEvent = (/Firefox/i.test(navigator.userAgent)) ? 'DOMMouseScroll' : 'mousewheel'; // Mouse wheel browser detection

        var scroll =  function(e) {
            var delta = (e.wheelDelta) ? e.wheelDelta : e.detail,
                speed = (e.wheelDelta) ? 0.4 : 10,
                containerY = containerEl.getY(),
                containerHeight = containerEl.getHeight(),
                gridY = widgetContainerEl.getY(),
                gridHeight = widgetContainerEl.getHeight(),
                newPosition = gridY - (delta * speed),
                max = containerY,
                min = -(gridHeight - containerHeight - containerY);

            newPosition = Math.max(min, Math.min(newPosition, max));

            if (gridHeight > containerHeight) {
                widgetContainerEl.setY(newPosition);
            } else {
                widgetContainerEl.setY(containerY);
            }
        };

        if (document.attachEvent) {
            containerEl.dom.attachEvent('on'+mouseWheelEvent, scroll);
        } else if (document.addEventListener) {
            containerEl.dom.addEventListener(mouseWheelEvent, scroll, false);
        }
    }
});

//{/block}