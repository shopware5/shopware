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
 * @package    Base
 * @subpackage Component
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - DragAndDropSelector Panel
 *
 * todo@all: Documentation
 *
 *
 * @example
 * Ext.create('Shopware.panel.DragAndDropSelector', {
 *      name: 'supplierIds',
 *      fromTitle: 'Supplier Available',
 *      toTitle: 'Supplier Chosen',
 *      fromStore:this.supplierStore,
 *      buttons:[ 'add','remove' ],
 *      gridHeight: 270,
 *      selectedItems: me.getStore('Something'),
 *      buttonsText: {
 *      add: "Add",
 *      remove: "Remove"
 * }
 * });
 */
Ext.define('Shopware.DragAndDropSelector',
{
    /**
     * Based on Ext.panel.Panel
     */
    extend:'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     * @array
     */
    alias: [ 'widget.draganddropselector', 'widget.ddselector' ],

    /**
     * From-Store which holds the Available Data
     *
     * @default null
     * @object
     */
    fromStore: null,

    /**
     * To-Store which holds the Selected Data
     *
     * @default null
     * @object
     */
    toStore: null,

    /**
     * Grid on the left side
     *
     * @default null
     * @object
     */
    fromField: null,

    /**
     * DockedItems for the grid on the left side
     *
     * @default null
     * @object
     */
    fromFieldDockedItems: null,

    /**
     * Grid on the right side
     *
     * @default null
     * @object
     */
    toField: null,

    /**
     * DockedItems for the grid on the right side
     *
     * @default null
     * @object
     */
    toFieldDockedItems: null,

    /**
     * Recordset with all selected items
     *
     * @default null
     * @object
     */
    selectedItems: null,

    /**
     * FromTitle which holds Title on the Left Side
     *
     * @string
     */
    fromTitle: '',

    /**
     * toTitle which holds Title on the Right Side
     *
     * @string
     */
    toTitle: '',


    /**
     * default columns for the from grid
     */
    fromColumns :[{
        text: 'name',
        flex: 1,
        dataIndex: 'name'
    }],

    /**
     * default columns for the to grid
     */
    toColumns :[{
        text: 'name',
        flex: 1,
        dataIndex: 'name'
    }],

    /**
     * hides the header of the grids
     */
    hideHeaders: true,
    /**
     * height of the grid panel
     *
     * @integer
     */
    gridHeight: null,

    /**
     * show paging toolbar
     */
    showPagingToolbar: false,

    /**
     * standard layout
     */
    layout:{
        type:'hbox',
        align: 'stretch'
    },

    /**
     * available buttons
     *
     * @object
     */
    buttons: ['top', 'up', 'add', 'remove', 'down', 'bottom'],

    /**
     * default button texts
     *
     * @object
     */
    buttonsText: {
        top: "Move to Top",
        up: "Move Up",
        add: "Add to Selected",
        remove: "Remove from Selected",
        down: "Move Down",
        bottom: "Move to Bottom"
    },

    /**
     * Init the component
     */
    initComponent : function() {
        var me = this;
        me.toStore = me.selectedItems;

        //to set the usedIds to the store
        me.refreshStore();
        me.fromStore.load();

        var config = {
            title: me.fromTitle,
            store: me.fromStore,
            columns :me.fromColumns,
            dockedItems: me.fromFieldDockedItems,
            border: false,
            viewConfig: {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    dragGroup: 'firstGridDDGroup',
                    dropGroup: 'secondGridDDGroup'
                },
                listeners: {
                    drop: function() {
                        me.refreshStore();
                    }
                }
            }
        };

        if (me.showPagingToolbar) {
            config.bbar = Ext.create('Ext.toolbar.Paging', {
                store: me.fromStore,
                displayInfo: true
            });
        }

        me.fromField = me.createGrid(config);

        me.toField = me.createGrid({
            title : me.toTitle,
            store : me.toStore,
            columns : me.toColumns,
            dockedItems: me.toFieldDockedItems,
            border: false,
            viewConfig: {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    dragGroup: 'secondGridDDGroup',
                    dropGroup: 'firstGridDDGroup'
                },
                listeners: {
                    drop: function() {
                        me.refreshStore();
                    }
                }
            }
        });
        // Create the view
        me.items = [
            me.fromField,
            me.getMiddleButtons(),
            me.toField
        ];

        me.callParent(arguments);
    },
    /**
     * The two grids are separated by a container which contains to buttons.
     * One button is used to move the selection from the left side to the right side and the second button does this vice-versa.
     *
     * @return Ext.container.Container
     */
    getMiddleButtons : function() {
        var me = this;
        return Ext.create('Ext.container.Container',{
            margins: '0 4',
            width: 22,
            layout: {
                type: 'vbox',
                pack: 'center'
            },
            items: me.createButtons()
        });
    },
    /**
     * Creates all adjusted buttons
     *
     * @return Ext.Button
     */
    createButtons: function(){
        var me = this,
            buttons = [];

        Ext.Array.forEach(me.buttons, function(name) {
            var button = Ext.create('Ext.Button', {
                tooltip: me.buttonsText[name],
                cls: Ext.baseCSSPrefix + 'form-itemselector-btn',
                iconCls: Ext.baseCSSPrefix + 'form-itemselector-' + name,
                navBtn: true,
                margin: '4 0 0 0'
            });
            button.addListener('click',  me['on' + Ext.String.capitalize(name) + 'BtnClick'], me);
            buttons.push(button);
        });
        return buttons;
    },
    /**
     * Creates a grid with the adjusted config
     *
     * @return Ext.grid.Panel
     */
    createGrid: function(config) {
        var me = this;
        var defaultConfig = {
            stripeRows : true,
            multiSelect: true,
            hideHeaders: me.hideHeaders,
            height: me.gridHeight,
            flex: 1
        };
        defaultConfig = Ext.apply(defaultConfig, config);
        var gridPanel = Ext.create('Ext.grid.Panel', defaultConfig);
        gridPanel.addListener('itemdblclick',  me.onItemDblClick, me);
        return gridPanel;
    },

    /**
     * Event listener on item double click
     * Moves the selected Items from grid to grid
     *
     * @param view
     * @param rec
     */
    onItemDblClick: function(view, rec){
        var me = this,
            from = me.fromStore,
            to = me.toStore,
            current,
            destination;
        if (view.store === me.fromField.store) {
            current = from;
            destination = to;
        } else {
            current = to;
            destination = from;
        }
        current.remove(rec);
        destination.add(rec);
        me.refreshStore();
    },

    /**
     * Event listener on add button click
     * Moves the selected Items from grid to grid
     */
    onAddBtnClick : function() {
        var me = this,
            fromList = me.fromField,
            selected = this.getSelections(fromList);

        //performance fix because ext js is slow in removing single things
        var storeItems = me.fromStore.data.items;
        me.fromStore.removeAll();
        //storeItems contains now an array of Ext.data.Model
        storeItems = me.fastRemoveStoreItems(storeItems, selected);
        me.fromStore.add(storeItems);
        me.toStore.add(selected);
        me.refreshStore();
    },

    /**
     * Event listener on Remove button click
     * Moves the selected Items from grid to grid
     */
    onRemoveBtnClick : function() {
        var me = this,
            toList = me.toField,
            selected = me.getSelections(toList);

        //performance fix because ext js is slow in removing single things
        var storeItems = me.toStore.data.items;
        me.toStore.removeAll();
        //storeItems contains now an array of Ext.data.Model
        storeItems = me.fastRemoveStoreItems(storeItems, selected);
        me.toStore.add(storeItems);
        me.fromStore.add(selected);
        me.refreshStore();
    },

    /**
     * Returns the selected Items
     *
     * @return Ext.Array
     */
    getSelections: function(list){
        var store = list.getStore(),
            selections = list.getSelectionModel().getSelection();

        return Ext.Array.sort(selections, function(a, b){
            a = store.indexOf(a);
            b = store.indexOf(b);

            if (a < b) {
                return -1;
            } else if (a > b) {
                return 1;
            }
            return 0;
        });
    },

    /**
     * Refreshes the Store so send the latest selected items with the search
     */
    refreshStore: function() {
        var me = this,
            ids = [];
        if(me.toStore != null) {
            me.selectedItems = me.toStore;
        }
        if(me.selectedItems != null){
            me.selectedItems.each(function(element) {
                ids.push(element.get('id'));
            });
        }
        me.fromStore.getProxy().extraParams = {
            'usedIds[]': ids
        };
    },

    /**
     * removes the selectedItems from the storeItems
     */
    fastRemoveStoreItems: function(storeItems, selected) {
        var toRemove = [];

        //performance fix because ext js is slow in removing single things
        for(var i in storeItems) {
            var select = storeItems[i];
            Ext.each(selected, function(item) {
                if(select.get('id') === item.get('id')) {
                    toRemove.unshift(i);
                }
            });
        }

        Ext.each(toRemove, function(index) {
            Ext.Array.erase(storeItems, index, 1);
        });
        return storeItems;
    },

    /**
     * Destroys the DragAndDropSelector panel
     *
     * @public
     * @return void
     */
    destroy: function() {
        this.fromStore = null;
        Ext.destroyMembers(this, 'fromField', 'toField');
        this.callParent();
    }
});
