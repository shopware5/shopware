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

    layout: 'anchor',
    header: false,
    shadow: false,
    footerButton: false,
    centerOnStart: false,

    closable: false,
    maximizable: false,
    minimizable: true,
    collapsible: false,
    autoShow: false,
    resizable: {
        floating: true,
        handles: 'all'
    },
    draggable: {
        delegate: 'widget-toolbar'
    },

    x: 10,
    y: 10,

    padding: 10,

    widgetStore: null,
    desktop: null,
    widgetSettings: null,

    toolbar: null,

    columnCount: 4,
    columnsShown: 1,

    pinnedOnTop: false,

    containerCollection: null,

    initComponent: function() {
        var me = this,
            settings = me.widgetSettings;

        me.columnsShown = settings.get('columnsShown');
        me.height = settings.get('height');
        me.hidden = settings.get('minimized');
        me.pinnedOnTop = settings.get('pinned');

        me.containerCollection = Ext.create('Ext.util.MixedCollection');

        me.toolbar = me.createToolbar();

        me.dockedItems = [
            me.toolbar
        ];

        me.wrapper = me.createWidgetWrapper();

        me.items = [
            me.wrapper
        ];

        me.desktop.on('resize', me.onDesktopResize.bind(me));

        me.onDesktopResize(me.desktop, me.desktop.getWidth(), me.desktop.getHeight());

        me.on('resize', me.onResize);

        me.addEvents(
            'minimizeWindow',
            'fixWindow',
            'changePosition',
            'saveWidgetPosition',
            'addWidget',
            'removeWidget',
            'saveWindowSize'
        );

        me.callParent(arguments);

        me.widgetStore.each(me.createWidgets.bind(me));
    },

    createWidgetWrapper: function () {
        var me = this,
            wrapper = Ext.create('Ext.container.Container', {
                layout: 'hbox'
            });

        for(var i = 0; i < me.columnCount; i++) {
            var container = me.createWidgetContainer(i);

            wrapper.add(container);
            me.containerCollection.add(container);
        }

        return wrapper;
    },

    createWidgetContainer: function(column) {
        var me = this,
            options = {
                layout: 'anchor',
                flex: 1,   // We want same sized columns, so we could use flex
                padding: '0 5px',
                minHeight: 200,
                cls: Ext.baseCSSPrefix + 'widget-column-container',
                columnId: column,
                listeners: {
                    render: me.createContainerDropZone,
                    scope: me
                }
            };

        return Ext.create('Ext.container.Container', options);
    },

    createContainerDropZone: function(container) {
        var me = this,
            dropProxyEl = Ext.create('Ext.Component', {
            cls: Ext.baseCSSPrefix + 'widget-proxy-element',
            height: 200
        });

        container.dropZone = Ext.create('Ext.dd.DropZone', container.getEl(), {
            ddGroup: 'widget-container',

            getTargetFromEvent: function() {
                return container;
            },

            onNodeEnter: function(target, dd) {
                dropProxyEl.addCls('active');
            },

            onNodeOut: function(target) {
                var dropSource = this,
                    dropIndex = target.items.indexOf(dropProxyEl),
                    lastIndex = target.items.getCount() - 1;

                if(dropIndex != lastIndex) {
                    target.move(dropIndex, lastIndex);
                    dropSource.dropIndex = dropIndex;
                }

                dropProxyEl.removeCls('active');
            },

            onNodeDrop: function(target, dd, e, data) {
                var dropSource = this,
                    newColumn = target.columnId,
                    newRow = dropSource.dropIndex,
                    panel = dd.panel.cloneConfig({
                        position: {
                            rowId: newRow,
                            columnId: newColumn
                        }
                    });

                target.insert(newRow, panel);

                // Fire event which saves the new position
                me.fireEvent('saveWidgetPosition', newColumn, newRow, panel.widgetId, panel.$initialId);

                me.containerCollection.each(function(container) {
                    container.dropZone.onNodeOut(container);
                });

                return true;
            },

            onNodeOver: function (nodeData, source, e, data) {
                var items = container.items,
                    posY = source.lastPageY;

                items.each(function(item, i) {
                    var dropIndex = items.indexOf(dropProxyEl),
                        itemIndex = items.indexOf(item),
                        itemY = item.el.getY(),
                        itemHeight = item.el.getHeight();

                    if(item === dropProxyEl) {
                        if(posY > itemY + itemHeight) {
                            container.move(dropIndex, items.getCount() - 1);
                        }
                        return true;
                    }

                    if(posY > itemY + itemHeight && dropIndex <= itemIndex) {
                        container.move(dropIndex, itemIndex + 1);
                        return false;
                    }

                    if(posY < itemY && dropIndex > itemIndex) {
                        container.move(dropIndex, itemIndex);
                        return false;
                    }
                });
            }
        });

        container.add(dropProxyEl);

        container.dropProxyEl = dropProxyEl;
    },

    createWidgets: function (model) {
        var me = this,
            views = model.get('views'),
            name = model.get('name'),
            container,
            widget;

        if(!name || !views.length) {
            return;
        }

        Ext.each(views, function(view) {
            container = me.containerCollection.getAt(view.column);

            if(!container) {
                container = me.containerCollection.getAt(0);
            }

            widget = me.createWidget(name, model.get('id'), view);

            container.insert(widget.position.rowId, widget);
        });
    },

    createWidget: function (name, widgetId, record) {
        var me = this,
            config = {
                id: name,
                widgetId: widgetId,
                viewId: record.id,
                title: record.label,
                position: {
                    columnId: record.column,
                    rowId: record.position
                },
                $initialId: record.id,

                draggable: me.createWidgetDragZone()
            };

        return Ext.widget(name, config);
    },

    createWidgetDragZone: function () {
        var me = this;

        return {
            ddGroup: 'widget-container',

            onBeforeDrag: function (data, e) {
                var dragSource = this,
                    widget = data.panel;

                dragSource.originalPosition = widget.el.getXY();
            },

            startDrag: function (e) {
                var dragSource = this,
                        widget = dragSource.panel;

                me.containerCollection.each(function(container, i) {
                    container.dropProxyEl.setHeight(widget.height);

                    if(container.columnId === widget.position.columnId) {
                        container.remove(widget, false);
                    }
                });
            },

            onInvalidDrop: function (e) {
                var dragProxy = this,
                    widget = dragProxy.panel,
                    container = me.containerCollection.getAt(widget.position.columnId);

                container.insert(widget.position.rowId, widget.cloneConfig());
            }
        };
    },

    onDesktopResize: function (desktop, width, height) {
        var me = this,
            offset = me.el ? me.getEl().getTop() : 0,
            maxWidth = width - 10 * 2,
            maxHeight = height - 10 * 2 - offset,
            resizer = me.resizer ? me.resizer.resizeTracker : me.resizable,
            maxColumns;

        me.columnCount = me.getColumnCount(width);
        me.widthStep = ~~(maxWidth / me.columnCount);

        resizer.widthIncrement = me.widthStep;
        resizer.minWidth = me.widthStep;
        resizer.maxHeight = maxHeight;

        me.maxHeight = maxHeight;
        me.minWidth = me.widthStep;

        maxColumns = Math.min(me.columnCount, me.columnsShown);

        me.setWidth(maxColumns * me.widthStep);

        if(me.height > maxHeight) {
            me.setHeight(maxHeight);
        }

        me.onResize(me, me.widthStep * maxColumns);
    },

    getColumnCount: function(width) {
        if(width > 1440) {
            if(width > 1920) {
                return 6;
            }
            return 4;
        }
        return 3;
    },

    onResize: function (win, width) {
        var me = this,
            container;

        me.containerCollection.each(function(el) {
            el.hide();
        });

        me.columnsShown = ~~(width / me.widthStep);

        for(var i = 0; i < me.columnCount; i++) {
            container = me.containerCollection.getAt(i);

            if(i < me.columnsShown && container.isHidden()) {
                container.show();
            }
        }

        if(me.el) {
            me.onScroll({ wheelDelta: 1 });
        }

        me.fireEvent('saveWindowSize', me.columnsShown, me.height);
    },

    createToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            padding: '0 0 10px 0',
            id: 'widget-toolbar',
            cls: Ext.baseCSSPrefix + 'widget-toolbar',

            items: [{
                xtype: 'button',
                cls: 'btn-widget-add',
                menu: {
                    xtype: 'menu',
                    cls: Ext.baseCSSPrefix + 'widget-menu',
                    plain: true,
                    defaultAlign: 'tr-br',
                    items: me.createWidgetMenuItems()
                }
            }, '->', {
                xtype: 'button',
                cls: 'btn-widget-pin' + (me.pinnedOnTop ? ' active' : ''),
                handler: function() {
                    me.fireEvent('fixWindow', me, this);
                }
            }, {
                xtype: 'button',
                cls: 'btn-widget-minimize',
                handler: function() {
                    me.fireEvent('minimizeWindow', me);
                }
            }, {
                xtype: 'button',
                cls: 'btn-widget-position',
                menu: {
                    xtype: 'menu',
                    cls: Ext.baseCSSPrefix + 'widget-position-selection-menu',
                    plain: true,
                    defaultAlign: 'tr-br',
                    items: [{
                        text: 'links oben',
                        iconCls: 'sprite-application-dock-180',
                        handler: function() {
                            me.fireEvent('changePosition', me, false, false);
                        }
                    }, {
                        text: 'links unten',
                        iconCls: 'sprite-application-dock-180',
                        handler: function() {
                            me.fireEvent('changePosition', me, false, true);
                        }
                    }, {
                        text: 'rechts oben',
                        iconCls: 'sprite-application-dock',
                        handler: function() {
                            me.fireEvent('changePosition', me, true, false);
                        }
                    }, {
                        text: 'rechts unten',
                        iconCls: 'sprite-application-dock',
                        handler: function() {
                            me.fireEvent('changePosition', me, true, true);
                        }
                    }]
                }
            }]
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
                 listeners: {
                     checkchange: function(checkbox, status, eOpts) {

                         if (status) {
                             me.fireEvent('addWidget', me, widget.get('name'));
                             return;
                         }

                         me.fireEvent('removeWidget', me, widget.get('name'));
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

        me.registerScrollEvent();
    },

    registerScrollEvent: function() {
        var me = this,
            containerEl = me.getEl(),
            mouseWheelEvent = (/Firefox/i.test(navigator.userAgent)) ? 'DOMMouseScroll' : 'mousewheel'; // Mouse wheel browser detection

        if (document.attachEvent) {
            containerEl.dom.attachEvent('on' + mouseWheelEvent, me.onScroll.bind(me));
        } else if (document.addEventListener) {
            containerEl.dom.addEventListener(mouseWheelEvent, me.onScroll.bind(me), false);
        }
    },

    onScroll: function(e) {
        var me = this,
            containerEl = me.getEl(),
            containerHeight = me.getHeight(),
            widgetContainerEl = me.wrapper.getEl(),
            widgetContainerY = widgetContainerEl.getY(),
            widgetContainerHeight = widgetContainerEl.getHeight(),
            toolbarEl = me.toolbar.getEl(),
            delta = (e.wheelDelta) ? e.wheelDelta : e.detail,
            speed = (e.wheelDelta) ? 0.4 : 10,
            offset = delta * speed,
            position = widgetContainerY + offset,
            min = (widgetContainerHeight - containerHeight - containerEl.getTop()) * -1 - 5,
            max = containerEl.getTop() + toolbarEl.getHeight() + 5,
            style = {
                boxShadow: ''
            };

        if(containerHeight > widgetContainerHeight) {
            widgetContainerEl.setY(max);
            toolbarEl.setStyle(style);
            return;
        }

        position = Math.max(min, Math.min(max, position));

        if(position !== max) {
            style.boxShadow = '0px 10px 10px -7px rgba(0, 0, 0, 0.33)';
        }

        toolbarEl.setStyle(style);

        widgetContainerEl.setY(position);
    }
});
//{/block}