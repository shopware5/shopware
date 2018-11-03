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
 *
 * @category   Shopware
 * @package    Window
 * @subpackage Plugin
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Window HUD-Panel Plugin
 *
 * The component is a part of the wireframe components for the
 * Shopware backend.
 *
 * This plugin provides a library window on the right side of the window.
 * The library  provides different draggable items (based on the passed store).
 *
 * Note: The drop zones (use Ext.dd.DropTarget) and the window content
 * are in the responsibility of each developer !!!
 */
Ext.define('Shopware.window.plugin.Hud', {

    /**
     * Base CSS class for the window
     * @string
     */
    windowCls: Ext.baseCSSPrefix + 'hub-pnl',
    /**
     * CSS Class for the hub library panel
     * @string
     */
    hudCls: Ext.baseCSSPrefix + 'hub-library',

    /**
     * Title for the Hub Panel
     * @string
     */
    hudTitle: 'Elemente-Bibliothek',

    /**
     * Height (in pixels) for the Hub Panel
     * @integer
     */
    hudHeight: 325,

    /**
     * Width (in pixels) of the Hub Panel
     * @integer
     */
    hudWidth: 225,

    /**
     * Left offset (in pixels) to the containing window
     * @integer
     */
    hudOffset: 10,

    /**
     * Indicates if the hub panel will be shown on start up
     * @boolean
     */
    hudShow: true,

    /**
     * Template for the libray view
     * @string
     */
    tpl: '{literal}<ul><tpl for="."><li class="drag-item">{name}</li></tpl></ul>{/literal}',

    /**
     * Item selector for the library view
     * @string
     */
    itemSelector: '.x-library-element',

    /**
     * Translate hudStore error message
     *
     * @param config
     */
    hudStoreErrorMessage: function(className) {
        return className + ' needs the property "hudStore" which represents the store used by the hub panel to create the draggable items.';
    },

    /**
     * Applies the user configuration to the default plugin
     * configuration.
     *
     * @public
     * @return void
     */
    constructor: function(config) {
        Ext.apply(this, config);
    },

    /**
     * Initialize the hub plugin
     *
     * @private
     * @return void
     */
    init: function(view) {
        var me = this;
        me.ownerView = view;
        me.ownerView.addCls(me.windowCls);
        me.ownerView.on('show', me.setupLibraryWindow, me);
        me.ownerView.hubPnl = me;
    },

    /**
     * Creates the drag zones for the library items
     *
     * @private
     * @param [object] view - the data view which represents the library window
     * @return void
     */
    setupLibraryWindow: function() {
        var me = this,
            el = me.ownerView,
            extEl = Ext.get(el.getEl().dom);

        // Check if the hudStore is defined
        if(!me.hudStore || Ext.isEmpty(me.hudStore)) {
            Ext.Error.raise(me.hudStoreErrorMessage(me.$className));
            return false;
        }

        // Create data view to display the store
        me.libraryView = Ext.create('Ext.view.View', {
            tpl: me.tpl,
            store: me.hudStore,
            itemSelector: me.itemSelector,
            overItemCls: Ext.baseCSSPrefix + 'item-over',
            trackOver: true,
            onItemSelect: function(record) {
                var node = this._selectedNode; //this.getNode(record);

                if (node) {
                    Ext.fly(node).addCls(this.selectedItemCls);
                }
            },

            onItemDeselect: function(record) {
                var node = this._deselectedNode; //this.getNode(record);

                if (node) {
                    Ext.fly(node).removeCls(this.selectedItemCls);
                }
            },

            processItemEvent: function(record, item, index, e) {
                if (e.type == "mousedown" && e.button == 0) {
                    this._deselectedNode = this._selectedNode;
                    this._selectedNode = item;
                }
            },

            updateIndexes : function(startIndex, endIndex) {
                var ns = this.all.elements,
                    records = this.store.getRange(),
                    tmpRecords = [],
                    i, j;

                Ext.each(records, function(item) {
                    var children = item.get('children');

                    Ext.each(children, function(child) {
                        tmpRecords.push(child);
                    });
                });
                records = tmpRecords;

                startIndex = startIndex || 0;
                endIndex = endIndex || ((endIndex === 0) ? 0 : (ns.length - 1));
                for(i = startIndex, j = startIndex - 1; i <= endIndex; i++){
                    if (Ext.fly(ns[i]).is('.x-library-element')) {
                        j++;
                    }

                    ns[i].viewIndex = i;

                    ns[i].viewRecordId = records[j].internalId;
                    if (!ns[i].boundView) {
                        ns[i].boundView = this.id;
                    }
                }
            },
            listeners: {
                scope: me,
                render: me.initializeDragZones,
                afterrender: me.addAdditionalEvents
            }
        });

        // Create the library panel
        me.libraryPnl = Ext.create('Ext.panel.Panel', {
            height: me.hudHeight,
            unstyled: true,
            preventHeader: true,
            width: me.hudWidth,
            cls: me.hudCls,
            autoScroll: true,
            style: 'position: absolute; top:0px; right: -' + (me.hudWidth + me.hudOffset) + 'px;',
            renderTo: extEl,
            items: [ me.libraryView ]
        });

        // Hide the libary panel on start up
        if(!me.hudShow) {
            me.libraryPnl.hide();
        }

        // Remove the overflow from the window component to get a visible area for the library panel
        extEl.setStyle('overflow', 'visible');
        el.libraryPnl = me.libraryPnl;
    },

    /**
     * Creates the drag zones for the library items
     *
     * @param [object] view - the data view which represents the library window
     * @return void
     */
    initializeDragZones: function(view) {
        var me = this, draggedElement;

        view.dragZone = new Ext.dd.DragZone(view.getEl(), {
            ddGroup: 'emotion-dd',
            proxyCls: Ext.baseCSSPrefix + 'emotion-dd-proxy',

            onDragStart: function() { },

            /**
             * On receipt of a mousedown event, see if it is within a draggable element.
             * Return a drag data object if so. The data object can contain arbitrary application
             * data, but it should also contain a DOM element in the ddel property to provide
             * a proxy to drag.
             */
            getDragData: function(event) {
                var source = event.getTarget(view.itemSelector, 10), d, element = Ext.get(source),
                    id, attr, i;

                var proxy = view.dragZone.proxy;
                if(!proxy.getEl().hasCls(Ext.baseCSSPrefix + 'shopware-dd-proxy')) {
                    proxy.getEl().addCls(Ext.baseCSSPrefix + 'shopware-dd-proxy')
                }

                if(!source || !element) { return false; }

                id = ~~(1 * element.getAttribute('data-componentId'));

                if(!id) {
                    for(i in element.dom.attributes) {
                        attr = element.dom.attributes[i];
                        if(attr.name == 'data-componentid') {
                            id = parseInt(attr.value, 10);
                            break;
                        }
                    }
                }

                d = source.cloneNode(true);
                d.id = Ext.id();

                // Add our custom style to the dragged element
                element.addCls('dragged');
                draggedElement = element;
                element.on('click', function() { this.removeCls('dragged') }, element, { single: true });

                return {
                    ddel: d,
                    sourceEl: source,
                    repairXY: Ext.fly(source).getXY(),
                    sourceStore: view.store,
                    draggedRecord: me.originalStore.getById(id)
                }
            },
            /**
             * Provide coordinates for the proxy to slide back to on failed drag.
             * This is the original XY coordinates of the draggable element captured
             * in the getDragData method.
             *
             * @return [array] coordinates (X and Y)
             */
            getRepairXY: function(event) {
                var source = event.getTarget(view.itemSelector, 10), element = Ext.get(source);

                // We need to remove the dragged cls from the element by hand to prevent displaying issues
                if(draggedElement && draggedElement.hasCls('dragged')) {
                    Ext.defer(function() {
                        draggedElement.removeCls('dragged');
                    }, 50);
                }
                return this.dragData.repairXY;
            }
        });
    },

    /**
     * Shows the library panel
     *
     * @public
     * @return void
     */
    showPanel: function() {
        this.fireEvent('beforeShowHud', this.libraryView);
        this.libraryPnl.show();
        this.fireEvent('afterShowHud', this.libraryView);
    },

    /**
     * Hides the library panel
     *
     * @public
     * @return void
     */
    hidePanel: function() {
        this.fireEvent('beforeHideHud', this.libraryView);
        this.libraryPnl.hide();
        this.fireEvent('afterHideHud', this.libraryView);
    },

    addAdditionalEvents: function() {
        var me = this,
            me = me.libraryView;

        me.getEl().on({

            /**
             * Event listener method which gives the toggle
             * it's functionality.
             *
             * @event click
             */
            'click': {
                delegate: '.toggle',
                fn: function(event, element) {
                    var el = Ext.get(element),
                        parent = el.parent(),
                        panel = parent.parent().child('.x-library-inner-panel')

                    Ext.suspendLayouts();
                    if(panel.isVisible()) {
                        el.addCls('inactive');
                        el.removeCls('active');
                        panel.setStyle('display', 'none');
                    } else {
                        el.addCls('active');
                        el.removeCls('inactive');
                        panel.setStyle('display', 'block');
                    }
                    Ext.resumeLayouts(true);
                }
            }
        });
    }
});
