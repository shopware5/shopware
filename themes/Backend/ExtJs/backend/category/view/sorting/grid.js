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
 * @package    Category
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/sorting} */

//{block name="backend/category/view/sorting/grid"}
Ext.define('Shopware.apps.Category.view.sorting.Grid', {
    /**
     * Define that the product list is an extension of the Ext.panel.Panel
     * @string
     */
    extend: 'Ext.panel.Panel',

    /**
     * Register the alias for this class.
     * @string
     */
    alias: 'widget.manual-sort-products-grid',

    /**
     * Set no border for the window
     * @boolean
     */
    border: false,

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll: true,

    dragOverCls: 'drag-over',

    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'product-sort',

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.viewConfig = {
            plugins: {
                ptype: 'gridviewdragdrop',
                ddGroup: 'Product',
                enableDrop: true
            }
        };

        me.items = [me.createProductView()];
        me.dockedItems = [me.getPagingBar()];

        me.callParent(arguments);
    },

    /**
     * Creates the product listing based on an Ext.view.View (know as DataView)
     * and binds the "Product"-store to it
     *
     * @return [object] this.dataView - created Ext.view.View
     */
    createProductView: function () {
        var me = this;

        me.dataView = Ext.create('Ext.view.View', {
            disabled: false,
            itemSelector: '.product-box',
            name: 'image-listing',
            multiSelect: true,
            store: me.store,
            tpl: me.createProductViewTemplate(),
            listeners: {
                itemclick: function (view, record, item, idx, event) {
                    if (event.target.classList.contains('pin')) {
                        me.fireEvent('unpin', record);
                    }
                }
            }
        });

        me.initDragAndDrop();

        return me.dataView;
    },

    /**
     * Creates the template for the product view panel
     *
     * @return { Ext.XTemplate } generated Ext.XTemplate
     */
    createProductViewTemplate: function () {
        var me = this;

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
            // If the type is image, then show the image
            '<div class="product-box">',
            '<tpl if="values.position !== null">',
            '<a href="#" title="{/literal}{s name="remove_position"}{/s}{literal}" class="sprite-cross pin"></a>',
            '</tpl>',
            '<div class="image-container">',
            '<tpl if="values.thumbnail == null">',
            '<img src="', '{/literal}{link file="backend/_resources/images/index/no-picture.jpg"}{literal}', '', '" />',
            '<tpl else>',
            '<img src="', '{link file=""}', '{literal}{thumbnail}{/literal}', '" />',
            '</tpl>',
            '</div>',
            '<div class="product-name">',
            '<span>{[Ext.util.Format.ellipsis(values.name, 50)]}</span>',
            '</div>',
            '<a class="paging prev-page sprite-arrow-turn-180-left{[this.prevPage()]}" title="{/literal}{s name="move_to_prev_page"}{/s}{literal}"></a>',
            '<a class="paging next-page sprite-arrow-turn{[this.nextPage()]}" title="{/literal}{s name="move_to_next_page"}{/s}{literal}"></a>',
            '</div>',
            '</tpl>',
            '<div class="x-clear"></div>{/literal}',
            {
                // Add class if current product is on first page in store list
                prevPage: function () {
                    if (me.store.currentPage === 1) {
                        return ' x-hidden';
                    }
                },

                nextPage: function () {
                    var lastPage = Math.ceil(me.store.totalCount / me.store.pageSize);

                    if (lastPage <= me.store.currentPage || lastPage === 1) {
                        return ' x-hidden';
                    }
                },
            }
        );
    },

    /**
     * Create trigger catch when fast move button is click
     */
    registerMoveActions: function () {
        var me = this;

        var el = me.el;
        // Trigger event when "move to start" action is clicked
        el.on('click', function (event, target) {
            if (target.classList.contains('disabled')) {
                return false;
            }
            event.stopEvent();
            me.fireEvent('moveToPrevPage', me.store);
        }, null, {
            delegate: 'a.prev-page'
        });

        // Trigger event when "move to end" action is clicked
        el.on('click', function (event, target) {
            if (target.classList.contains('disabled')) {
                return false;
            }
            event.stopEvent();
            me.fireEvent('moveToNextPage', me.store);
        }, null, {
            delegate: 'a.next-page'
        });
    },

    getPagingBar: function () {
        var comboStore = Ext.create('Ext.data.Store', {
            fields: [ 'value', 'display' ],
            data: [
                { value: 25, display: '25' },
                { value: 50, display: '50' },
                { value: 100, display: '100' },
            ]
        });

        var combo = Ext.create('Ext.form.field.ComboBox', {
            store: comboStore,
            valueField: 'value',
            displayField: 'display',
            fieldLabel: '{s name="items_per_page" name=backend/customer/view/main}{/s}',
            labelStyle: 'margin-top: 2px',
            width: 220,
            labelWidth: 110,
            value: 25,
            listeners: {
                scope: this,
                change: Ext.bind(this.onPerPageChange, this)
            }
        });

        this.toolbar = Ext.create('Ext.toolbar.Paging', {
            store: this.store,
            dock: 'bottom',
            displayInfo: true
        });

        this.toolbar.add([{ xtype: 'tbspacer' }, combo]);

        return this.toolbar;
    },

    onPerPageChange: function(comp, newValue) {
        var me = this;

        me.store.pageSize = newValue;
        me.store.load();
    },

    /**
     * Creates the drag and drop zone for the Ext.view.View to allow
     */
    initDragAndDrop: function () {
        var me = this;

        me.dataView.on('afterrender', function (v) {
            var selModel = v.getSelectionModel();
            me.dataView.dragZone = new Ext.dd.DragZone(v.getEl(), {
                ddGroup: 'Product',
                getDragData: function (e) {
                    var sourceEl = e.getTarget(v.itemSelector, 10);
                    if (sourceEl) {
                        var selected = selModel.getSelection(),
                            record = v.getRecord(sourceEl);

                        if (!selected.length) {
                            selModel.select(record);
                            selected = selModel.getSelection();
                        }

                        var d = sourceEl.cloneNode(true);
                        d.id = Ext.id();

                        return {
                            ddel: d,
                            sourceEl: sourceEl,
                            repairXY: Ext.fly(sourceEl).getXY(),
                            sourceStore: v.store,
                            draggedRecord: v.getRecord(sourceEl),
                            productModels: selected
                        };
                    }
                },
                getRepairXY: function () {
                    return this.dragData.repairXY;
                }
            });

            me.dataView.dropZone = new Ext.dd.DropZone(me.dataView.getEl(), {
                ddGroup: 'Product',
                getTargetFromEvent: function (e) {
                    return e.getTarget(me.dataView.itemSelector);
                },

                onNodeEnter: function (target, dd, e, data) {
                    var record = me.dataView.getRecord(target);
                    if (record !== data.draggedRecord) {
                        Ext.fly(target).addCls(me.dragOverCls);
                    }
                },

                onNodeOut: function (target) {
                    Ext.fly(target).removeCls(me.dragOverCls);
                },

                onNodeDrop: function (target, dd, e, data) {
                    var draggedRecord = me.dataView.getRecord(target);
                    var productModels = data.productModels;

                    me.fireEvent('drop', me.store, productModels, draggedRecord);
                }
            });

            me.registerMoveActions();
        });
    },

    reconfigure: function (store) {
        this.store = store;
        this.dataView.bindStore(store);
        this.toolbar.bindStore(store);
    }
});
//{/block}
