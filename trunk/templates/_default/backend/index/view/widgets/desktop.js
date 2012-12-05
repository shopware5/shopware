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
 * @author shopware AG
 */

/**
 * Shopware UI - Widgets Desktop Container
 *
 * This file holds off the widgets container where
 * all widgets are included.
 */
Ext.define('Shopware.apps.Index.view.widgets.Desktop', {
	extend: 'Ext.container.Container',
	alias: 'widget.widgets-container',
    margin: 10,
    cls: Ext.baseCSSPrefix + 'widget-container',

    /**
     * Collection which holds off all widget container columns.
     * @default null
     * @Ext.util.MixedCollection
     */
    containerCollection: null,

    /**
     * Default layout for the widget desktop.
     * @object
     */
    layout: {
        type: 'hbox',
        pack: 'start',
        align: 'stretch'
    },

    /**
     * Component class name for the widget holder containers.
     * @string
     */
    containerComponent: 'Ext.container.Container',

    /**
     * Initializes the component and adds all necessary icons.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this, j = me.widgetStore.getCount();
        me.items = [];

        me.containerCollection = Ext.create('Ext.util.MixedCollection');

        // Create the container first...
        for(var i = 0; i < me.columnCount; i++) {
            me.items.push(me.createWidgetContainer(i));
        }

        me.addEvents(
            'savePosition'
        );

        //... render the component
        me.callParent(arguments);

        // ... and create the widgets
        while(j--) {
            var data = me.widgetStore.getAt(j);
            me.createWidget(data);
        }
    },

    /**
     * Helper method which renders the widget holder containers.
     *
     * @public
     * @return void
     */
    createWidgetContainer: function(column) {
        var me = this, container, defaults = {
            flex: 1,   // We want same sized columns, so we could use flex
            padding: '10',
            cls: Ext.baseCSSPrefix + 'widget-column-container',
            columnId: column,
            listeners: {
                render: me.createContainerDropZone,
                scope: me
            }
        };
        container = Ext.create(me.containerComponent, defaults);
        me.containerCollection.add(container);

        return container;
    },

    /**
     * Creates the user widgets based on the
     * passed data object.
     *
     * @public
     * @param [object] data - widget data
     */
    createWidget: function(data) {
        var me = this,
            view = data.getViewData(),
            name = data.get('name'),
            config, widget, holder;

        config = view || {};
        if(!name) { return false; }

        // Bind stores
        config.turnoverStore = me.turnoverStore;
        config.visitorsStore = me.visitorsStore;
        config.ordersStore = me.ordersStore;
        config.merchantStore = me.merchantStore;
        config.subApplication = me.subApplication;


        if(config && !Ext.isEmpty(config)) {
            config = me.refactorWidgetConfiguration(config);
            holder = me.containerCollection.getAt(config.position.columnId);

            // If we're having no container for the column, get the last one from the container collection
            if(!holder) {
                holder = me.containerCollection.getAt(me.containerCollection.getCount() - 1);
            }
        }

        widget = Ext.widget(name, config);
        holder.insert(config.position.rowId, widget);

        return [ widget, holder ];
    },

    /**
     * Initialzes the drop zone to allow dropping widgets
     * into the container.
     *
     * @public
     * @param [object] container - Ext.container.Container
     * @return void
     */
    createContainerDropZone: function(container) {
        var me = this, dropProxyEl;

        dropProxyEl = Ext.create('Ext.Component', {
            cls: Ext.baseCSSPrefix + 'widget-proxy-element',
            hidden: true
        });
        container.add(dropProxyEl);

        container.dropZone = Ext.create('Ext.dd.DropZone', container.getEl(), {
            ddGroup: 'widget-container',

            getTargetFromEvent: function() {
                return container;
            },

            onNodeEnter: function(target, dd) {
                var pnl = dd.panel, height = pnl.height;
                target.addCls(Ext.baseCSSPrefix + 'widget-droppable');

                // Always move the drop proxy element to the end of the container
                target.move(target.items.indexOf(dropProxyEl), target.items.getCount() - 1);
                dropProxyEl.show();
                dropProxyEl.addCls('active');
                dropProxyEl.setHeight(height);
            },

            onNodeOut: function(target) {
                target.removeCls(Ext.baseCSSPrefix + 'widget-droppable');
                dropProxyEl.removeCls('active');
                dropProxyEl.hide();
            },

            onNodeOver: function() {
                return Ext.dd.DropZone.prototype.dropAllowed;
            },

            onNodeDrop: function(target, dd, e, data) {
                try {
                    var droppedPanel = dd.panel,
                        panel = droppedPanel.cloneConfig();

                    target.removeCls(Ext.baseCSSPrefix + 'widget-droppable');
                    target.add(panel);

                    // We need to timeout the panel destroying due ExtJS needs the dragged element on drop
                    window.setTimeout(function() {
                        droppedPanel.destroy();
                    }, 2);
                } catch(err) {  }

                // Get new position
                var newColumn = me.containerCollection.getAt(target.columnId),
                    newRow = newColumn.items.getCount() - 2;

                panel.position.rowId = newRow;

                // Fire event which saves the new position
                me.fireEvent('savePosition', target.columnId, newRow, panel.widgetId, panel.authId, panel.$initialId);

                return true;
            }
        });
    },

    /**
     * Helper method which refactors the widget configuration.
     *
     * @private
     * @param [object] config
     */
    refactorWidgetConfiguration: function(config) {
        config.title = config.label || '';
        config.height = 220;
        config.margin = '0 0 20';
        config.$initialId = config.id;
        config.position = {
            columnId: config.column,
            rowId: config.position
        };

        config.draggable = {
            ddGroup: 'widget-container'
        };
        config.widgetId = config.id;
        delete config.label;
        delete config.id;

        return config;
    }
});