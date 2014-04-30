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

    minHeight: 600,

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
    initComponent: function() {
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
    },

    /**
     * Creates the window toolbar with all its buttons and menus
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function() {
        var me = this,
            snippets = me.snippets.toolbar;

        return Ext.create('Ext.toolbar.Toolbar', {
            padding: '0 0 10px 0',
            id: 'widget-toolbar',
            cls: Ext.baseCSSPrefix + 'widget-toolbar',
            // IE fix
            style: 'background: transparent !important; background-color: transparent !important;',
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
                tooltip: snippets.minimize,
                cls: 'btn-widget-minimize',
                handler: function() {
                    me.fireEvent('minimizeWindow', me, this);
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
                        text: snippets.dock.topLeft,
                        iconCls: 'sprite-application-dock-180',
                        handler: function() {
                            me.fireEvent('changePosition', me, 'tl');
                        }
                    },  {
                        text: snippets.dock.topRight,
                        iconCls: 'sprite-application-dock',
                        handler: function() {
                            me.fireEvent('changePosition', me, 'tr');
                        }
                    },{
                        text: snippets.dock.bottomLeft,
                        iconCls: 'sprite-application-dock-180',
                        handler: function() {
                            me.fireEvent('changePosition', me, 'bl');
                        }
                    }, {
                        text: snippets.dock.bottomRight,
                        iconCls: 'sprite-application-dock',
                        handler: function() {
                            me.fireEvent('changePosition', me, 'br');
                        }
                    }]
                }
            }],

            listeners: {
                afterrender: function() {
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

        if(me.resizer && me.initHandles) {
            var dock = me.widgetSettings.get('dock'),
                handles =  [],
                verticalHandle = 's',
                horizontalHandle = 'e';


            if(dock.indexOf('b') != -1) {
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
                layout: 'hbox'
            });

        for(var i = 0; i < me.columnCount; i++) {
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

    /**
     * Creates a new Ext.dd.DropZone which handles the drop logic for the widgets.
     * Also creates a new Ext.Component which will be used as a Drop-Area visualisation.
     *
     * @param container
     */
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
                var dropIndex = target.items.indexOf(dropProxyEl),
                    lastIndex = target.items.getCount() - 1;

                if(dropIndex != lastIndex) {
                    target.move(dropIndex, lastIndex);
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
                me.fireEvent('saveWidgetPosition', newColumn, newRow, panel.$initialId);

                me.containerCollection.each(function(container) {
                    container.dropZone.onNodeOut(container);
                });

                return true;
            },

            onNodeOver: function (nodeData, source, e, data) {
                var dropSource = this,
                    items = container.items,
                    posY = source.lastPageY;

                items.each(function(item, i) {
                    var dropIndex = items.indexOf(dropProxyEl),
                        itemIndex = items.indexOf(item),
                        itemY = item.el.getY(),
                        itemHeight = item.el.getHeight();

                    if(item === dropProxyEl) {
                        if(posY > itemY + itemHeight && itemIndex !== items.getCount() - 1) {
                            container.move(dropIndex, items.getCount() - 1);
                        }
                        return true;
                    }

                    if(posY > itemY + itemHeight && dropIndex <= itemIndex) {
                        container.move(dropIndex, itemIndex + 1);
                        return false;
                    }

                    if(posY < itemY + (itemHeight / 3) && dropIndex > itemIndex) {
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

        if(!name || !views.length) {
            return;
        }

        Ext.each(views, function(view) {
            container = me.containerCollection.getAt(view.column);

            if(!container) {
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
                $initialId: record.id,
                draggable: me.createWidgetDragZone()
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
        var me = this;

        return {
            ddGroup: 'widget-container',

            startDrag: function (e) {
                var dragSource = this,
                    widget = dragSource.panel,
                    dropProxyEl;

                me.containerCollection.each(function(container, i) {
                    dropProxyEl = container.dropProxyEl;

                    if(dropProxyEl.height !== widget.lastBox.height) {
                        dropProxyEl.setHeight(widget.lastBox.height);
                    }

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
            maxHeight = (height) - offsetY * 2,
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

        if(me.getEl()) {
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
    getColumnCount: function(width) {
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
            container,
            i;

        me.containerCollection.each(function(el) {
            el.hide();
        });

        me.columnsShown = ~~(width / me.widthStep);

        me.moveWidgetsToVisibleSpace();

        for(i = 0; i < me.columnCount; i++) {
            container = me.containerCollection.getAt(i);

            if(i < me.columnsShown && container.isHidden()) {
                container.show();
            }
        }

        if(me.getEl()) {
            me.onScroll({ wheelDelta: me.invertScroll ? -1 : 1 });
        }

        me.fireEvent('saveWindowSize', me.columnsShown, me.height);
    },

    /**
     * Moves widgets that are in other, not visible, columns into the most right shown column.
     */
    moveWidgetsToVisibleSpace: function () {
        var me = this,
            column,
            newColumn,
            newColumnId,
            widgetsToUpdate = [],
            widgetsToMove = [];

        // small hack to fix the widget layouts
        setTimeout(function() {
            for(var i = me.columnCount - 1; i > me.columnsShown - 1; i--) {
                column = me.containerCollection.getAt(i);

                newColumn = me.containerCollection.getAt(i - 1);
                newColumnId = newColumn.columnId;

                column.items.each(function(widget) {
                    if(widget === column.dropProxyEl) {
                        return true;
                    }

                    var newRowId = newColumn.items.getCount() - 1,
                        newWidget = widget.cloneConfig({
                            position: {
                                columnId: newColumnId,
                                rowId: newRowId
                            }
                        });

                    widgetsToMove.push(newWidget);
                    newColumn.insert(newRowId, newWidget);

                    widgetsToUpdate.push({
                        column: newColumnId,
                        position: newRowId,
                        id: widget.$initialId
                    });

                    column.remove(widget, true);
                });
            }

            if(widgetsToUpdate.length !== 0) {
                me.fireEvent('saveWidgetPositions', widgetsToUpdate);
            }
        }, 0);
    },

    /**
     * Creates the widget menu check items.
     * Iterates the widget store and adds a new 'menucheckitem' to the items array for each widget.
     *
     * @returns { Array } - Array of menu items
     */
    createWidgetMenuItems: function() {
        var me = this,
            items = [];

        me.widgetStore.each(function(widget) {
             items.push({
                 text: widget.get('label'),
                 widgetId: widget.get('id'),
                 iconCls: 'sprite-plus-circle-frame',
                 listeners: {
                     click: function(menuItem) {
                         me.fireEvent('addWidget', me, widget.get('name'), menuItem);
                     }
                 }
             });
        });

        return items;
    },

    /**
     * After the window was rendered, its element is available,
     * so we setup the event listener for scrolling.
     */
    afterRender: function() {
        var me = this;

        me.callParent(arguments);

        me.registerScrollEvent();
    },

    /**
     * Attaches the event listener for scrolling to the window element.
     * Sets the 'invertScroll' flag whether we need to invert the scrolling because of some browsers.
     */
    registerScrollEvent: function() {
        var me = this,
            containerEl = me.getEl(),
            invertScroll = /Firefox/i.test(navigator.userAgent),
            mouseWheelEvent = invertScroll ? 'DOMMouseScroll' : 'mousewheel'; // Mouse wheel browser detection

        me.invertScroll = invertScroll;

        if (document.attachEvent) {
            containerEl.dom.attachEvent('on' + mouseWheelEvent, me.onScroll.bind(me));
        } else if (document.addEventListener) {
            containerEl.dom.addEventListener(mouseWheelEvent, me.onScroll.bind(me), false);
        }
    },

    /**
     * Handles the scrolling of the window wrapper.
     * Calculates the new position and sets it if its possible.
     *
     * @param e - Mouse scoll event
     */
    onScroll: function(e) {
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
            style = {
                boxShadow: ''
            };

        if(winHeight > wrapperHeight) {
            wrapperEl.setY(max);
            toolbarEl.setStyle(style);
            return;
        }

        position = Math.max(min, Math.min(max, position));

        if(position !== max) {
            style.boxShadow = '0px 10px 10px -7px rgba(0, 0, 0, 0.33)';
        }

        toolbarEl.setStyle(style);

        wrapperEl.setY(position);
    },

    /**
     * All resizer handles will be hidden and only the passed ones will be shown
     * It's dirty but it works, the is no cleaner way provided by the resizer.
     *
     * @param allowedHandles
     */
    handleResizer: function(allowedHandles) {
        var me = this,
            resizer = me.resizer,
            positions = resizer.possiblePositions,
            pos,
            i;

        Ext.iterate(positions, function(p) {
            pos = positions[p];

            resizer[pos].hide();
        });

        for(i = 0; i < allowedHandles.length; i++) {
            pos = positions[allowedHandles[i]];

            resizer[pos].show();
        }
    }
});

//{/block}