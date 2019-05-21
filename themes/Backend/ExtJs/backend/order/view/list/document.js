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
 * @package    Order
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order list backend module
 *
 * The order document list
 */
//{block name="backend/order/view/list/document"}
Ext.define('Shopware.apps.Order.view.list.Document', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.order-document-list',

    /**
     * Set css class
     * @string
     */
    cls: Ext.baseCSSPrefix + 'document-grid',

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll: true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        columns: {
            name: '{s name=column/name}Name{/s}',
            date: '{s name=column/date}Date{/s}',
            amount: '{s name=column/amount}Amount{/s}',
            downloadDocument: '{s name=column/download}Download{/s}'
        }
    },

    plugins: [{
        ptype: 'grid-attributes',
        table: 's_order_documents_attributes'
    }],

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
        me.columns = me.getColumns();
        me.pagingbar = me.getPagingBar();
        me.callParent(arguments);
    },

    /**
     * Creates the grid columns.
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.columns.date,
                dataIndex: 'date',
                flex: 1,
                renderer: me.dateColumn
            }, {
                header: me.snippets.columns.name,
                dataIndex: 'name',
                flex: 2,
                renderer: me.nameColumn
            }, {
                header: me.snippets.columns.amount,
                dataIndex: 'amount',
                flex: 1,
                renderer: me.amountColumn
            },
            me.createActionColumn()
        ];
    },

    /**
     * Creates the action columns
     *
     * @returns { Ext.grid.column.Action }
     */
    createActionColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Action', {
            width: 60,
            items: [
                /*{if {acl_is_allowed resource=order privilege=deleteDocument}}*/
                me.createDeleteColumn(),
                /*{/if}*/
                me.createSendColumn()
            ]
        });
    },

    /**
     * Creates the delete action column item for the grid.
     *
     * @return Object
     */
    createDeleteColumn: function() {
        var me = this;

        return {
            iconCls: 'sprite-minus-circle-frame',
            handler: Ext.bind(me.deleteDocument, me)
        };
    },

    /**
     * Handler for the delete action column.
     *
     * @param { Ext.grid.Panel } view
     * @param { number } rowIndex
     * @param { number } colIndex
     * @param { object } item
     * @param { object } opts
     * @param { Ext.data.Model } record
     */
    deleteDocument: function(view, rowIndex, colIndex, item, opts, record) {
        var me = this;

        me.fireEvent('delete-document', view, record);
    },

    /**
     * Creates the send mail action column item for the grid.
     *
     * @return Object
     */
    createSendColumn: function() {
        var me = this;

        return {
            iconCls: 'sprite-mail-send',
            handler: Ext.bind(me.openMail, me)
        };
    },

    /**
     * Handler for the send mail action column.
     *
     * @param { Ext.grid.Panel } view
     * @param { number } rowIndex
     * @param { number } colIndex
     * @param { object } item
     * @param { object } opts
     * @param { Ext.data.Model } record
     */
    openMail: function(view, rowIndex, colIndex, item, opts, record) {
        var me = this;

        me.fireEvent('open-mail', record);
    },

    /**
     * Creates the paging bar for the document grid.
     */
    getPagingBar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom'
        })
    },

    /**
     * Column renderer function which formats the date column of the document grid.
     * @param value
     */
    dateColumn: function(value) {

        if (!Ext.isDate(value)) {
            return value;
        }
        return Ext.util.Format.date(value);
    },

    /**
     * Columns renderer for the name column
     * @param value
     */
    nameColumn: function(value, metaData, record, rowIndex, colIndex, store, view) {
        var helper = new Ext.dom.Helper,
            type = record.getDocType().first(),
            display = type.get('name');

        if (record.get('documentId')) {
            display += ' ' + Ext.String.leftPad(record.get('documentId'), 8, '0');
        }

        var spec = {
            tag: 'a',
            html: display,
            href: '{url action="openPdf"}?id=' + record.get('hash'),
            target: '_blank'
        };

        return helper.markup(spec);
    },

    /**
     * Column renderer function which formats the amount column with the Ext.util.Format.currency() function.
     * @param value
     */
    amountColumn: function(value) {
        if (!Ext.isNumeric(value)) {
            return value;
        }
        return Ext.util.Format.currency(value);
    }

});
//{/block}
