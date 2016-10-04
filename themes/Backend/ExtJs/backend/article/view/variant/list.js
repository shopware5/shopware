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
 * @package    Article
 * @subpackage Variants
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page
 * The variant list component is the listing component for the created article variants.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/variant/list"}
Ext.define('Shopware.apps.Article.view.variant.List', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
    */
    alias:'widget.article-variant-list',

    /**
     * Set css class
     * @string
     */
    cls:Ext.baseCSSPrefix + 'article-variant-list',

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll:true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        regexNumberValidation: '{s name=detail/base/regex_number_validation}The inserted article number contains illegal characters!{/s}',
        columns:{
            number:'{s name=variant/list/column/number}Order number{/s}',
            stock:'{s name=variant/list/column/stock}Stock{/s}',
            price: {
                header: '{s name=variant/list/column/price}Price{/s}',
                undefined: '{s name=variant/list/column/price_undefined}Undefined{/s}',
                from: '{s name=variant/list/column/price_from}From{/s}'
            },
            pseudoPrice: {
                header: '{s name=variant/list/column/pseudoprice}Pseudoprice{/s}',
                undefined: '{s name=variant/list/column/pseudoprice_undefined}Undefined{/s}',
            },
            standard: '{s name=variant/list/column/standard}Preselection{/s}',
            active: '{s name=variant/list/column/active}Active{/s}',
            remove: '{s name=variant/list/column/remove}Remove variant{/s}',
            edit: '{s name=variant/list/column/edit}Edit variant{/s}'
        },
        toolbar:{
            add:'{s name=variant/list/toolbar/button_add}Add{/s}',
            remove:'{s name=variant/list/toolbar/button_delete}Delete all selected{/s}',
            search:'{s name=variant/list/toolbar/search_empty_text}Search...{/s}',
            data:'{s name=variant/list/toolbar/data}Apply standard data{/s}',
            save:'{s name=variant/list/toolbar/save}Save changes{/s}',
            orderNumber: {
                field: '{s name=variant/list/toolbar/order_field}Apply standard prices{/s}',
                button: '{s name=variant/list/toolbar/order_button}Regenerate order numbers{/s}',
                empty: '{s name=variant/list/toolbar/order_empty}mainDetail.number{/s}'
            }
        },
        saved: {
            errorMessage: '{s name=article_saved/error_message}An error has occurred while saving the article:{/s}',
            errorTitle: '{s name=article_saved/error_title}Error{/s}',
            ordernumberNotMatch: '{s name=detail/base/regex_number_validation}The inserted article number contains illegal characters!{/s}'
        }
    },

    /**
     * Initialize the Shopware.apps.Article.view.variant.List and defines the necessary default configuration
     * @return void
     */
    initComponent:function () {
        var me = this,
            mainWindow = me.subApp.articleWindow;

        mainWindow.on('storesLoaded', me.onStoresLoaded, me);
        me.configuratorGroupStore = mainWindow.configuratorGroupStore;

        me.registerEvents();
        me.columns = me.getColumns(true);

        me.toolbar = me.getToolbar();
        me.pagingbar = me.getPagingBar();
        me.plugins = [ me.createCellEditor() ];
        me.dockedItems = [ me.toolbar, me.pagingbar ];
        me.callParent(arguments);
    },

    /**
     * Creates the cell editor the grid panel.
     *
     * @public
     * @return [object] Ext.grid.plugin.CellEditing
     */
    createCellEditor: function() {
        var me = this;

        me.cellEditor = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToMoveEditor: 1,
            autoCancel: true
        });

        //register listener on the edit event to save the record and convert the price value. Without
        //this listener the insert price "10,55" would be become "1055"
        me.cellEditor.on('edit', function(editor, e) {
            if (e.value && e.field === 'price') {
                var newPrice = Ext.Number.from(e.value);
                newPrice = Ext.Number.toFixed(newPrice, 2);

                var oldPrice = Ext.Number.from(e.originalValue);
                oldPrice = Ext.Number.toFixed(oldPrice, 2);

                if (newPrice != oldPrice) {
                    me.fireEvent('editVariantPrice', e.record, newPrice);
                }

            } else if (e.field === 'pseudoPrice') {
                var newPseudoPrice = Ext.Number.toFixed(0, 2);
                var oldPseudoPrice = null;

                if(e.value !== null) {
                    newPseudoPrice = Ext.Number.from(e.value);
                    newPseudoPrice = Ext.Number.toFixed(newPseudoPrice, 2);

                    oldPseudoPrice = Ext.Number.from(e.originalValue);
                    if(! Ext.isDefined(e.originalValue) || e.originalValue === null) {
                        oldPseudoPrice = 0;
                    }

                    oldPseudoPrice = Ext.Number.toFixed(oldPseudoPrice, 2);
                }

                if (newPseudoPrice !== oldPseudoPrice || newPseudoPrice === 0.00) {
                    me.fireEvent('editVariantPseudoPrice', e.record, newPseudoPrice);
                }

            } else {
                var oldValue = e.originalValue,
                    newValue = e.value;

                //the number field is a mapping field of the variant. so we have to map this field
                if (e.field === 'details.number') {
                    oldValue = e.record.get('number');
                    newValue = e.record.get('details.number') || e.record.get('number')
                }

                if(e.field === 'details.number' &&  (!newValue || !newValue.match(/^[a-zA-Z0-9-_. ]+$/))) {
                    Shopware.Notification.createGrowlMessage(me.snippets.saved.errorTitle, me.snippets.saved.ordernumberNotMatch, me.snippets.growlMessage);
                    e.record.set('number', oldValue);
                    e.record.set('details.number', oldValue);
                    return;
                }

                if (oldValue === newValue) {
                    return;
                }

                if (e.field === 'details.number') {
                    e.record.set('number', newValue);
                }

                me.fireEvent('saveVariant', e.record);
            }
        });

        /**
         * Event listener which filters the html tags from the number value.
         */
        me.cellEditor.on('beforeedit', function(editor, e) {
            if(e.field === 'details.number') {

                // We need to defer the function call to make sure that the editor is rendered
                // and the value is loaded.
                Ext.defer(function() {

                    editor.editors.each(function(ed) {
                        ed.validationRequestParam = e.record.get('id');
                        ed.setValue(e.record.get('number'));
                    });
                }, 50);
            }
        }, me);

        return me.cellEditor;
    },

    /**
     * Called when the user creates the variants. This function recreates the grid columns.
     */
    refreshColumns: function() {
        var me = this;
        me.getSelectionModel().deselectAll();
        me.reconfigure(me.getStore(), me.getColumns(true));
    },

    /**
     * Defines additional events which will be fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the delete button in the toolbar or
             * use the action column of the grid to remove one or multiple variants
             * @event deleteVariant
             * @param [array] Record - The selected records
             */
            'deleteVariant',

            /**
             * @event saveVariant
             */
            'saveVariant',

            /**
             * Event will be fired when the user insert a value into the search field of the toolbar
             * to filter the listing.
             * @event searchVariants
             */
            'searchVariants',

            /**
             * Event will be fired when the user clicks on the edit action column of the
             * grid, to edit a single variant.
             * @event editVariant
             * @param [Ext.data.Model] Record - The selected record
             */
            'editVariant',

            /**
             * Event will be fired when the user clicks the "generate order numbers" button in the
             * toolbar to create new article order numbers.
             * @event generateOrderNumbers
             */
            'generateOrderNumbers',

            /**
             * Event will be fired when the user clicks the "apply standard prices" button in the toolbar
             * to apply the standard prices for all article variants.
             * @event applyPrices
             */
            'applyData',

            /**
             * Event will be fired over the row editor update button.
             * @event saveVariants
             */
            'editVariantPrice',

            /**
             * Event will be fired over the row editor update button.
             * @event saveVariants
             */
            'editVariantPseudoPrice',

            /**
             * Event will be fired over the save button if the user is on the configurator tab.
             * Fired from the detail.window component
             * @event createVariants
             */
            'createVariants',

            /**
             * Event will be fired over the save button if the user is on the settings tab.
             * Fired from the detail.window component
             * @event saveSettings
             */
            'saveSettings'
        );
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function (dynamic) {
        var me = this, standardColumns, columns = [];

        dynamic = dynamic || false;

        standardColumns = [
            {
                header: me.snippets.columns.stock,
                dataIndex: 'inStock',
                sortable: false,
                flex: 1,
                renderer: me.stockColumnRenderer,
                editor: {
                    xtype: 'numberfield',
                    allowBlank: true,
                    allowDecimals: false
                }
            } ,{
                header: me.snippets.columns.price.header,
                dataIndex: 'price',
                sortable: false,
                flex: 1,
                renderer: me.priceColumnRenderer,
                editor: {
                    xtype: 'numberfield',
                    allowBlank: false,
                    decimalPrecision: 2
                }
            } ,{
                header: me.snippets.columns.pseudoPrice.header,
                dataIndex: 'pseudoPrice',
                sortable: false,
                flex: 1,
                renderer: me.pseudoPriceColumnRenderer,
                editor: {
                    xtype: 'numberfield',
                    allowBlank: true,
                    decimalPrecision: 2
                }
            } , {
                header: me.snippets.columns.standard,
                dataIndex: 'standard',
                sortable: false,
                flex: 1,
                editor: {
                    xtype: 'checkbox',
                    inputValue: true,
                    uncheckedValue: false
                }
            } , {
                header: me.snippets.columns.active,
                dataIndex: 'active',
                sortable: false,
                flex: 1,
                editor: {
                    xtype: 'checkbox',
                    inputValue: true,
                    uncheckedValue: false
                }
            } ,
            {
                /**
                 * Special column type which provides clickable icons in each row
                 */
                xtype:'actioncolumn',
                width:70,
                items:[
                    {
                        iconCls:'sprite-minus-circle-frame',
                        action:'deleteVariant',
                        tooltip:me.snippets.columns.remove,
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            var records = [ record ];
                            me.fireEvent('deleteVariant', records);
                        }
                    } , {
                        iconCls:'sprite-pencil',
                        action:'editVariant',
                        tooltip:me.snippets.columns.edit,
                        handler:function (view, rowIndex, colIndex, item, opts, record) {
                            me.fireEvent('editVariant', record);
                        }
                    }
                ]
            }
        ];

        columns.push({
            header: me.snippets.columns.number,
            dataIndex: 'details.number',
            sortable: true,
            flex:1,
            align: 'left',
            renderer: me.numberColumnRenderer,
            editor: {
                allowBlank: false
            }
        });

        if(dynamic) {
            columns = columns.concat(me.createDynamicColumns());
        }
        columns = columns.concat(standardColumns);
        return columns;
    },

    /**
     * Creates the grid columns for the dynamic configurator groups.
     * @return [array] An array of column objects.
     */
    createDynamicColumns: function() {
        var me = this, columns = [], column;

        me.configuratorGroupStore.each(function(group) {
            if (group.get('active')) {
                columns.push(
                    {
                        header: group.get('name'),
                        dataIndex: 'configuratorOptions.name',
                        sortable: false,
                        flex: 1,
                        configuratorGroup: group,
                        renderer: me.configuratorGroupColumnRenderer
                    }
                );
            }
        });

        return columns;
    },

    /**
     * Renderer function of the price column. If a scale price defined, the function returns the first price value
     * with an additional flag "from*" to display the user that this variant has scale prices.
     * @param value
     * @param metaData
     * @param record
     */
    priceColumnRenderer: function(value, metaData, record) {
        var me = this,
            prices = record.getPrice();

        if (prices.getCount() === 0) {
            return me.snippets.columns.price.undefined;
        } else {
            var firstPrice = prices.first();
            if (prices.getCount() === 1) {
                return Ext.util.Format.currency(firstPrice.get('price'));
            } else {
                return me.snippets.columns.price.from + ' ' + Ext.util.Format.currency(firstPrice.get('price'));
            }
        }
    },

    /**
     * Renderer function of the pseudoPrice column.
     * @param value
     * @param metaData
     * @param record
     */
    pseudoPriceColumnRenderer: function(value, metaData, record) {
        var me = this,
            prices = record.getPrice();

        if (prices.getCount() === 0 || prices.first().get('pseudoPrice') === 0) {
            return me.snippets.columns.pseudoPrice.undefined;
        }

        var firstPrice = prices.first();
        return Ext.util.Format.currency(firstPrice.get('pseudoPrice'));
    },

    /**
     * Renderer function of the number column.
     * @param value
     * @param metaData
     * @param record
     */
    numberColumnRenderer: function(value, metaData, record) {
        if (record) {
            return record.get('number');
        } else {
            return '';
        }
    },
    /**
     * Renderer function of the stock column.
     * @param value
     * @param metaData
     * @param record
     */
    stockColumnRenderer: function(value, metaData, record) {
        if (record) {
            return record.get('inStock');
        } else {
            return '';
        }
    },



    /**
     * Renderer function for each configurator group column
     */
    configuratorGroupColumnRenderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
        var me = this;
        var column = me.columns[colIndex];
        var options = record.getConfiguratorOptions();

        if (column && options) {
            var configuratorGroup = column.configuratorGroup;
            var currentOption = null;
            options.each(function(option) {
                if (option.get('groupId') === configuratorGroup.get('id')) {
                    currentOption = option;
                    return;
                }
            });
            if (currentOption !== null) {
                return currentOption.get('name');
            }
        }
        return '';
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel:function () {
        var me = this;

        return Ext.create('Ext.selection.CellModel');
    },


    /**
     * Creates the grid toolbar with the different buttons.
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        var me = this;

        //creates the price button to apply the standard prices of the main article on all variants.
        me.applyDataButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-money--arrow',
            text: me.snippets.toolbar.data,
            action: 'applyData',
            handler: function() {
                me.fireEvent('applyData');
            }
        });

        //creates the text field for the order number syntax.
        me.orderNumberField = Ext.create('Ext.form.field.Text', {
            emptyText: me.snippets.toolbar.orderNumber.empty,
            fieldLabel: me.snippets.toolbar.orderNumber.label,
            flex: 1,
            allowBlank: false,
            name: 'numberSyntax'
        });

        //creates the button to regenerate all order numbers for the article variants.
        me.orderNumberButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-key--arrow',
            text: me.snippets.toolbar.orderNumber.button,
            action: 'generateOrderNumbers',
            handler: function() {
                me.fireEvent('generateOrderNumbers', me.orderNumberField);
            }
        });

        //creates the search field to filter the listing.
        me.searchField = Ext.create('Ext.form.field.Text', {
            name:'searchfield',
            cls:'searchfield',
            width:170,
            emptyText:me.snippets.toolbar.search,
            enableKeyEvents:true,
            checkChangeBuffer:500,
            listeners: {
                change: function(field, value) {
                    me.fireEvent('searchVariants', value);
                }
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items:[
                me.applyDataButton,
                { xtype:'tbspacer', width: 6 },
                { xtype: 'tbseparator' },
                { xtype:'tbspacer', width: 6 },
                me.orderNumberField,
                { xtype:'tbspacer', width: 6 },
                me.orderNumberButton,
                '->',
                me.searchField,
                { xtype:'tbspacer', width:6 }
            ]
        });
    },

    /**
     * Creates the paging toolbar for the grid to allow store paging. The paging toolbar uses the same store as the Grid
     *
     * @return Ext.toolbar.Paging The paging toolbar for the customer grid
     */
    getPagingBar:function () {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock:'bottom',
            displayInfo:true
        });
    },

    onStoresLoaded: function(article, stores) {
        var me = this;

        me.customerGroupStore = stores['customerGroups'];
        me.configuratorGroupStore = stores['configuratorGroups'];
        me.reconfigure(me.getStore(), me.getColumns(true));
    }

});
//{/block}

