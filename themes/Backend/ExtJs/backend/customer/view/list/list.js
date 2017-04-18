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
// {block name="backend/customer/view/list/list"}
Ext.define('Shopware.apps.Customer.view.list.List', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
    */
    alias: 'widget.customer-list',

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

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        columns: {
            number: '{s name=column/number}Customer number{/s}',
            firstName: '{s name=column/first_name}First name{/s}',
            lastName: '{s name=column/last_name}Last name{/s}',
            date: '{s name=column/date}Date{/s}',
            customerGroup: '{s name=column/customer_group}Customer group{/s}',
            company: '{s name=column/company}Company{/s}',
            zipCode: '{s name=column/zip_code}Zip code{/s}',
            city: '{s name=column/city}City{/s}',
            accountMode: '{s name=column/accountMode}Type{/s}',
            orderCount: '{s name=column/orderCount}Number of orders{/s}',
            sales: '{s name=column/sales}Turnover{/s}',
            remove: '{s name=column/delete}Delete customer{/s}',
            edit: '{s name=column/detail}Show customer details{/s}'
        }
    },

    /**
     * Initialize the Shopware.apps.Customer.view.main.List and defines the necessary
     * default configuration
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.registerEvents();
        /* {if {acl_is_allowed privilege=delete}} */
        me.selModel = me.getGridSelModel();
        /* {/if} */
        me.columns = me.getColumns();

        me.dockedItems = [ me.getPagingBar() ];

        me.callParent(arguments);

        var header = me.headerCt;

        header.on('menucreate', function (ct, menu, eOpts) {
            menu.remove(menu.items.items[2]);
            menu.remove(menu.items.items[1]);
            menu.remove(menu.items.items[0]);

            menu.add([
                me.createSortingItem('Kundennummber', 'customernumber'),
                me.createSortingItem('Kunde seit', 'firstlogin'),
                me.createSortingItem('Kundengruppe', 'customerGroup'),
                me.createSortingItem('Kundenname', 'lastname'),
                me.createSortingItem('Stadt', 'city'),
                me.createSortingItem('Postleitzahl', 'zipcode'),
                me.createSortingItem('Straße', 'street'),
                me.createSortingItem('Gesamtumsatz', 'invoice_amount_sum'),
                me.createSortingItem('Ø Warenkorb', 'invoice_amount_avg'),
                me.createSortingItem('Ø Warenwert', 'product_avg'),
                me.createSortingItem('Anzahl Bestellungen', 'count_orders'),
                me.createSortingItem('Letzte Bestellung', 'last_order_time'),
                me.createSortingItem('Interessen', 'interests')
            ]);
        });
    },

    createSortingItem: function(text, field) {
        var me = this;

        return {
            text: text,
            field: field,
            menu: {
                items: [
                    { text: 'Aufsteigend',
                        handler: function () {
                            me.sortingHandler(field, 'asc');
                        } },
                    { text: 'Absteigend',
                        handler: function () {
                            me.sortingHandler(field, 'desc');
                        } }
                ]
            }
        };
    },

    sortingHandler: function(field, direction) {
        var me = this;

        me.getStore().sort({
            property: field,
            direction: direction
        });
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the delete icon in the
             * action column
             *
             * @event deleteColumn
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'deleteColumn',

            /**
             * Event will be fired when the user clicks the delete icon in the
             * action column
             *
             * @event deleteColumn
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'editColumn'
        );
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this;

        return [{
            header: 'Information',
            dataIndex: 'customernumber',
            flex: 2,
            renderer: function (value, meta, record) {
                return '<b>' + record.get('customernumber') + '</b> - ' + record.get('customerGroup') +
                    '<br><i>Kunde seit: ' + Ext.util.Format.date(record.get('firstlogin')) + '</i></span>';
            }
        }, {
            header: 'Kunde',
            dataIndex: 'lastname',
            flex: 3,
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
                    company = '<br><i>' + record.get('company') + '</i>';
                }
                return name + age + company;
            }
        }, {
            header: 'Anschrift',
            dataIndex: 'city',
            flex: 3,
            renderer: function(v, meta, record) {
                var lines = [
                    record.get('street'),
                    [record.get('zipcode'), record.get('city'), record.get('country')].join(' '),
                    record.get('additional_address_line1'),
                    record.get('additional_address_line2')
                ];
                return lines.join('<br>');
            }
        }, {
            header: 'Umsatz',
            dataIndex: 'invoice_amount_sum',
            flex: 2,
            renderer: function(v, meta, record) {
                return '' +
                    'Gesamt: <b>' + record.get('invoice_amount_sum') + '</b>' +
                    '<br>Ø Warenkorb: <b>' + record.get('invoice_amount_avg') + '</b>' +
                    '<br>Ø Warenwert: <b>' + record.get('product_avg') + '</b>';
            }
        }, {
            header: 'Bestellungen',
            dataIndex: 'count_orders',
            renderer: function(v, meta, record) {
                return '<b>Bestellungen: ' + record.get('count_orders') + '</b>' +
                    '<br>Letzte: ' + Ext.util.Format.date(record.get('last_order_time'));
            }
        }, {
            header: 'Top Interessen',
            dataIndex: 'interests',
            flex: 4,
            renderer: function(v, meta, record) {
                v = record.get('interests');
                if (!v || v.length <= 0) {
                    return 'Nicht bekannt';
                }
                var interests = [];
                Ext.each(v, function(interest) {
                    interests.push('<b>' + interest.categoryName + '</b> - <i>' + interest.manufacturerName + '</i>');
                });
                interests = interests.slice(0, 3);
                return interests.join('<br>');
            }
        }, {
            header: 'Kategoriesierung',
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
        }, {
            xtype: 'actioncolumn',
            width: 70,
            items: [
                    /* {if {acl_is_allowed privilege=delete}} */
                {
                    iconCls: 'sprite-minus-circle-frame',
                    action: 'deleteCustomer',
                    tooltip: me.snippets.columns.remove,
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        me.fireEvent('deleteColumn', record);
                    }
                },
                    /* {/if} */

                    /* {if {acl_is_allowed privilege=detail}} */
                {
                    iconCls: 'sprite-pencil',
                    action: 'editCustomer',
                    tooltip: me.snippets.columns.edit,
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        me.fireEvent('editColumn', record);
                    }
                }
                    /* {/if} */
            ]
        }
        ];
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
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange: function (sm, selections) {
                    me.fireEvent('selection-changed', selections);
                }
            }
        });
    },

    /**
     * Creates the paging toolbar for the customer grid to allow
     * and store paging. The paging toolbar uses the same store as the Grid
     *
     * @return Ext.toolbar.Paging The paging toolbar for the customer grid
     */
    getPagingBar: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom',
            displayInfo: true
        });
    },

    /**
     * Formats the date column
     *
     * @param [string] - The order time value
     * @return [string] - The passed value, formatted with Ext.util.Format.date()
     */
    dateColumn: function (value) {
        return !value ? value : Ext.util.Format.date(value);
    },

    /**
     * Formats the accountMode column
     *
     * @param [string] - accountMode
     * @returns [string] - description
     */
    accountModeRenderer: function (value) {
        if (value) {
            return '{s name="accountModeGuest"}Accountless{/s}';
        }

        return '{s name="accountModeNormal"}Customer{/s}';
    },

    /**
     * Formats the sales column
     * @param [string] - The sales value
     * @return [string] - The passed value, formatted with Ext.util.Format.currency()
     */
    salesColumn: function (value) {
        return !value ? value : Ext.util.Format.currency(value);
    }

});
// {/block}
