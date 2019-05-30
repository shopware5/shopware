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
 * Shopware UI - Order list filter panel
 *
 * Displayed on the left side of the order list module.
 */
//{block name="backend/order/view/list/filter"}
Ext.define('Shopware.apps.Order.view.list.Filter', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.panel.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-list-filter',

    /**
     * An optional extra CSS class that will be added to this component's Element.
     */
    cls: Ext.baseCSSPrefix + 'filter-options',

    /**
     * True to use overflow:'auto' on the components layout element and show scroll bars automatically when necessary, false to clip any overflowing content.
     */
    autoScroll: true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title:'{s name=filter/title}Filter options{/s}',
        from: '{s name=filter/from}From{/s}',
        to: '{s name=filter/to}To{/s}',
        orderState: '{s name=filter/orderState}Order status{/s}',
        paymentState: '{s name=filter/paymentState}Payment status{/s}',
        paymentName: '{s name=filter/paymentName}Payment method{/s}',
        dispatchName: '{s name=filter/dispatchName}Shipping type{/s}',
        customerGroup: '{s name=filter/customerGroup}Customer group{/s}',
        shop: '{s name=filter/shop}Shop{/s}',
        perform: '{s name=filter/perform}Perform filters{/s}',
        reset: '{s name=filter/reset}Reset filters{/s}',
        empty: '{s name=filter/empty}Display all{/s}',
        article: '{s name=filter/article}Article{/s}',
        partner: '{s name=filter/partner}Partner{/s}',
        shipping: '{s name=filter/shipping}Shipping country{/s}',
        billing: '{s name=filter/billing}Billing country{/s}',
        document: {
            title: '{s name=document/title}Documents{/s}',
            date: '{s name=document/date}Date{/s}',
            name:  '{s name=document/name}Name{/s}'
        }
    },

    /**
     * Component event which is fired when the component is initials.
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.registerEvents();
        me.items = [ me.createFieldContainer() ];
        me.title = me.snippets.title;
        me.callParent(arguments);
    },

    /**
     * Adds the specified events to the list of events which this Observable may fire.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the "accept filter" button
             * which is placed in the filter options panel on the left hand of the order list.
             *
             * @event
             * @param [object] - Form values
             */
            'acceptFilters',

            /**
             * Event will be fired when the user clicks the "reset filter" button
             * which is placed in the filter options panel on the left hand of the order list.
             *
             * @event
             * @param [object] - Form
             */
            'resetFilters'
        );
    },

    /**
     * Creates the outer container for the filter options panel.
     * @return [Ext.container.Container]
     */
    createFieldContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            border: false,
            padding: 10,
            items: [
                me.createFilterForm(),
                me.createFilterButtons(),
                me.createDocumentsGrid()
            ]
        });
    },

    /**
     * Creates the form filter fields which displayed on the left hand of
     * the order list. The filters will be perform by the "Perform filters" button
     * which displayed under the form.
     * @return [Ext.form.Panel]
     */
    createFilterForm: function() {
        var me = this;


        me.filterForm = Ext.create('Ext.form.Panel', {
            border: false,
            cls: Ext.baseCSSPrefix + 'filter-form',
            defaults:{
                anchor:'98%',
                labelWidth:155,
                minWidth:250,
                xtype:'pagingcombo',
                style: 'box-shadow: none;',
                labelStyle: 'font-weight:700;'
            },
            items: [
                me.createFromField(),
                me.createToField(),
                me.createOrderStatusField(),
                me.createPaymentStatusField(),
                me.createPaymentField(),
                me.createDispatchField(),
                me.createCustomerGroupField(),
                me.createArticleSearch(),
                me.createShopField(),
                me.createPartnerField(),
                me.createDeliveryCountrySelection(),
                me.createBillingCountrySelection()
            ]
        });
        return me.filterForm;
    },

    createFromField: function() {
        var me = this;
        return Ext.create('Ext.form.field.Date', {
            name: 'from',
            fieldLabel: me.snippets.from,
            submitFormat: 'd.m.Y'
        });
    },

    createToField: function() {
        var me = this;
        return Ext.create('Ext.form.field.Date', {
            name: 'to',
            fieldLabel: me.snippets.to,
            submitFormat: 'd.m.Y'
        });
    },

    createOrderStatusField: function() {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'orders.status',
            queryMode: 'local',
            store: me.orderStatusStore,
            valueField: 'id',
            displayField: 'description',
            emptyText: me.snippets.empty,
            fieldLabel: me.snippets.orderState
        });
    },

    createPaymentStatusField: function() {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'orders.cleared',
            queryMode: 'local',
            store: me.paymentStatusStore,
            valueField: 'id',
            displayField: 'description',
            emptyText: me.snippets.empty,
            fieldLabel: me.snippets.paymentState
        });
    },

    createPaymentField: function() {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'orders.paymentId',
            pageSize: 7,
            queryMode: 'remote',
            store: Ext.create('Shopware.store.Payment', { pageSize: 7 }),
            valueField: 'id',
            displayField: 'description',
            emptyText: me.snippets.empty,
            fieldLabel: me.snippets.paymentName
        });
    },

    createDispatchField: function() {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'orders.dispatchId',
            pageSize: 7,
            queryMode: 'remote',
            store: Ext.create('Shopware.store.Dispatch', { pageSize: 7 }),
            valueField: 'id',
            displayField: 'name',
            emptyText: me.snippets.empty,
            fieldLabel: me.snippets.dispatchName
        });
    },


    createCustomerGroupField: function() {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'customer.groupKey',
            store: Ext.create('Shopware.store.CustomerGroup', { pageSize: 7 }),
            valueField: 'key',
            pageSize: 7,
            queryMode: 'remote',
            displayField: 'name',
            emptyText: me.snippets.empty,
            fieldLabel: me.snippets.customerGroup
        });
    },

    createArticleSearch: function() {
        var me = this;

        return Ext.create('Shopware.form.field.ArticleSearch', {
            name: 'details.articleNumber',
            fieldLabel: me.snippets.article,
            store: Ext.create('Shopware.apps.Base.store.Variant'),
            anchor: '99%'
        });
    },

    createShopField: function() {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'orders.shopId',
            store: Ext.create('Shopware.store.Shop', { pageSize: 7 }),
            valueField: 'id',
            pageSize: 7,
            queryMode: 'remote',
            displayField: 'name',
            emptyText: me.snippets.empty,
            fieldLabel: me.snippets.shop
        });
    },

    createPartnerField: function() {
        var me = this;

        var store = Ext.create('Ext.data.Store', {
            fields: [
                {
                    name: 'name',
                    type: 'string'
                },
                {
                    name: 'value',
                    type: 'string'
                }
            ],
            remoteSort: true,
            remoteFilter: true,
            pageSize: 10,
            proxy: {
                type: 'ajax',
                url: '{url action=getPartners}',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        });

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'orders.partnerId',
            store: store,
            displayField: 'name',
            valueField: 'value',
            queryMode: 'remote',
            pageSize: 10,
            emptyText: me.snippets.empty,
            fieldLabel: me.snippets.partner
        });
    },

    createDeliveryCountrySelection: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'shipping.countryId',
            store: this.getCountryStore(),
            valueField: 'id',
            queryMode: 'remote',
            displayField: 'name',
            fieldLabel: this.snippets.shipping
        });
    },

    createBillingCountrySelection: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'billing.countryId',
            store: this.getCountryStore(),
            valueField: 'id',
            queryMode: 'remote',
            displayField: 'name',
            fieldLabel: this.snippets.billing
        });
    },

    getCountryStore: function() {
        var selectionFactory = Ext.create('Shopware.attribute.SelectionFactory', {});
        var store = selectionFactory.createEntitySearchStore("Shopware\\Models\\Country\\Country");
        store.pageSize = 999;

        store.sort([{
            property: 'active',
            direction: 'DESC'
        }, {
            property: 'name',
            direction: 'ASC'
        }]);
        store.remoteSort = true;

        return store;
    },


    /**
     * Creates the "reset filters" and "perform filters" button
     * which displayed in the filter options panel on the left hand
     * of the order list.
     * @return [Ext.container.Container]
     */
    createFilterButtons: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            style: 'margin-top: 10px;',
            items: [
                {
                    xtype: 'button',
                    cls: 'small secondary',
                    text: me.snippets.reset,
                    handler: function() {
                        me.fireEvent('resetFilters', me.filterForm);
                    }
                },
                {
                    xtype: 'button',
                    text: me.snippets.perform,
                    style: 'float: right;',
                    cls: 'primary small',
                    handler: function() {
                        me.fireEvent('acceptFilters', me.filterForm.getValues());
                    }
                }
            ]
        });
    },

    /**
     * Creates a grid panel which displays the last created order documents.
     *
     * @return [Ext.grid.Panel]
     */
    createDocumentsGrid: function() {
        var me = this;

        me.documentGrid =  Ext.create('Shopware.apps.Order.view.list.Document', {
            height: 200,
            title: me.snippets.document.title,
            getColumns: function() {
                var grid = this;
                return [
                    {
                        header: grid.snippets.columns.date,
                        dataIndex: 'date',
                        flex: 1,
                        renderer: grid.dateColumn
                    }, {
                        header: grid.snippets.columns.name,
                        dataIndex: 'name',
                        flex: 2,
                        renderer: grid.nameColumn
                    }
                ];
            },
            store: me.documentStore
        });

        return Ext.create('Ext.container.Container', {
            style: 'margin-top: 15px',
            items: [ me.documentGrid ]
        });
    }

});
//{/block}
