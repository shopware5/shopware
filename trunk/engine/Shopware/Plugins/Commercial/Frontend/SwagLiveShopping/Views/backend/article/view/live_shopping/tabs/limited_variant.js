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
 * @package    SwagLiveShopping
 * @subpackage ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{block name="backend/live_shopping/view/live_shopping/tabs/list"}
//{namespace name="backend/live_shopping/article/view/main"}
Ext.define('Shopware.apps.Article.view.live_shopping.tabs.LimitedVariant', {

    extend: 'Ext.grid.Panel',

    title: '{s name=limited_variants/title}Limit variants{/s}',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.live-shopping-limited-detail-listing',

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
    initComponent: function() {
        var me = this;
        me.tbar = me.createToolBar();
        me.columns = me.createColumns();
        me.callParent(arguments);
    },

    /**
     * Creates the columns for the grid panel.
     * @return Array
     */
    createColumns: function() {
        var me = this, columns = [];

        columns.push(me.createNumberColumn());
        columns.push(me.createNameColumn());
        columns.push(me.createActionColumn());

        return columns;
    },

    /**
     * Creates the number column for the listing
     * @return Ext.grid.column.Column
     */
    createNumberColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=limited_variants/product_number_column}Product number{/s}',
            dataIndex: 'number',
            flex: 1
        });
    },

    /**
     * Creates the number column for the listing
     * @return Ext.grid.column.Column
     */
    createNameColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=limited_variants/additional_text_column}Additional text{/s}',
            dataIndex: 'additionalText',
            flex: 1
        });
    },

    /**
     * Creates the action column for the listing.
     * @return Ext.grid.column.Action
     */
    createActionColumn: function() {
        var me = this, items;

        items = me.getActionColumnItems();

        return Ext.create('Ext.grid.column.Action', {
            items: items,
            width: items.length * 30
        });
    },


    /**
     * Creates the action column items for the listing.
     * @return Array
     */
    getActionColumnItems: function() {
        var me = this,
            items = [];

        items.push(me.createDeleteActionColumnItem());
        return items;
    },

    /**
     * Creates the delete action column item for the listing action column
     * @return Object
     */
    createDeleteActionColumnItem: function() {
        var me = this;

        return {
            iconCls:'sprite-minus-circle-frame',
            width: 30,
            tooltip: '{s name=limited_variants/delete_variant_column}Delete variant{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('deleteLimitedVariant', [ record ]);
            }
        };
    },

    /**
     * Creates the tool bar for the listing component.
     * @return Ext.toolbar.Toolbar
     */
    createToolBar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolBarItems(),
            dock: 'top'
        });
    },

    /**
     * Creates the elements for the listing toolbar.
     * @return Array
     */
    createToolBarItems: function() {
        var me = this;

        return [
            { xtype: 'tbspacer', width: 6 },
            me.createToolBarVariantComboBox()
        ];
    },

    /**
     * Creates the paging combo box for the listing combo box.
     * @return Shopware.form.field.PagingComboBox
     */
    createToolBarVariantComboBox: function() {
        var me = this;

        var store = Ext.create('Shopware.apps.Article.store.live_shopping.Variant');
        store.getProxy().extraParams.articleId = me.article.get('id');

        me.variantComboBox = Ext.create('Shopware.form.field.PagingComboBox', {
            store: store,
            triggerAction: 'all',
            queryMode: 'remote',
            margin: '0 0 9 0',
            pageSize: 10,
            width: 400,
            fieldLabel: '{s name=limited_variants/add_variant_field}Add variant{/s}',
            labelWidth: 180,
            displayField: 'number',
            valueField: 'id',
            tpl: me.createComboBoxTemplate(),
            // template for the content inside text field
            displayTpl: me.createComboBoxDisplayTemplate(),
            listeners: {
                beforeselect: function(comboBox, record) {
                    me.fireEvent('addLimitedVariant', record);
                    //we want to prevent the default actions from the combo box.
                    return false;
                }
            }
        });

        return me.variantComboBox;
    },

    /**
     * Creates the xTemplate for the "tpl" property of the
     * variant combo box.
     * @return Ext.XTemplate
     */
    createComboBoxTemplate: function() {
        return Ext.create('Ext.XTemplate',
            '{literal}<tpl for=".">',
                '<div class="x-boundlist-item">',
                    '<span style="font-weight: 700; color: #475C6B; text-shadow: 1px 1px 1px #FFFFFF;">',
                        '{number}',
                    '</span>',
                    '<p style="color: #999;">',
                        '{additionalText}',
                    '</p>',
                '</div>',
            '</tpl>{/literal}'
        );
    },

    /**
     * Creates the xTemplate for the "displayTpl" property of the
     * variant combo box.
     * @return Ext.XTemplate
     */
    createComboBoxDisplayTemplate: function() {
        return Ext.create('Ext.XTemplate',
            '{literal}<tpl for=".">',
                '<span style="font-weight: 700; color: #475C6B; text-shadow: 0 1px 0 #fff;">',
                    '{number}',
                '</span>',
                '<p style="color: #999;">',
                    '{additionalText}',
                '</p>',
            '</tpl>{/literal}'
        );
    }
});
//{/block}