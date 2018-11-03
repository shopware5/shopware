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
 * Shopware Widget Window
 *
 * This window contains all active widgets and handles their positioning.
 * It also provides the possibility to easely add and remove widgets over the toolbar.
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

    minHeight: 450,

    /**
     * Contains all widgets
     */
    widgetStore: null,

    desktop: null,

    /**
     * Contains all widget/window settings of all users
     *
     * A single user setting can have the following options:
     * {
     *     authId: 1
     *     height: 500,
     *     columnsShown: 3,
     *     dock: 'br', // b-ottom r-ight
     *     minimized: false
     * }
     */
    widgetSettings: null,

    /**
     * The window toolbar which will be created in the initComponent
     */
    toolbar: null,

    /**
     * Flag if the window content scrolling should be inverted
     */
    invertScroll: false,

    /**
     * Maxmum column amount
     */
    columnCount: 6,

    /**
     * Column amount that should be displayed
     */
    columnsShown: 2,

    /**
     * Ext.util.MixedCollection of all widget containers
     */
    containerCollection: null,

    /**
     * Flag if handles should be initialized
     */
    initHandles: true,

    /**
     * Snippet texts for multilanguage support
     */
    snippets: {
        toolbar: {
            title: '{s name="window/toolbar/headline"}Widgets{/s}',
            minimize: '{s name="window/toolbar/minimize"}Minimize window{/s}',
            dock: {
                topLeft: '{s name="window/toolbar/dock/topLeft"}Top left{/s}',
                topRight: '{s name="window/toolbar/dock/topRight"}Top right{/s}',
                bottomLeft: '{s name="window/toolbar/dock/bottomLeft"}Bottom left{/s}',
                bottomRight: '{s name="window/toolbar/dock/bottomRight"}Bottom right{/s}'
            }
        }
    },

    /**
     * Initializes the window and sets all flags and settings provided by the widgetSettings store.
     * Creates a toolbar with all menus and a wrapper which contains all widget containers.
     * Also registers all needed events to handle resizing.
     * After the callParent was called, every active widget will be created.
     */
    initComponent: function () {
        var me = this,
            settings = me.widgetSettings;

        me.columnsShown = settings.get('columnsShown');
        me.height = settings.get('height');
        me.hidden = settings.get('minimized');

        me.containerCollection = Ext.create('Ext.util.MixedCollection');

        me.toolbar = me.createToolbar();
        me.wrapper = me.createWidgetWrapper();

        me.dockedItems = [ me.toolbar ];
        me.items = [ me.wrapper ];

        me.desktop.on('resize', me.onDesktopResize.bind(me));
        me.on('resize', me.onResize);

        me.addEvents(
            'minimizeWindow',
            'changePosition',
            'saveWidgetPosition',
            'saveWidgetPositions',
            'addWidget',
            'saveWindowSize'
        );

        me.callParent(arguments);

        me.widgetStore.each(me.createWidgets.bind(me));

        me.onDesktopResize(me.desktop, me.desktop.getWidth(), me.desktop.getHeight());

        window.addEventListener('message', Ext.bind(me.onPostMessage, me), false);
    },

    /**
     * Overrides the minimize function of Enlight.app.Window to fix the minimize animation and local storage saving.
     */
    minimize: function () {
        var me = this;

        if (me.minimized) {
            return;
        }

        me.fireEvent('minimizeWindow');
    },

    /**
     * Creates the window toolbar with all its buttons and menus
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function () {
        var me = this,
            snippets = me.snippets.toolbar;

        return Ext.create('Ext.toolbar.Toolbar', {
            padding: '0 0 10px 0',
            id: 'widget-toolbar',
            dock: 'top',
            cls: Ext.baseCSSPrefix + 'widget-toolbar',
            // IE fix
            style: 'background: transparent !important; background-color: transparent !important;',
            items: [
                {
                    xtype: 'button',
                    cls: 'btn-widget-add',
                    menu: {
                        xtype: 'menu',
                        cls: Ext.baseCSSPrefix + 'widget-menu',
                        plain: true,
                        defaultAlign: 'tr-br',
                        items: me.createWidgetMenuItems()
                    }
                },
                {
                    xtype: 'container',
                    html: snippets.title,
                    style: 'font-size: 16px; padding-left: 8px; color: #335064'
                },
                '->',
                {
                    xtype: 'button',
                    tooltip: snippets.minimize,
                    cls: 'btn-widget-minimize',
                    handler: function () {
                        me.fireEvent('minimizeWindow');
                    }
                },
                {
                    xtype: 'button',
                    cls: 'btn-widget-position',
                    menu: {
                        xtype: 'menu',
                        cls: Ext.baseCSSPrefix + 'widget-position-selection-menu',
                        plain: true,
                        defaultAlign: 'tr-br',
                        items: [
                            {
                                text: snippets.dock.topLeft,
                                iconCls: 'sprite-application-dock-180',
                                handler: function () {
                                    me.fireEvent('changePosition', me, 'tl');
                                }
                            },
                            {
                                text: snippets.dock.topRight,
                                iconCls: 'sprite-application-dock',
                                handler: function () {
                                    me.fireEvent('changePosition', me, 'tr');
                                }
                            },
                            {
                                text: snippets.dock.bottomLeft,
                                iconCls: 'sprite-application-dock-180',
                                handler: function () {
                                    me.fireEvent('changePosition', me, 'bl');
                                }
                            },
                            {
                                text: snippets.dock.bottomRight,
                                iconCls: 'sprite-application-dock',
                                handler: function () {
                                    me.fireEvent('changePosition', me, 'br');
                                }
                            }
                        ]
                    }
                }
            ],

            listeners: {
                afterrender: function () {
                    me.fireEvent('changePosition', me, me.widgetSettings.get('dock'), false);
                }
            }
        });
    },

    /**
     * Initializes the Resize handles when the resizer is available
     */
    afterLayout: function () {
        var me = this;

        if (me.resizer && me.initHandles) {
            var dock = me.widgetSettings.get('dock'),
                handles = [],
                verticalHandle = 's',
                horizontalHandle = 'e';


            if (dock.indexOf('b') != -1) {
                verticalHandle = 'n';
            }

            if (dock.indexOf('r') != -1) {
                horizontalHandle = 'w';
            }

            handles.push(verticalHandle);
            handles.push(horizontalHandle);
            handles.push(verticalHandle + horizontalHandle);

            me.handleResizer(handles);

            me.initHandles = false;
        }
    },

    /**
     * Creates the container wrapper and all its widget containers.
     * It is also used for scrolling so we only move the wrapper instead of all containers.
     *
     * @returns { Ext.container.Container }
     */
    createWidgetWrapper: function () {
        var me = this,
            wrapper = Ext.create('Ext.container.Container', {
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                }
            }),
            len = me.columnCount,
            i = 0;

        for (; i < len; i++) {
            var container = me.createWidgetContainer(i);

            wrapper.add(container);
            me.containerCollection.add(container);
        }

        return wrapper;
    },

    /**
     * Creates and returns a widget container, which will contain widgets.
     * After the container was rendered, a new dropZone will be created.
     *
     * @param column
     * @returns { Ext.container.Container }
     */
    createWidgetContainer: function (column) {
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

    /**
     * Creates a new Ext.dd.DropZone which handles the drop logic for the widgets.
     * Also creates a new Ext.Component which will be used as a Drop-Area visualisation.
     *
     * @param container
     */
    createContainerDropZone: function (container) {
        var me = this,
            dropProxyEl = Ext.create('Ext.Component', {
                cls: Ext.baseCSSPrefix + 'widget-proxy-element',
                height: 200
            });

        container.dropZone = Ext.create('Ext.dd.DropZone', container.getEl(), {
            ddGroup: 'widget-container',

            getTargetFromEvent: function () {
                return container;
            },

            onNodeEnter: function (target, dd) {
                dropProxyEl.addCls('active');
            },

            onNodeOut: function (target) {
                var dropIndex = target.items.indexOf(dropProxyEl),
                    lastIndex = target.items.getCount() - 1;

                if (dropIndex != lastIndex) {
                    target.move(dropIndex, lastIndex);
                }

                dropProxyEl.removeCls('active');
            },

            onNodeDrop: function (target, dd, e, data) {
                var dropSource = this,
                    newColumn = target.columnId,
                    newRow = dropSource.dropIndex,
                    panel = dd.panel,
                    newPanel = me.createWidget(panel.xtype, panel.widgetId, {
                        id: panel.viewId,
                        column: newColumn,
                        position: newRow,
                        data: panel.widgetRecord.data
                    }, panel.title);

                target.insert(newRow, newPanel);

                // Fire event which saves the new position
                me.fireEvent('saveWidgetPosition', newColumn, newRow, newPanel.viewId);

                me.containerCollection.each(function (container) {
                    container.dropZone.onNodeOut(container);
                });

                if (me.getEl()) {
                    me.onScroll({ wheelDelta: me.invertScroll ? -1 : 1 });
                }

                Ext.defer(function () {
                    dd.panel.destroy();
                }, 50);

                return true;
            },

            onNodeOver: function (nodeData, source, e, data) {
                var dropSource = this,
                    items = container.items,
                    posY = source.lastPageY;

                items.each(function (item, i) {
                    var dropIndex = items.indexOf(dropProxyEl),
                        itemIndex = items.indexOf(item),
                        itemY = item.el.getY(),
                        itemHeight = item.el.getHeight();

                    if (item === dropProxyEl) {
                        if (posY > itemY + itemHeight && itemIndex !== items.getCount() - 1) {
                            container.move(dropIndex, items.getCount() - 1);
                        }
                        return true;
                    }

                    if (posY > itemY + itemHeight && dropIndex <= itemIndex) {
                        container.move(dropIndex, itemIndex + 1);
                        return false;
                    }

                    if (posY < itemY + (itemHeight / 3) && dropIndex > itemIndex) {
                        container.move(dropIndex, itemIndex);
                        return false;
                    }
                });

                dropSource.dropIndex = items.indexOf(dropProxyEl);
            }
        });

        container.add(dropProxyEl);

        container.dropProxyEl = dropProxyEl;
    },

    /**
     * Iterates all views (widgets) from the passed model, creates them and put them into their right columns.
     * If the set column could not be found (e.g. index out of range), the first column will be used.
     *
     * @param model
     */
    createWidgets: function (model) {
        var me = this,
            views = model.get('views'),
            name = model.get('name'),
            container,
            widget;

        if (!name || !views.length) {
            return;
        }

        Ext.each(views, function (view) {
            container = me.containerCollection.getAt(view.column);

            if (!container) {
                container = me.containerCollection.getAt(0);
                view.column = 0;
            }

            widget = me.createWidget(name, model.get('id'), view, model.get('label'));

            container.insert(widget.position.rowId, widget);
        });
    },

    /**
     * Creates a new widget with settings provided by the parameters
     *
     * @param name
     * @param widgetId
     * @param record
     * @param label
     * @returns { Ext.panel.Panel } - New created widget
     */
    createWidget: function (name, widgetId, record, label) {
        var me = this,
            config = {
                widgetId: widgetId,
                viewId: record.id,
                title: label,
                position: {
                    columnId: record.column,
                    rowId: record.position
                },
                draggable: me.createWidgetDragZone(),
                widgetRecord: record
            };

        return Ext.widget(name, config);
    },

    /**
     * Returns the draggable configuration for a widget, which handles the drag logic.
     * Also handles the case when a invalid drop occures
     *
     * @returns { Object } - DragZone (draggable) configuration
     */
    createWidgetDragZone: function () {
        var me = this,
            scrollTimer,
            delay = 1000;

        return {
            ddGroup: 'widget-container',

            startDrag: function (e) {
                var dragSource = this,
                    widget = dragSource.panel,
                    dropProxyEl;

                me.containerCollection.each(function (container, i) {
                    dropProxyEl = container.dropProxyEl;

                    if (dropProxyEl.height !== widget.lastBox.height) {
                        dropProxyEl.setHeight(widget.lastBox.height);
                    }

                    if (container.columnId === widget.position.columnId) {
                        container.remove(widget, false);
                    }
                });
            },

            onDrag: function (e) {
                var dragSource = this,
                    sourceY = dragSource.lastPageY - dragSource.deltaY,
                    windowBox = me.getEl().getBox(),
                    tolerance = 100,
                    speed = 25;

                if (scrollTimer) {
                    window.clearInterval(scrollTimer);
                    delay = 1000;
                }

                if (sourceY > windowBox.y && sourceY < windowBox.y + tolerance) {
                    dragSource.scrollWrapper(speed);
                }

                if (sourceY < windowBox.y + windowBox.height && sourceY > windowBox.y + windowBox.height - tolerance) {
                    dragSource.scrollWrapper(speed * -1);
                }
            },

            scrollWrapper: function (scrollDelta) {
                var dragSource = this;

                delay = 50;

                scrollTimer = Ext.defer(function () {
                    me.onScroll({
                        wheelDelta: scrollDelta * (me.invertScroll ? -1 : 1)
                    });

                    dragSource.scrollWrapper(scrollDelta);
                }, delay);
            },

            onMouseUp: function () {
                if (scrollTimer) {
                    window.clearInterval(scrollTimer);
                    delay = 1000;
                }
            },

            onInvalidDrop: function (e) {
                var dragProxy = this,
                    widget = dragProxy.panel,
                    pos = widget.position,
                    container = me.containerCollection.getAt(widget.position.columnId),
                    newPanel = me.createWidget(widget.xtype, widget.widgetId, {
                        id: widget.viewId,
                        column: pos.columnId,
                        position: pos.rowId
                    }, widget.title);

                container.insert(pos.rowId, newPanel);
            }
        };
    },

    /**
     * Called when the desktop will be resized.
     * Determines the maximum amount of columns that can be shown depending on the desktop width.
     * Calculates the new width and height limit and the window size will be adjusted.
     * Calls the 'onResize' method for further window / column handling.
     *
     * @param desktop
     * @param width
     * @param height
     */
    onDesktopResize: function (desktop, width, height) {
        var me = this,
            offsetX = 10,
            offsetY = 10,
            maxWidth = width - offsetX * 2,
            maxHeight = Math.max(me.minHeight, (height) - offsetY * 2),
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

        if (me.height > maxHeight) {
            me.setHeight(maxHeight);
        }

        me.onResize(me, me.widthStep * maxColumns);

        if (me.getEl()) {
            me.fireEvent('changePosition', me, me.widgetSettings.get('dock'));
        }
    },

    /**
     * Returns the amount of maximum shown columns depending the desktop width
     *
     * width < 1440 = 3
     * width > 1440 = 4
     * width > 1920 = 5
     * width > 2400 = 6
     *
     * @param width
     * @returns { number } - max amount of columns that can be shown
     */
    getColumnCount: function (width) {
        return Math.min(6, Math.max(3, Math.ceil(width / 480)));
    },

    /**
     * Handles the resizing of the window.
     * Calculates how many columns should be shown and hides the others.
     * Also triggers the 'saveWindowSize' event to save the new window height and amount of shown columns to the localStorage.
     *
     * @param win
     * @param width
     */
    onResize: function (win, width) {
        var me = this,
            oldColumnCount = me.columnsShown,
            container,
            i;

        me.containerCollection.each(function (el) {
            el.hide();
        });

        me.columnsShown = ~~(width / me.widthStep);

        if (oldColumnCount > me.columnsShown) {
            me.moveWidgetsToVisibleSpace(oldColumnCount - me.columnsShown);
        }

        for (i = 0; i < me.columnCount; i++) {
            container = me.containerCollection.getAt(i);

            if (i < me.columnsShown && container.isHidden()) {
                container.show();
            }
        }

        if (me.getEl()) {
            me.onScroll({ wheelDelta: me.invertScroll ? -1 : 1 });
        }

        me.fireEvent('saveWindowSize', me.columnsShown, me.height);
    },

    /**
     * Moves widgets that are in other, not visible, columns into the most right shown column.
     * @param { number } offset - amount of columns changed
     */
    moveWidgetsToVisibleSpace: function (offset) {
        var me = this,
            moveToRight = me.widgetSettings.get('dock').indexOf('l') === -1,
            collection = me.containerCollection,
            widgetsToUpdate = [],
            i;

        // small hack to fix the widget layouts
        Ext.defer(function () {
            Ext.suspendLayouts();

            if (moveToRight) {
                for (i = offset; i > 0; i--) {
                    widgetsToUpdate = widgetsToUpdate.concat(me.prependWidgetsToNewColumn(collection.getAt(i), collection.getAt(i - 1)));
                }
                for (i = offset; i < me.columnCount; i++) {
                    if (collection.getAt(i).items.getCount() <= 1) {
                        continue;
                    }
                    widgetsToUpdate = widgetsToUpdate.concat(me.prependWidgetsToNewColumn(collection.getAt(i), collection.getAt(i - offset)));
                }
            } else {
                for (i = me.columnCount - 1; i > me.columnsShown - 1; i--) {
                    widgetsToUpdate = widgetsToUpdate.concat(me.appendWidgetsToNewColumn(collection.getAt(i), collection.getAt(i - 1)));
                }
            }

            Ext.resumeLayouts(true);

            if (widgetsToUpdate && widgetsToUpdate.length !== 0) {
                me.fireEvent('saveWidgetPositions', widgetsToUpdate);
            }
        }, 1);
    },

    /**
     * Appends all widget of the one column into the other.
     *
     * @param oldColumn
     * @param newColumn
     * @returns { Array }
     */
    appendWidgetsToNewColumn: function (oldColumn, newColumn) {
        var me = this,
            newColumnId = newColumn.columnId,
            widgetsToUpdate = [],
            newRowId;

        oldColumn.items.each(function (widget) {
            if (widget === oldColumn.dropProxyEl) {
                return true;
            }

            newRowId = newColumn.items.getCount() - 1;

            var newWidget = me.createWidget(widget.xtype, widget.widgetId, {
                id: widget.viewId,
                column: newColumnId,
                position: newRowId
            }, widget.title);

            newColumn.insert(newRowId, newWidget);

            widgetsToUpdate.push({
                column: newColumnId,
                position: newRowId,
                id: widget.viewId
            });

            oldColumn.remove(widget, true);
        });

        return widgetsToUpdate;
    },

    /**
     * Prepends all widget of the one column into the other.
     *
     * @param oldColumn
     * @param newColumn
     * @returns { Array }
     */
    prependWidgetsToNewColumn: function (oldColumn, newColumn) {
        var me = this,
            newColumnId = newColumn.columnId,
            widgetsToUpdate = [],
            newRowId = 0,
            rowOffset = oldColumn.items.getCount() - 1,
            newIndex;

        newColumn.items.each(function (widget, index) {
            if (widget === newColumn.dropProxyEl) {
                return true;
            }

            newIndex = index + rowOffset;

            widget.position.rowId = newIndex;

            widgetsToUpdate.push({
                column: newColumnId,
                position: newIndex,
                id: widget.viewId
            });
        });

        oldColumn.items.each(function (widget, index) {
            if (widget === oldColumn.dropProxyEl) {
                return true;
            }

            newRowId = index;

            var newWidget = me.createWidget(widget.xtype, widget.widgetId, {
                id: widget.viewId,
                column: newColumnId,
                position: newRowId
            }, widget.title);

            newColumn.insert(newRowId, newWidget);

            widgetsToUpdate.push({
                column: newColumnId,
                position: newRowId,
                id: widget.viewId
            });

            oldColumn.remove(widget, true);

        });

        return widgetsToUpdate;
    },

    /**
     * Creates the widget menu check items.
     * Iterates the widget store and adds a new 'menucheckitem' to the items array for each widget.
     *
     * @returns { Array } - Array of menu items
     */
    createWidgetMenuItems: function () {
        var me = this,
            items = [];

        me.widgetStore.each(function (widget) {
            if (widget.get('name').indexOf('swag-bi') === -1) {
                items.push({
                    text: widget.get('label'),
                    widgetId: widget.get('id'),
                    iconCls: 'sprite-plus-circle-frame',
                    listeners: {
                        click: function (menuItem) {
                            me.fireEvent('addWidget', me, widget.get('name'), menuItem);
                        }
                    }
                });
            }
        });

        items.push({
            text: '{s name=shopware_bi namespace=backend/index/view/widgets}{/s}',
            widgetId: null,
            iconCls: 'sprite-plus-circle-frame',
            listeners: {
                click: function () {
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Benchmark'
                    });
                }
            }
        });

        return items;
    },

    /**
     * After the window was rendered, its element is available,
     * so we setup the event listener for scrolling.
     */
    afterRender: function () {
        var me = this;

        me.callParent(arguments);

        me.registerScrollEvent();
    },

    /**
     * Attaches the event listener for scrolling to the window element.
     * Sets the 'invertScroll' flag whether we need to invert the scrolling because of some browsers.
     */
    registerScrollEvent: function () {
        var me = this,
            containerEl = me.getEl(),
            invertScroll = /Firefox/i.test(navigator.userAgent),
            mouseWheelEvent = invertScroll ? 'DOMMouseScroll' : 'mousewheel'; // Mouse wheel browser detection

        me.invertScroll = invertScroll;

        containerEl.dom.addEventListener(mouseWheelEvent, me.onScroll.bind(me), false);
    },

    /**
     * Handles the scrolling of the window wrapper.
     * Calculates the new position and sets it if its possible.
     *
     * @param e - Mouse scroll event
     */
    onScroll: function (e) {
        if (this.hidden) {
            return;
        }

        var me = this,
            winEl = me.getEl(),
            winHeight = me.getHeight(),
            wrapperEl = me.wrapper.getEl(),
            wrapperY = wrapperEl.getY(),
            wrapperHeight = wrapperEl.getHeight(),
            toolbarEl = me.toolbar.getEl(),
            delta = (e.wheelDelta) ? e.wheelDelta : e.detail,
            speed = (e.wheelDelta) ? 0.4 : 10,
            offset = (delta * speed) * (me.invertScroll ? -1 : 1),
            position = wrapperY + offset,
            verticalOffset = 5,
            min = (wrapperHeight - winHeight - winEl.getTop()) * -1 - verticalOffset,
            max = winEl.getTop() + toolbarEl.getHeight() + verticalOffset,
            topStyle = me.getPrefixedBoxShadowStyle(''),
            dropProxyEl = me.containerCollection.getAt(0).dropProxyEl.getEl(),
            dropProxyHeight = (dropProxyEl && dropProxyEl.getHeight()) || 200;

        if (winHeight > wrapperHeight) {
            wrapperEl.setY(max);
            toolbarEl.setStyle(topStyle);
            me.removeBodyCls('can-scroll');
            return;
        }

        position = Math.max(min, Math.min(max, position));

        if (position !== max) {
            topStyle = me.getPrefixedBoxShadowStyle('0 14px 4px -8px rgba(0, 0, 0, 0.1)');
        }

        if (position > min + dropProxyHeight) {
            me.addBodyCls('can-scroll');
        } else {
            me.removeBodyCls('can-scroll');
        }

        toolbarEl.setStyle(topStyle);

        wrapperEl.setY(position);
    },

    /**
     * Helper function to get the vendor prefixed box shadow styles.
     *
     * @param style
     * @returns { object }
     */
    getPrefixedBoxShadowStyle: function (style) {
        var vendors = [
                '',
                '-webkit-',
                '-moz-',
                '-ms-',
                '-o-'
            ],
            len = vendors.length,
            i = 0,
            prefixedStyle = {};

        for (; i < len; i++) {
            prefixedStyle[vendors[i] + 'box-shadow'] = style || '';
        }

        return prefixedStyle;
    },

    /**
     * All resizer handles will be hidden and only the passed ones will be shown
     * It's dirty but it works, the is no cleaner way provided by the resizer.
     *
     * @param allowedHandles
     */
    handleResizer: function (allowedHandles) {
        var me = this,
            resizer = me.resizer,
            positions = resizer.possiblePositions,
            pos,
            len = allowedHandles.length,
            i = 0;

        Ext.iterate(positions, function (p) {
            pos = positions[p];

            resizer[pos].hide();
        });

        for (; i < len; i++) {
            pos = positions[allowedHandles[i]];

            resizer[pos].show();
        }
    },

    /**
     * Adds a widget by name
     *
     * @param { object } configuration
     */
    addWidgetByName: function (configuration) {
        this.fireEvent('addWidget', this, configuration.name, null, configuration.data);
    },

    /**
     * Custom post message receiver
     *
     * @param { MessageEvent } message
     */
    onPostMessage: function (message) {
        var me = this,
            data = message.data;

        if (typeof data === 'string' && (data.indexOf('swag-bi') >= 0) || data.indexOf('openBenchmarkModule') >= 0) {
            var widgetInfo = data.split('|');

            if (widgetInfo[0] === 'openBenchmarkModule') {
                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Benchmark',
                    params: {
                        shopId: widgetInfo[1]
                    }
                });
            } else {
                me.addWidgetByName({
                    name: widgetInfo[0],
                    data: {
                        shopId: widgetInfo[1],
                        name: widgetInfo[2],
                        title: widgetInfo[3]
                    }
                })
            }
        }
    }
});

//{/block}
