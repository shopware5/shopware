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

//{namespace name=backend/article_list/main}
//{block name="backend/article_list/view/main/grid"}
Ext.define('Shopware.apps.ArticleList.view.main.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.multi-edit-main-grid',

    /**
     * Make the grid statefull
     */
    stateful: true,
    /**
     * StateId (used in the cookiename later)
     */
    stateId: 'multiedit-grid',

    /**
     * Variant active column
     */
    detailActiveColumn: null,

    snippets: {
        'Article_id': '{s name=columns/product/Article_id}Article_id{/s}',
        'Article_mainDetailId': '{s name=columns/product/Article_mainDetailId}Article_mainDetailId{/s}',
        'Article_supplierId': '{s name=columns/product/Article_supplierId}Article_supplierId{/s}',
        'Article_taxId': '{s name=columns/product/Article_taxId}Article_taxId{/s}',
        'Article_priceGroupId': '{s name=columns/product/Article_priceGroupId}Article_priceGroupId{/s}',
        'Article_filterGroupId': '{s name=columns/product/Article_filterGroupId}Article_filterGroupId{/s}',
        'Article_configuratorSetId': '{s name=columns/product/Article_configuratorSetId}Article_configuratorSetId{/s}',
        'Article_name': '{s name=columns/product/Article_name}Article_name{/s}',
        'Article_description': '{s name=columns/product/Article_description}Article_description{/s}',
        'Article_descriptionLong': '{s name=columns/product/Article_descriptionLong}Article_descriptionLong{/s}',
        'Article_added': '{s name=columns/product/Article_added}Article_added{/s}',
        'Article_active': '{s name=columns/product/Article_active}Article_active{/s}',
        'Article_pseudoSales': '{s name=columns/product/Article_pseudoSales}Article_pseudoSales{/s}',
        'Article_highlight': '{s name=columns/product/Article_highlight}Article_highlight{/s}',
        'Article_keywords': '{s name=columns/product/Article_keywords}Article_keywords{/s}',
        'Article_changed': '{s name=columns/product/Article_changed}Article_changed{/s}',
        'Article_priceGroupActive': '{s name=columns/product/Article_priceGroupActive}Article_priceGroupActive{/s}',
        'Article_lastStock': '{s name=columns/product/Article_lastStock}Article_lastStock{/s}',
        'Article_crossBundleLook': '{s name=columns/product/Article_crossBundleLook}Article_crossBundleLook{/s}',
        'Article_notification': '{s name=columns/product/Article_notification}Article_notification{/s}',
        'Article_template': '{s name=columns/product/Article_template}Article_template{/s}',
        'Article_mode': '{s name=columns/product/Article_mode}Article_mode{/s}',
        'Article_availableFrom': '{s name=columns/product/Article_availableFrom}Article_availableFrom{/s}',
        'Article_availableTo': '{s name=columns/product/Article_availableTo}Article_availableTo{/s}',
        'Detail_id': '{s name=columns/product/Detail_id}Detail_id{/s}',
        'Detail_articleId': '{s name=columns/product/Detail_articleId}Detail_articleId{/s}',
        'Detail_unitId': '{s name=columns/product/Detail_unitId}Detail_unitId{/s}',
        'Detail_number': '{s name=columns/product/Detail_number}Detail_number{/s}',
        'Detail_supplierNumber': '{s name=columns/product/Detail_supplierNumber}Detail_supplierNumber{/s}',
        'Detail_kind': '{s name=columns/product/Detail_kind}Detail_kind{/s}',
        'Detail_additionalText': '{s name=columns/product/Detail_additionalText}Detail_additionalText{/s}',
        'Detail_active': '{s name=columns/product/Detail_active}Detail_active{/s}',
        'Detail_inStock': '{s name=columns/product/Detail_inStock}Detail_inStock{/s}',
        'Detail_stockMin': '{s name=columns/product/Detail_stockMin}Detail_stockMin{/s}',
        'Detail_weight': '{s name=columns/product/Detail_weight}Detail_weight{/s}',
        'Detail_width': '{s name=columns/product/Detail_width}Detail_width{/s}',
        'Detail_len': '{s name=columns/product/Detail_len}Detail_len{/s}',
        'Detail_height': '{s name=columns/product/Detail_height}Detail_height{/s}',
        'Detail_ean': '{s name=columns/product/Detail_ean}Detail_ean{/s}',
        'Detail_position': '{s name=columns/product/Detail_position}Detail_position{/s}',
        'Detail_minPurchase': '{s name=columns/product/Detail_minPurchase}Detail_minPurchase{/s}',
        'Detail_purchaseSteps': '{s name=columns/product/Detail_purchaseSteps}Detail_purchaseSteps{/s}',
        'Detail_maxPurchase': '{s name=columns/product/Detail_maxPurchase}Detail_maxPurchase{/s}',
        'Detail_purchaseUnit': '{s name=columns/product/Detail_purchaseUnit}Detail_purchaseUnit{/s}',
        'Detail_referenceUnit': '{s name=columns/product/Detail_referenceUnit}Detail_referenceUnit{/s}',
        'Detail_packUnit': '{s name=columns/product/Detail_packUnit}Detail_packUnit{/s}',
        'Detail_shippingFree': '{s name=columns/product/Detail_shippingFree}Detail_shippingFree{/s}',
        'Detail_releaseDate': '{s name=columns/product/Detail_releaseDate}Detail_releaseDate{/s}',
        'Detail_shippingTime': '{s name=columns/product/Detail_shippingTime}Detail_shippingTime{/s}',
        'Attribute_id': '{s name=columns/product/Attribute_id}Attribute_id{/s}',
        'Attribute_articleId': '{s name=columns/product/Attribute_articleId}Attribute_articleId{/s}',
        'Attribute_articleDetailId': '{s name=columns/product/Attribute_articleDetailId}Attribute_articleDetailId{/s}',
        'Attribute_attr1': '{s name=columns/product/Attribute_attr1}Attribute_attr1{/s}',
        'Attribute_attr2': '{s name=columns/product/Attribute_attr2}Attribute_attr2{/s}',
        'Attribute_attr3': '{s name=columns/product/Attribute_attr3}Attribute_attr3{/s}',
        'Attribute_attr4': '{s name=columns/product/Attribute_attr4}Attribute_attr4{/s}',
        'Attribute_attr5': '{s name=columns/product/Attribute_attr5}Attribute_attr5{/s}',
        'Attribute_attr6': '{s name=columns/product/Attribute_attr6}Attribute_attr6{/s}',
        'Attribute_attr7': '{s name=columns/product/Attribute_attr7}Attribute_attr7{/s}',
        'Attribute_attr8': '{s name=columns/product/Attribute_attr8}Attribute_attr8{/s}',
        'Attribute_attr9': '{s name=columns/product/Attribute_attr9}Attribute_attr9{/s}',
        'Attribute_attr10': '{s name=columns/product/Attribute_attr10}Attribute_attr10{/s}',
        'Attribute_attr11': '{s name=columns/product/Attribute_attr11}Attribute_attr11{/s}',
        'Attribute_attr12': '{s name=columns/product/Attribute_attr12}Attribute_attr12{/s}',
        'Attribute_attr13': '{s name=columns/product/Attribute_attr13}Attribute_attr13{/s}',
        'Attribute_attr14': '{s name=columns/product/Attribute_attr14}Attribute_attr14{/s}',
        'Attribute_attr15': '{s name=columns/product/Attribute_attr15}Attribute_attr15{/s}',
        'Attribute_attr16': '{s name=columns/product/Attribute_attr16}Attribute_attr16{/s}',
        'Attribute_attr17': '{s name=columns/product/Attribute_attr17}Attribute_attr17{/s}',
        'Attribute_attr18': '{s name=columns/product/Attribute_attr18}Attribute_attr18{/s}',
        'Attribute_attr19': '{s name=columns/product/Attribute_attr19}Attribute_attr19{/s}',
        'Attribute_attr20': '{s name=columns/product/Attribute_attr20}Attribute_attr20{/s}',
        'Price_price': '{s name=columns/product/Price_price}Price_price{/s}',
        'Price_netPrice': '{s name=columns/product/Price_netPrice}Price_netPrice{/s}',
        'Supplier_name': '{s name=columns/product/Supplier_name}Supplier{/s}',
        'Tax_name': '{s name=columns/product/Tax_name}Tax{/s}'
    },

    /**
     * Setup the component
     */
    initComponent: function () {
        var me = this;

        this.setupStateManager();

        me.columns = me.getColumns();

        me.tbar = me.getToolbar();
        me.bbar = me.getPagingbar();
        me.selModel = me.getGridSelModel();

        me.addEvents(
            /**
             * Fired when the user edited a product in the grid
             */
            'saveProduct',

            /**
             * Delete a single article
             */
            'deleteProduct',

            /**
             * Delete multiple articles
             */
            'deleteMultipleProducts',

            /**
             * Trigger the split view
             */
            'triggerSplitView',

            /**
             * Triggered when the product selection changes
             */
            'productchange',

            /**
             * A search was triggered
             */
            'search'
        );

        me.rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            autoCancel: true,
            listeners: {
                scope: me,
                edit: function (editor, context) {
                    me.fireEvent('saveProduct', editor, context)
                }
            }
        });
        me.plugins = me.rowEditing;

        me.listeners = {
            'afterrender': me.onAfterRender
        };

        me.callParent(arguments);
    },

    onAfterRender: function() {
        var me = this;
        Ext.each(me.columns, function(col) {
            if (col.dataIndex === 'Detail_active') {
                me.detailActiveColumn = col;
                window.setTimeout(function() { col.setVisible(false); }, 0);
            }
        });
    },

    setupStateManager: function () {
        var me = this;
        me.stateManager = new Ext.state.LocalStorageProvider({ });

        Ext.state.Manager.setProvider(me.stateManager);
    },

    /**
     * Creates rowEditor Plugin
     *
     * @return [Ext.grid.plugin.RowEditing]
     */
    getRowEditorPlugin: function () {
        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            errorSummary: false,
            pluginId: 'rowEditing'
        });
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel: function () {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                // Unlocks the delete button if the user has checked at least one checkbox
                selectionchange: function (sm, selections) {
                    me.deleteButton.setDisabled(selections.length === 0);
                    me.splitViewModeBtn.setDisabled(selections.length === 0);
                    me.fireEvent('productchange', selections);
                }
            }
        });
    },

    getActionColumn: function () {
        var me = this;


        return {
            xtype: 'actioncolumn',
            width: 60,
            items: [
                /*{if {acl_is_allowed resource=article privilege=save}}*/
                {
                    action: 'edit',
                    cls: 'editBtn',
                    iconCls: 'sprite-pencil',
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        Shopware.app.Application.addSubApplication({
                            name: 'Shopware.apps.Article',
                            action: 'detail',
                            params: {
                                articleId: record.get('Article_id')
                            }
                        });
                    }
                },
                /*{/if}*/
                /*{if {acl_is_allowed resource=article privilege=delete}}*/
                {
                    iconCls: 'sprite-minus-circle-frame',
                    action: 'delete',
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        me.fireEvent('deleteProduct', record);
                    }
                }
                /*{/if}*/
            ]
        };
    },

    /**
     * Helper method which creates the columns for the
     * grid panel in this widget.
     *
     * @return [array] generated columns
     */
    getColumns: function () {
        var me = this,
                colLength,
                i,
                column,
                stateColumn,
                columnDefinition,
                width,
                xtype,
                renderer,
                columns = [ ];

        colLength = me.columnConfig.length;
        for (i = 0; i < colLength; i++) {
            column = me.columnConfig[i];

            if (!column.allowInGrid) {
                continue;
            }

            columnDefinition = {
                dataIndex: column.alias,
                header: me.getTranslationForColumnHead(column.alias),
                /*{if {acl_is_allowed resource=article privilege=save}}*/
                editor: me.getEditorForColumn(column),
                /*{/if}*/
                hidden: !column.show
            };

            if (xtype = me.getXtypeForColumn(column)) {
                columnDefinition.xtype = xtype;
            }

            if (renderer = me.getRendererForColumn(column)) {
                columnDefinition.renderer = renderer;
            }


            if (width = me.getWidthForColumn(column)) {
                columnDefinition.width = width;
            } else {
                columnDefinition.flex = 1;
            }

            if (column.alias == 'Detail_active') {
                columnDefinition.hidden = true;
            }

            columns.push(columnDefinition);
        }

        columns.push({
            header: '{s name=list/column_info}Info{/s}',
            width: 90,
            renderer: me.infoColumnRenderer
        });

        columns.push(me.getActionColumn());

        return columns;
    },

    /**
     * Returns a proper xtype fo a column
     *
     * @param column
     * @returns *
     */
    getXtypeForColumn: function (column) {
        var me = this;

        if (column.alias === 'Price_price') {
            return 'numbercolumn';
        }

        return undefined;
    },

    /**
     * Column renderer for columns shown in <b>tags</b>
     *
     * @param value
     * @returns string
     */
    boldColumnRenderer: function (value, metaData, record) {
        var result = value;

        var additional = record.get('Detail_additionalText');
        if (!additional) {
            additional = record.get('Detail_additionalText_dynamic');
        }

        if (additional) {
            result = value + ' - ' + additional;
        }
        return '<b>' + this.defaultColumnRenderer(result) + '</b>';
    },

    /**
     * Column renderer for most of the columns
     *
     * @param value
     * @returns string
     */
    defaultColumnRenderer: function (value) {
        return value;
    },

    /**
     * Column renderer for boolean columns in order to
     * @param value
     */
    booleanColumnRenderer: function (value) {
        var checked = 'sprite-ui-check-box-uncheck';
        if (value == true) {
            checked = 'sprite-ui-check-box';
        }
        return '<span style="display:block; margin: 0 auto; height:25px; width:25px;" class="' + checked + '"></span>';
    },

    /**
     *
     * Show info like: Is this a configurator article / does it have images /
     * does it have a category
     *
     * @param value
     * @param metaData
     * @param record
     * @returns string
     */
    infoColumnRenderer: function (value, metaData, record) {
        var me = this,
                result = '',
                title;

        var style = 'style="width: 25px; height: 25px; display: inline-block; margin-right: 3px;"';

        if (!record.get('imageSrc')) {
            title = '{s name=list/tooltip_noimage}Article has no image{/s}';
            result = result + '<div  title="' + title + '" class="sprite-image--exclamation" ' + style + '>&nbsp;</div>';
        }

        if (record.get('hasConfigurator')) {
            title = '{s name=list/tooltip_hasconfigurator}Article has configurator{/s}';
            result = result + '<div  title="' + title + '" class="sprite-images-stack" ' + style + '>&nbsp;</div>';
        }

        if (!record.get('hasCategories')) {
            title = '{s name=list/tooltip_categories}Article is not assigned to any category{/s}';
            result = result + '<div title="' + title + '" class="sprite-blue-folder--exclamation" ' + style + '>&nbsp;</div>';
        }

        return result;
    },

    /**
     * Will return a renderer depending on the passed column.
     * todo: Article_name should not be hardcoded here
     *
     * @param column
     * @returns string|function
     */
    getRendererForColumn: function (column) {
        var me = this;

        if (column.alias === 'Article_name') {
            return me.boldColumnRenderer;
        }

        if (column.type === 'boolean') {
            return me.booleanColumnRenderer;
        }

        if (column.alias === 'Detail_inStock') {
            return me.colorColumnRenderer;
        }

        if (column.alias === 'Price_price') {
            return undefined;
        }

        return me.defaultColumnRenderer;
    },

    /**
     * Will return a green string for values > 0 and red otherwise
     *
     * @param value
     * @returns string
     */
    colorColumnRenderer: function (value) {
        value = value || 0;
        if (value > 0) {
            return '<span style="color:green;">' + value + '</span>';
        } else {
            return '<span style="color:red;">' + value + '</span>';
        }
    },

    /**
     * Helper method which returns a "human readable" translation for a columnAlias
     * Will return the columnAlias, if no translation was created
     *
     * @param columnHeader
     * @returns string
     */
    getTranslationForColumnHead: function (columnHeader) {
        var me = this;

        if (me.snippets.hasOwnProperty(columnHeader)) {
            return me.snippets[columnHeader];
        }
        return columnHeader;
    },

    /**
     * Return width for a given column
     *
     * For known fields like boolean, integer, date and datetime, we can try and
     * educated guess, for anything else undefined is returned.
     *
     * @param column
     */
    getWidthForColumn: function (column) {
        var me = this;

        if (column.alias.slice(-2).toLowerCase() === 'id') {
            return 60;
        }

        switch (column.alias) {
            case 'Price_price':
                return 90;
            case 'Detail_number':
                return 110;
            case 'Supplier_name':
                return 110;
            case 'Article_active':
            case 'Detail_active':
                return 40;
            case 'Tax_name':
                return 75;
            case 'Detail_inStock':
                return 80;
        }

        switch (column.type) {
            case 'integer':
            case 'decimal':
            case 'float':
                return 60;
            case 'string':
            case 'text':
                return undefined;
            case 'boolean':
                return 60;
            case 'date':
                return 100;
            case 'datetime':
                return 140;
            default:
                return undefined;
        }
    },

    /**
     * Helper method which returns a rowEditing.editor for a given column.
     *
     * @param column
     * @returns Object|boolean
     */
    getEditorForColumn: function (column) {
        var me = this;

        // Do create editor for columns, which have been configured to be non-editable
        if (!column.editable) {
            return false;
        }

        switch (column.alias) {
            case 'Price_price':
                return {
                    width: 85,
                    xtype: 'numberfield',
                    allowBlank: false,
                    hideTrigger: true,
                    keyNavEnabled: false,
                    mouseWheelEnabled: false
                };
            default:
                switch (column.type) {
                    case 'integer':
                    case 'decimal':
                    case 'float':
                        var precision = 0;
                        if (column.precision) {
                            precision = column.precision
                        } else if (column.type === 'float') {
                            precision = 3;
                        } else if (column.type === 'decimal') {
                            precision = 3;
                        }
                        return { xtype: 'numberfield', decimalPrecision: precision };
                        break;

                    case 'string':
                    case 'text':
                        return 'textfield';
                        break;

                    case 'boolean':
                        return {
                            xtype: 'checkbox',
                            inputValue: 1,
                            uncheckedValue: 0
                        };
                        break;

                    case 'date':
                        return new Ext.form.DateField({
                            disabled: false,
                            format: 'Y-m-d'
                        });
                        break;

                    case 'datetime':
                        return new Ext.form.DateField({
                            disabled: false,
                            format: 'Y-m-d H:i:s'
                        });

                        return new Shopware.apps.Base.view.element.DateTime({
                            timeCfg: { format: 'H:i:s' },
                            dateCfg: { format: 'Y-m-d' }
                        });
                        break;

                    default:
                        break;
                }
                break;
        }
    },


    /**
     * Creates the grid toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function () {
        var me = this, buttons = [];

        me.splitViewModeBtn = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-ui-split-panel',
            text: '{s name=enableSplitView}Activate split view{/s}',
            disabled: true,
            enableToggle: true,
            handler: function () {
                var selectionModel = me.getSelectionModel(),
                        record = selectionModel.getSelection()[0];

                me.fireEvent('triggerSplitView', this, record);
            }
        });

        buttons.push(me.splitViewModeBtn);

        /*{if {acl_is_allowed resource=article privilege=save}}*/
        buttons.push(
                Ext.create('Ext.button.Button', {
                    text: '{s name=addProduct}Add{/s}',
                    iconCls: 'sprite-plus-circle-frame',
                    handler: function () {
                        Shopware.app.Application.addSubApplication({
                            name: 'Shopware.apps.Article',
                            action: 'detail'
                        });
                    }
                })
        );
        /*{/if}*/

        // Creates the delete button to remove all selected esds in one request.
        me.deleteButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-minus-circle-frame',
            text: '{s name=deleteProduct}Delete{/s}',
            disabled: true,
            handler: function () {
                var selectionModel = me.getSelectionModel(),
                        records = selectionModel.getSelection();

                if (records.length > 0) {
                    me.fireEvent('deleteMultipleProducts', records);
                }
            }
        });

        /*{if {acl_is_allowed resource=article privilege=delete}}*/
        buttons.push(me.deleteButton);
        /*{/if}*/

        buttons.push('->');

        buttons.push({
            xtype: 'textfield',
            name: 'searchfield',
            action: 'search',
            width: 170,
            cls: 'searchfield',
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            emptyText: '{s name=list/emptytext_search}Search ...{/s}',
            listeners: {
                'change': function (field, value) {
                    var store = me.store,
                            searchString = Ext.String.trim(value);

                    me.fireEvent('search', searchString);
                }
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: buttons
        });
    },

    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function () {
        var me = this,
                productSnippet = '{s name=pagingCombo/products}products{/s}';

        var pageSize = Ext.create('Ext.form.field.ComboBox', {
            labelWidth: 120,
            cls: Ext.baseCSSPrefix + 'page-size',
            queryMode: 'local',
            width: 180,
            editable: false,
            listeners: {
                scope: me,
                select: me.onPageSizeChange
            },
            store: Ext.create('Ext.data.Store', {
                fields: [ 'value', 'name' ],
                data: [
                    { value: '25', name: '25 ' + productSnippet },
                    { value: '50', name: '50 ' + productSnippet },
                    { value: '75', name: '75 ' + productSnippet },
                    { value: '100', name: '100 ' + productSnippet },
                    { value: '125', name: '125 ' + productSnippet },
                    { value: '150', name: '150 ' + productSnippet }
                ]
            }),
            displayField: 'name',
            valueField: 'value'
        });

        var pagingBar = Ext.create('Ext.toolbar.Paging', {
            dock: 'bottom',
            displayInfo: true
        });

        pagingBar.insert(pagingBar.items.length, [
            { xtype: 'tbspacer', width: 6 },
            pageSize
        ]);

        return pagingBar;
    },

    /**
     * Event listener method which fires when the user selects
     * a entry in the "number of orders"-combo box.
     *
     * @event select
     * @param { object } combo - Ext.form.field.ComboBox
     * @param { array } records - Array of selected entries
     * @return void
     */
    onPageSizeChange: function (combo, records) {
        var record = records[0],
                me = this;

        me.store.pageSize = record.get('value');
        if (!me.store.getProxy().extraParams.ast) {
            return;
        }

        me.store.loadPage(1);
    }
});
//{/block}
