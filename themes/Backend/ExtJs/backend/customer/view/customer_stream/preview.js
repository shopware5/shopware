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
 * @package    Customer
 * @subpackage List
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}

/**
 * Shopware UI - Customer list backend module
 * The customer list view displays the data of the list store.
 * One row displays the head data of a customer.
 */
// {block name="backend/customer/view/customer_stream/preview"}
Ext.define('Shopware.apps.Customer.view.customer_stream.Preview', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
    */
    alias: 'widget.customer-stream-preview-list',

    /**
     * Set css class
     * @string
     */
    cls: Ext.baseCSSPrefix + 'customer-grid',

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll: true,

    /**
     * Defaults for the grid panel.
     * @object
     */
    defaults: { flex: 1 },

    displayDeleteIcon: false,

    /**
     * Initialize the Shopware.apps.Customer.view.main.List and defines the necessary
     * default configuration
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.columns = me.getColumns();

        me.dockedItems = [ me.getPagingBar() ];

        me.callParent(arguments);

        var header = me.headerCt;

        header.on('menucreate', function (ct, menu) {
            menu.remove(menu.items.items[2]);
            menu.remove(menu.items.items[1]);
            menu.remove(menu.items.items[0]);

            menu.add([
                me.createSortingItem('{s name="number"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\NumberSorting'),
                me.createSortingItem('{s name="first_login"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\CustomerSinceSorting'),
                me.createSortingItem('{s name="customer_group"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\CustomerGroupSorting'),
                me.createSortingItem('{s name="lastname"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\LastNameSorting'),
                me.createSortingItem('{s name="city"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\CitySorting'),
                me.createSortingItem('{s name="zip_code"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\ZipCodeSorting'),
                me.createSortingItem('{s name="street"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\StreetNameSorting'),
                me.createSortingItem('{s name="invoice_amount_sum"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\TotalAmountSorting'),
                me.createSortingItem('{s name="average_amount"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\AverageAmountSorting'),
                me.createSortingItem('{s name="average_product_amount"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\AverageProductAmountSorting'),
                me.createSortingItem('{s name="count_orders"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\TotalOrderSorting'),
                me.createSortingItem('{s name="last_order_time"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\LastOrderSorting'),
                me.createSortingItem('{s name="age"}{/s}', 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\AgeSorting')
            ]);
        });

        header.on('headerclick', Ext.bind(me.handleColumnSorting, me));
    },

    handleColumnSorting: function(ct, column, e, t, eOpts) {
        var me = this,
            colSortClsPrefix = Ext.baseCSSPrefix + 'column-header-sort-',
            ascCls = colSortClsPrefix + 'ASC',
            descCls = colSortClsPrefix + 'DESC',
            ownerHeaderCt = column.getOwnerHeaderCt(),
            oldSortState = column.mySortState,
            state = 'ASC',
            headers = ownerHeaderCt.getGridColumns();

        if (oldSortState === 'ASC') {
            state = 'DESC';
        }

        column.addCls(colSortClsPrefix + state);

        switch (state) {
            case 'DESC':
                column.removeCls([ascCls]);
                break;
            case 'ASC':
                column.removeCls([descCls]);
                break;
        }

        if (ownerHeaderCt && !column.triStateSort) {
            for (var i = 0; i < headers.length; i++) {
                if (headers[i] !== column) {
                    headers[i].removeCls([ascCls, descCls]);
                }
            }
        }
        column.mySortState = state;

        me.sortingHandler(column.sortingClass, state);
    },

    createSortingItem: function(text, sortingClass) {
        var me = this;

        return {
            text: text,
            sortingClass: sortingClass,
            menu: {
                items: [
                    { text: '{s name="ascending"}{/s}',
                        handler: function () {
                            me.sortingHandler(sortingClass, 'ASC');
                        } },
                    { text: '{s name="descending"}{/s}',
                        handler: function () {
                            me.sortingHandler(sortingClass, 'DESC');
                        } }
                ]
            }
        };
    },

    sortingHandler: function(sortingClass, direction) {
        var me = this;

        var sorting = { };
        sorting[sortingClass] = {
            direction: direction
        };
        me.getStore().getProxy().extraParams.sorting = Ext.JSON.encode(sorting);
        me.getStore().load();
    },

    renderCurrency: function(value) {
        value = value * 1;
        return Ext.util.Format.currency(value, this.subApp.currencySign, 2, (this.subApp.currencyAtEnd == 1));
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this;

        return [{
            header: '{s name="information"}{/s}',
            dataIndex: 'customernumber',
            sortable: false,
            sortingClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\NumberSorting',
            flex: 2,
            renderer: function (value, meta, record) {
                return '<b>' + record.get('customernumber') + '</b> - ' + record.get('customer_group_name') +
                    '<br><i>{s name="first_login"}{/s}: ' + Ext.util.Format.date(record.get('firstlogin')) + '</i></span>';
            }
        }, {
            header: '{s name="customer"}{/s}',
            dataIndex: 'lastname',
            sortable: false,
            sortingClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\LastNameSorting',
            flex: 2,
            renderer: function (v, meta, record) {
                var names = [
                    record.get('title'),
                    record.get('firstname'),
                    record.get('lastname')
                ];

                var name = '<b>' + names.join(' ') + '</b>';
                var age = '';
                if (record.get('age')) {
                    age = ' (' + record.get('age') + ')';
                }
                var company = '';
                if (record.get('company')) {
                    company = '<br>' + record.get('company') + '';
                }

                var mail = Ext.String.format('<a href="mailto:[0]" data-qtip="[0]">[0]</a>', record.get('email'));

                return name + age + company + '<br>' + mail;
            }
        }, {
            header: '{s name="address"}{/s}',
            dataIndex: 'city',
            sortable: false,
            sortingClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\CitySorting',
            flex: 3,
            renderer: function(v, meta, record) {
                var lines = [
                    record.get('street'),
                    [record.get('zipcode'), record.get('city'), record.get('country_name')].join(' '),
                    record.get('additional_address_line1'),
                    record.get('additional_address_line2')
                ];
                return lines.join('<br>');
            }
        }, {
            header: '{s name="amount_header"}{/s}',
            dataIndex: 'invoice_amount_sum',
            sortable: false,
            sortingClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\TotalAmountSorting',
            flex: 2,
            renderer: function(v, meta, record) {
                return '' +
                    '{s name="invoice_amount_sum"}{/s}: <b>' + me.renderCurrency(record.get('invoice_amount_sum')) + '</b>' +
                    '<br>{s name="average_amount"}{/s}: <b>' + me.renderCurrency(record.get('invoice_amount_avg')) + '</b>' +
                    '<br>{s name="average_product_amount"}{/s}: <b>' + me.renderCurrency(record.get('product_avg')) + '</b>';
            }
        }, {
            header: '{s name="order_header"}{/s}',
            dataIndex: 'count_orders',
            flex: 3,
            sortable: false,
            sortingClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Sorting\\TotalOrderSorting',
            renderer: function(v, meta, record) {
                return '<b>{s name="count_orders"}{/s}: ' + record.get('count_orders') * 1 + '</b>' +
                    '<br>{s name="last_order_time"}{/s}: ' + Ext.util.Format.date(record.get('last_order_time'));
            }
        }, {
            header: '{s name="assigned_streams"}{/s}',
            dataIndex: 'streams',
            flex: 2,
            sortable: false,
            renderer: function(streams) {
                if (streams.length <= 0) {
                    return;
                }

                var names = [];
                Ext.each(streams, function(item) {
                    names.push('<a href="#" class="stream-inline" data-id="' + item.id + '">' + item.name + '</a>');
                });

                return names.join('<br>');
            }
        }
        , {
            xtype: 'actioncolumn',
            width: 60,
            items: [
                /* {if {acl_is_allowed privilege=detail}} */
                {
                    iconCls: 'sprite-pencil',
                    action: 'editCustomer',
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        me.fireEvent('edit', record);
                    }
                },
                /* {/if} */
                /* {if {acl_is_allowed privilege=delete}} */
                {
                    action: 'delete',
                    iconCls: 'sprite-cross',
                    getClass: function() {
                        if (!me.displayDeleteIcon) {
                            return 'x-hidden';
                        }
                        return '';
                    },
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        me.fireEvent('delete', record);
                    }
                }
                /* {/if} */
            ]
        }

        ];
    },

    /**
     * Creates the paging toolbar for the customer grid to allow
     * and store paging. The paging toolbar uses the same store as the Grid
     *
     * @return Ext.toolbar.Paging The paging toolbar for the customer grid
     */
    getPagingBar: function () {
        var me = this;

        var comboStore = Ext.create('Ext.data.Store', {
            fields: [ 'value', 'display' ],
            data: [
                { value: 10, display: '10 {s name="items"}{/s}' },
                { value: 20, display: '20 {s name="items"}{/s}' },
                { value: 50, display: '50 {s name="items"}{/s}' },
                { value: 100, display: '100 {s name="items"}{/s}' },
                { value: 200, display: '200 {s name="items"}{/s}' }
            ]
        });

        var combo = Ext.create('Ext.form.field.ComboBox', {
            store: comboStore,
            valueField: 'value',
            displayField: 'display',
            fieldLabel: '{s name="items_per_page"}{/s}',
            labelStyle: 'margin-top: 2px',
            width: 220,
            labelWidth: 110,
            listeners: {
                scope: me,
                change: Ext.bind(me.onPerPageChange, me)
            }
        });

        var toolbar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom',
            displayInfo: true
        });

        toolbar.add([{ xtype: 'tbspacer' }, combo]);
        combo.setValue(toolbar.store.pageSize);

        return toolbar;
    },

    /**
     * Formats the date column
     *
     * @param value [string] - The order time value
     * @return [string] - The passed value, formatted with Ext.util.Format.date()
     */
    dateColumn: function (value) {
        return !value ? value : Ext.util.Format.date(value);
    },

    onPerPageChange: function(comp, newValue) {
        var me = this;

        me.store.pageSize = newValue;
        me.store.load();
    }
});
// {/block}
