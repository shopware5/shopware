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
 * @package    Supplier
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/supplier/view/main}*/

/**
 * Shopware View - Supplier
 *
 * Backend - Management for Suppliers. Create | Modify | Delete and Logo Management.
 * Default supplier view. Extends a grid view.
 */
//{block name="backend/supplier/view/main/list"}
Ext.define('Shopware.apps.Supplier.view.main.List', {
    extend : 'Ext.grid.Panel',
    alias : 'widget.supplier-main-list',
    autoScroll : true,
    stateful : true,
    stateId : 'shopware-supplier-list',

    /**
     * Initialize the Shopware.apps.Supplier.view.main.List and defines the necessary
     * default configuration
     *
     * @return void
     */
    initComponent : function () {
        var me = this;

        me.store = me.supplierStore;
        me.store.load();
        me.selModel = me.getSelModel();

        // Define the columns and renders
        me.columns = me.getGridColumns();

        me.bbar = me.getPagingbar();

        me.callParent(arguments);
    },

    /**
     * Return the selection model for this grid.
     *
     * @return Ext.selection.CheckboxModel
     */
    getSelModel : function() {
        return Ext.create('Ext.selection.CheckboxModel');
    },

    /**
     * Return an array of objects (grid columns)
     *
     * @return array of grid columns
     */
    getGridColumns : function() {
        var me = this;
        return [
            {
                header : '{s name=grid_name}Name{/s}',
                dataIndex : 'name',
                renderer : me.nameColumn,
                width: 125
            },
            {
                header : '{s name=grid_articlecounter}Article{/s}',
                dataIndex : 'articleCounter',
                width : 50,
                renderer : me.articleCountColumn
            },
            {
                header : '{s name=grid_link}URL{/s}',
                dataIndex : 'link',
                width: 150,
                renderer : me.urlColumn
            },
            {
                header : '{s name=grid_description}Description{/s}',
                dataIndex : 'description',
                flex : 1,
                renderer : me.descriptionColumn
            },
            {
                xtype : 'actioncolumn',
                width : 60,
                items : me.getActionColumn()
            }
        ];
    },

    /**
     * Returns the an array of icons for the action column
     *
     * @return Array of buttons
     */
    getActionColumn : function() {
        return [
            /*{if {acl_is_allowed privilege=delete}}*/
            {
                iconCls : 'sprite-minus-circle-frame',
                action : 'delete',
                tooltip : '{s name=grid_delete_tooltip}Delete this supplier{/s}'
            },
            /*{/if}*/
            /*{if {acl_is_allowed privilege=update}}*/
            {
                iconCls : 'sprite-pencil',
                action : 'edit',
                tooltip : '{s name=grid_edit_tooltip}Edit this supplier{/s}'
            }
            /*{/if}*/
        ];
    },

    getPagingbar: function () {
        var me = this,
            manufacturerSnippet = '{s name=paging_term}manufacturer{/s}';

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
                    { value: '25', name: '25 ' + manufacturerSnippet },
                    { value: '50', name: '50 ' + manufacturerSnippet },
                    { value: '75', name: '75 ' + manufacturerSnippet },
                    { value: '100', name: '100 ' + manufacturerSnippet },
                    { value: '125', name: '125 ' + manufacturerSnippet },
                    { value: '150', name: '150 ' + manufacturerSnippet }
                ]
            }),
            displayField: 'name',
            valueField: 'value',
            value: '25'
        });

        var pagingBar = Ext.create('Ext.toolbar.Paging', {
            dock: 'bottom',
            displayInfo: true,
            store: me.store
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

        me.store.loadPage(1);
    },

    /**
     * Formats the email column
     *
     * @param [string] value - An URI to the supplier homepage
     * @return String
     */
    urlColumn : function (value) {
        return Ext.String.format('{literal}<a h'+'ref="{0}" target="_blank">{1}</a>{/literal}', value, value);
    },

    /**
     * Formats the name column
     *
     * @param [string] value - Name of the supplier
     * @return String
     */
    nameColumn : function (value) {
        return Ext.String.format('{literal}<strong style="font-weight: 700">{0}</strong>{/literal}', value);
    },

    /**
     * Formats the articles count column
     *
     * @param [integer] value - Count of how many articles are associated with this supplier
     * @return integer
     */
    articleCountColumn : function (value) {
        return  value;
    },

    /**
     * Formats the description column
     *
     * @param [string] value - HTML Text containing the description of the supplier
     * @return string
     */
    descriptionColumn : function (value) {
        value = Ext.util.Format.ellipsis(value, 40);
        value = Ext.util.Format.htmlEncode(value);
        return value;
    }
});
//{/block}
