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
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/customer/view/detail}

/**
 * Shopware UI - Customer list detail page
 *
 * This component represents the window for the detail page of a customer record.
 */
//{block name="backend/customer/view/detail/window"}
Ext.define('Shopware.apps.Customer.view.detail.Window', {
    /**
     * Define that the customer detail window is an extension of the Enlight application window
     * @string
     */
    //extend:'Enlight.app.Window',
    extend: 'Enlight.app.Window',

    /**
     * Set the border layout for the detail window.
     * @string
     */
    layout:'fit',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.customer-detail-window',
    /**
     * Define the width of the window
     * @integer
     */
    width:1020,
    /**
     * Define the height of the window
     * @integer
     */
    height:'90%',

    /**
     * Display no footer button for the detail window
     * @boolean
     */
    footerButton:false,
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,

    /**
     * Set css class for sass styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'customer-detail-window',
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-customer-detail-window',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        titleCreate:'{s name=window/create_title}Customer administration - Create a customer{/s}',
        titleEdit:'{s name=window/edit_title}Customer account:{/s}',
        cancel:'{s name=window/cancel}Cancel{/s}',
        save:'{s name=window/save}Save{/s}',
        dataTab:'{s name=window/data_tab}Data{/s}',
        orderTab:'{s name=window/order_tab}Orders{/s}',
        from:'{s name=window/from_date}From{/s}',
        to:'{s name=window/to_date}To{/s}'
    },

    /**
     * Component event method which is fired when the window is initialed.
     * @return void
     */
    initComponent:function () {
        var me = this;

        // Set the basic window title
        me.title = me.snippets.titleCreate;

        me.callParent(arguments);

        // Create the content if we're having an record on start up
        if(me.record) {
            me.createTabPanel();
        }
    },

    /**
     * @param model
     */
    setStores: function(stores) {
        var me = this, billing = null, shipping = null, state, countryStore;

        me.baseFieldSet.customerGroupCombo.bindStore(stores.getCustomerGroupStore);
        me.baseFieldSet.shopStoreCombo.bindStore(stores.getShopStore);
        me.billingFieldSet.countryCombo.bindStore(stores.getCountryStore);
        me.shippingFieldSet.countryCombo.bindStore(stores.getCountryStore);
        me.debitFieldSet.paymentCombo.bindStore(stores.getPaymentStore);
        me.paymentStore = stores.getPaymentStore;

        if(me.hasOwnProperty('orderGrid')) {
            me.orderGrid.dispatchStore = stores.getDispatchStore;
            me.orderGrid.orderStatusStore = stores.getOrderStatusStore;
            me.orderGrid.paymentStore = stores.getPaymentStore;
            me.orderGrid.paymentStatusStore = stores.getPaymentStatusStore;
        }
        me.detailForm.loadRecord(me.record);
        var billingComboStateStore = Ext.create('Shopware.store.CountryState'),
            shippingComboStateStore = Ext.create('Shopware.store.CountryState');

        me.billingFieldSet.countryStateCombo.bindStore(billingComboStateStore);
        me.shippingFieldSet.countryStateCombo.bindStore(shippingComboStateStore);

        if (me.record instanceof Ext.data.Model &&
            me.record.getBilling() instanceof Ext.data.Store &&
            me.record.getBilling().first() instanceof Ext.data.Model) {

            billing = me.record.getBilling().first();

            if(billing.get('countryId')) {

                billingComboStateStore.getProxy().extraParams.countryId = billing.get('countryId');
                billingComboStateStore.load({
                    callback: function() {
                        if(billing.get('stateId')) {
                            me.billingFieldSet.countryStateCombo.setValue(billing.get('stateId'));
                            me.billingFieldSet.countryStateCombo.show();
                        }
                        else {
                            me.billingFieldSet.countryStateCombo.setValue(null);
                            me.billingFieldSet.countryStateCombo.hide();
                            billing.set('stateId',null);
                        }
                    }
                });
            }
        }
        else {
            me.billingFieldSet.countryStateCombo.setValue(null);
        }

        if (me.record instanceof Ext.data.Model &&
                me.record.getShipping() instanceof Ext.data.Store &&
                me.record.getShipping().first() instanceof Ext.data.Model) {

            shipping = me.record.getShipping().first();

            if(shipping.get('countryId')) {
                shippingComboStateStore.getProxy().extraParams.countryId = shipping.get('countryId');
                shippingComboStateStore.load({
                    callback: function() {
                        me.shippingFieldSet.countryStateCombo.setValue(shipping.get('stateId'));
                        if(shipping.get('stateId')) {
                            me.shippingFieldSet.countryStateCombo.setValue(shipping.get('stateId'));
                            me.shippingFieldSet.countryStateCombo.show();
                        }
                        else {
                            me.shippingFieldSet.countryStateCombo.setValue(null);
                            me.shippingFieldSet.countryStateCombo.hide();
                            shipping.set('stateId',null);
                        }
                    }
                });
            }
        }
        else {
            me.shippingFieldSet.countryStateCombo.setValue(null);
        }


        if (!me.record.get('id') ) {
            me.detailForm.getForm().clearInvalid();
        }
    },


    /**
     * Helper method which creates the tabpanel.
     *
     * @public
     * @return void
     */
    createTabPanel: function() {
        var me = this;

        // Add the tab panel to the window
        me.add(Ext.create('Ext.tab.Panel', {
            items: me.getTabs()
        }));

        // Change the window title
        me.setWindowTitle();
    },

    /**
     * Internal helper function which sets the window title
     *
     * @public
     * @return void
     */
    setWindowTitle:function () {
        var me = this;

        //set different titles for create and edit customers
        if ( me.record.get('id') ) {
            me.setTitle(me.snippets.titleEdit + ' ' + me.record.getBilling().getAt(0).get('number'));
        } else {
            me.setTitle(me.snippets.titleCreate);
        }
    },

    /**
     * Creates the tabs for the tab panel of the window.
     * Contains the detail form which is used to display the customer data for an existing customer
     * or to create a new customer.
     * Can contains additionally an second tab which displays the customer orders and a chart which
     * displays the orders grouped by the order year and month
     * @return [array] - Contains the data and order tab container
     */
    getTabs:function () {
        var me = this,
            form = me.createFormTab();

        if ( me.record.get('id') ) {
            /*{if {acl_is_allowed resource=order privilege=read}}*/
                return [ form, me.createOrderTab() ];
            /*{else}*/
                return [ form ];
            /*{/if}*/
        } else {
            return [ form ];
        }

    },

    /**
     * Creates the customer data tab which contains the different field sets like billing or shipping
     * to edit or create a new customer.
     * @return [Ext.container.Container] - Contains the data and order tab
     */
    createFormTab:function () {
        var me = this, additional;

        //Create a customer? Then display only the form panel
        if (me.record.get('id')) {
            additional = Ext.create('Shopware.apps.Customer.view.detail.Additional', {
                region:'east',
                width: 205,
                minSize: 205,
                maxSize: 250,
                record: me.record,
                collapsible: true
            });
        }

        me.baseFieldSet = Ext.create('Shopware.apps.Customer.view.detail.Base', { record: me.record });
        me.commentFieldSet = Ext.create('Shopware.apps.Customer.view.detail.Comment', { record: me.record });
        me.billingFieldSet = Ext.create('Shopware.apps.Customer.view.detail.Billing', { record: me.record });
        me.shippingFieldSet = Ext.create('Shopware.apps.Customer.view.detail.Shipping', { record: me.record });
        me.debitFieldSet = Ext.create('Shopware.apps.Customer.view.detail.Debit', { record: me.record });

        me.detailForm = Ext.create('Ext.form.Panel', {
            collapsible: false,
            region:'center',
            minWidth: 600,
            bodyPadding:10,
            autoScroll:true,
            items:[
                me.baseFieldSet,
                me.commentFieldSet,
                me.billingFieldSet,
                me.shippingFieldSet,
                me.debitFieldSet
            ],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'shopware-ui',
                cls: 'shopware-toolbar',
                items: me.getFormButtons()
            }]
        });

        return Ext.create('Ext.container.Container', {
            layout: 'border',
            title:me.snippets.dataTab,
            items:
            [
                additional,
                me.detailForm
            ]
        });
    },

    /**
     * Creates the save and cancel button for the form panel.
     * @return [array] - Contains the cancel button and the save button
     */
    getFormButtons: function() {
        var me = this,
            buttons = [ '->' ];

        var cancelButton = Ext.create('Ext.button.Button', {
            text: me.snippets.cancel,
            scope: me,
            cls: 'secondary',
            handler:function () {
                me.destroy();
            }
        });
        buttons.push(cancelButton);

        var saveButton = Ext.create('Ext.button.Button', {
            text:me.snippets.save,
            action:'save-customer',
            cls:'primary'
        });

        //Create a customer? Then display only the form panel
        if ( !me.record.get('id') ) {
            buttons.push(saveButton);
        } else {
            /*{if {acl_is_allowed privilege=update}}*/
                buttons.push(saveButton);
            /*{/if}*/
        }
        return buttons;
    },

    /**
     * Creates the customer order tab which contains a grid with all customer orders and a chart
     * which displays the customer orders grouped by the order year and month
     * @return [Ext.container.Container] - Contains the order grid and the order chart
     */
    createOrderTab:function () {
        var me = this,
            gridStore = Ext.create('Shopware.apps.Customer.store.Orders'),
            chartStore = Ext.create('Shopware.apps.Customer.store.Chart');

        gridStore.getProxy().extraParams = { customerID:me.record.data.id };
        chartStore.getProxy().extraParams = { customerID:me.record.data.id };

        me.orderGrid = Ext.create('Shopware.apps.Customer.view.order.List', {
            flex: 1,
            gridStore: gridStore.load()
        })

        me.orderToolbar = me.createOrderToolbar();

        return Ext.create('Ext.container.Container', {
            layout: {
                type: 'vbox',
                align : 'stretch'
            },
            defaults: { flex: 1 },
            title: me.snippets.orderTab,
            items: [{
                xtype: 'panel',
                unstyled: true,
                layout: 'border',
                items: [{
                    xtype:'customer-list-order-chart',
                    region: 'center',
                    store:chartStore.load()
                }],
                dockedItems: [ me.orderToolbar ]
            }, me.orderGrid ]
        });
    },

    /**
     * Creates the toolbar for the order tab.
     * The toolbar contains two date fields (from, to) which allows the user to filter the chart store.
     *
     * @return [Ext.toolbar.Toolbar] - Toolbar for the order tab which contains the from and to date field to filter the chart
     */
    createOrderToolbar:function () {
        var me = this,
            today = new Date();

        me.fromDateField = Ext.create('Ext.form.field.Date', {
            labelWidth:45,
            name:'fromDate',
            fieldLabel:me.snippets.from,
            value:new Date(today.getFullYear() - 1, today.getMonth(), today.getDate())
        });

        me.toDateField = Ext.create('Ext.form.field.Date', {
            labelWidth:45,
            name:'toDate',
            fieldLabel:me.snippets.to,
            value:today
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui:'shopware-ui',
            padding: '10 0 5',
            cls: Ext.baseCSSPrefix + 'order-chart-toolbar',
            items:[
                { xtype:'tbspacer', width:6 },
                me.fromDateField,
                { xtype:'tbspacer', width:12 },
                me.toDateField
            ]
        });
    }

});
//{/block}
