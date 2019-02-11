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
 * Shopware UI - Order detail page
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/detail/overview"}
Ext.define('Shopware.apps.Order.view.detail.Overview', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend: 'Ext.form.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.order-overview-panel',

    /**
     * An optional extra CSS class that will be added to this component's Element.
     */
    cls: Ext.baseCSSPrefix + 'overview-panel shopware-form',

    /**
     * A shortcut for setting a padding style on the body element. The value can either be a number to be applied to all sides, or a normal css string describing padding.
     */
    bodyPadding: 10,

    /**
     * True to use overflow:'auto' on the components layout element and show scroll bars automatically when necessary, false to clip any overflowing content.
     */
    autoScroll: true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        convert: {
            title: '{s name=overview/convert/title}Canceled Order{/s}',
            message: '{s name=overview/convert/message}This is a canceled order. You are able to convert this into a regular order.{/s}',
            button: '{s name=overview/convert/button}Convert this order now{/s}'
        },
        billing: {
            title: '{s name=overview/billing/title}Billing{/s}',
            button: '{s name=overview/billing/button}Show customer details{/s}'
        },
        shipping: {
            title: '{s name=overview/shipping/title}Shipping{/s}',
            button: '{s name=overview/shipping/button}Show customer details{/s}',
            different: '{s name=overview/shipping/different}The shipping and billing addresses do not match!{/s}'
        },
        payment: {
            title: '{s name=overview/payment/title}Payment{/s}',
            button: '{s name=overview/payment/button}Show customer details{/s}'
        },
        edit: {
            title: '{s name=overview/edit/title}Edit order{/s}',
            save: '{s name=overview/edit/save}Save{/s}',
            cancel: '{s name=overview/edit/cancel}Cancel{/s}',
            clearedDate: '{s name=overview/edit/cleared_date}Paid on{/s}',
            trackingCode: '{s name=overview/edit/tracking_code}Tracking code{/s}',
            shippingCost: '{s name=overview/edit/shipping_cost}Shipping costs ([0]){/s}',
            shippingCostNet: '{s name=overview/edit/shipping_cost_net}Shipping costs net ([0]){/s}',
            shippingTaxRate: '{s name=overview/edit/shipping_tax_rate}Shipping tax rate{/s}',
            orderState: '{s name=overview/edit/order_status}Order status{/s}',
            paymentState: '{s name=overview/edit/payment_status}Payment status{/s}'
        },
        details: {
            title: '{s name=overview/details/title}Order details{/s}',
            shop: '{s name=overview/details/shop}Shop{/s}',
            language: '{s name=overview/details/language}Language{/s}',
            orderTime: '{s name=overview/details/orderTime}Order time{/s}',
            number: '{s name=overview/details/number}Order number{/s}',
            currency: '{s name=overview/details/currency}Currency{/s}',
            amount: '{s name=overview/details/amount}Total amount{/s}',
            amountEuro: '{s name=overview/details/amount_euro}Total amount (in Euro){/s}',
            dispatch: '{s name=overview/details/dispatch}Chosen shipping type{/s}',
            remoteAddress: '{s name=overview/details/remote_address}IP address{/s}',
            customerEmail: '{s name=overview/details/customer_email}E-Mail{/s}',
            referer: '{s name=overview/details/referer}Referer{/s}',
            deviceType: '{s name=overview/details/device_type}Device type{/s}',
            partnerId: '{s name=overview/details/partner_id}Partner ID{/s}',
            changed: '{s name=overview/details/changed}Last changed{/s}'
        },
        customerDeleted: '{s name=overview/details/customer_deleted_text}Caution: The assigned customer has been deleted.{/s}',
        openCustomer: '{s name=overview/details/open_customer}Open customer{/s}'
    },

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

        me.registerEvents();
        me.items = [
            me.createConvertOrderContainer(),
            me.createCustomerNotification(),
            me.createCustomerInformation(),
            me.createDetailsContainer(),
            me.createEditContainer(),
            me.createAttributeForm()
        ];
        me.dockedItems = [me.createToolbar()];
        me.callParent(arguments);
        me.detailsForm.loadRecord(me.record);
        me.editForm.loadRecord(me.record);
        me.attributeForm.loadAttribute(me.record.get('id'));
    },

    /**
     * Registers the addition component events.
     */
    registerEvents: function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the save button which is displayed on bottom of
             * the form panel.
             *
             * @event
             * @param [Ext.data.Model] - The form record
             */
            'saveOverview',

            /**
             * Event will be fired when the user clicks the "Edit address and payment".
             *
             * @event
             * @param [Ext.data.Model] - The form record
             */
            'showOrderAddress',

            /**
             * Event will be fired when the user clicks the "Save button" button.
             *
             * @event
             * @param [Ext.data.Model]    record - The current form record
             * @param [Ext.window.window] window - The detail window
             */
            'updateForms',

            /**
             * Event will be fired when the users clicks the "Convert order" button
             * @event
             * @param [Ext.data.Model]   record - The current form record
             */
            'convertOrder'
        );
    },

    /**
     * Creates the "convert canceled order" panel showing a short info text and a button
     *
     * @return Ext.panel.Panel
     */
    createConvertOrderPanel: function () {
        var me = this;

        me.canceledOrderMessage = Shopware.Notification.createBlockMessage(me.snippets.convert.message, 'notice');
        me.canceledOrderButton = Ext.create('Ext.button.Button', {
            text: me.snippets.convert.button,
            cls: 'primary',
            handler: function () {
                me.fireEvent('convertOrder', me.record);
            }
        });

        me.canceledOrderContainer = Ext.create('Ext.panel.Panel', {
            title: me.snippets.convert.title,
            bodyPadding: 10,
            flex: 1,
            paddingRight: 5,
            layout: {
                type: 'vbox',
                align: 'center'
            },
            items: [
                {
                    xtype: 'label',
                    text: me.snippets.convert.message
                },
                me.canceledOrderButton

            ]
        });
        return me.canceledOrderContainer;

    },

    /**
     * Creates the "convert canceled order container"
     * If the current order is not a canceled order, null is returned
     *
     * @return null|Ext.container.Container
     */
    createConvertOrderContainer: function () {
        var me = this,
            orderId = me.record.get('id'),
            status = me.record.get('status');

        if (status !== -1) {
            return null;
        }

        me.convertOrderContainer = Ext.create('Ext.container.Container', {
            minWidth: 250,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            defaults: {
                margin: '10 0'
            },
            items: [
                me.createConvertOrderPanel()
            ]
        });
        return me.convertOrderContainer;
    },

    /**
     * If the customer has been deleted a notification will be display on top of the detail page.
     * @return null|Ext.container.Container
     */
    createCustomerNotification: function () {
        var me = this,
            customer = me.record.getCustomer().first();

        if (customer !== Ext.undefined) {
            return null;
        }
        return Shopware.Notification.createBlockMessage(me.snippets.customerDeleted, 'notice');
    },

    /**
     * Creates the container for the three customer info panels
     *
     * @return Ext.container.Container
     */
    createCustomerInformation: function () {
        var me = this;

        me.billingPanel = me.createBillingContainer();
        me.shippingPanel = me.createShippingContainer();
        me.paymentPanel = me.createPaymentContainer();

        me.customerContainer = Ext.create('Ext.container.Container', {
            height: 170,
            minWidth: 250,
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            defaults: {
                margin: '0 5 0 0'
            },
            items: [
                me.billingPanel,
                me.shippingPanel,
                me.paymentPanel
            ]
        });
        return me.customerContainer;
    },

    /**
     * Creates the Ext.panel.Panel for the billing information.
     */
    createBillingContainer: function () {
        var me = this;

        var billing = me.record.getBilling();
        if (billing === null || billing.first() === null) {
            return;
        }

        billing = billing.first();

        return Ext.create('Ext.panel.Panel', {
            title: me.snippets.billing.title,
            bodyPadding: 10,
            flex: 1,
            paddingRight: 5,
            items: [
                {
                    xtype: 'container',
                    renderTpl: me.createAddressTemplate(),
                    renderData: billing.raw
                }
            ]
        });
    },

    /**
     * Creates the Ext.panel.Panel for the shipping information.
     */
    createShippingContainer: function () {
        var me = this,
            shipping = me.record.getShipping().first();

        if (shipping === Ext.undefined) {
            if (me.record.getBilling() === null || me.record.getBilling().first() === null) {
                return;
            }
            shipping = me.record.getBilling().first();
            if (shipping === null) {
                return;
            }
        }

        return Ext.create('Ext.panel.Panel', {
            title: me.getShippingPanelTitle(),
            bodyPadding: 10,
            flex: 1,
            style: 'padding: 0 8 0 0 !important;',
            items: [
                {
                    xtype: 'container',
                    renderTpl: me.createAddressTemplate(),
                    renderData: shipping.raw
                }
            ]
        });
    },


    /**
     * Checks if the shipping and billing address is different by comparing all their fields, except for the ids.
     * If this is the case, the panel title is colored red and an icon will be displayed to its right.
     *
     * @return string
     */
    getShippingPanelTitle: function () {
        var me = this, icon,
            shipping = me.record.getShipping().first(),
            billing = me.record.getBilling();

        if (shipping === Ext.undefined || billing === Ext.undefined || billing.first() === Ext.undefined) {
            return me.snippets.shipping.title;
        }

        billing = billing.first();

        // Compare ALL fields of the two addresses and if they differ, display an exclamation icon in the title of the box
        if (shipping.get('company') !== billing.get('company') ||
            shipping.get('department') !== billing.get('department') ||
            shipping.get('salutationSnippet') !== billing.get('salutationSnippet') ||
            shipping.get('firstName') !== billing.get('firstName') ||
            shipping.get('lastName') !== billing.get('lastName') ||
            shipping.get('street') !== billing.get('street') ||
            shipping.get('additionalAddressLine1') !== billing.get('additionalAddressLine1') ||
            shipping.get('additionalAddressLine2') !== billing.get('additionalAddressLine2') ||
            shipping.get('zipCode') !== billing.get('zipCode') ||
            shipping.get('city') !== billing.get('city') ||
            shipping.get('stateId') !== billing.get('stateId') ||
            shipping.get('countryId') !== billing.get('countryId')) {

            var helper = new Ext.dom.Helper;
            var iconSpec = {
                tag: 'div',
                cls: 'sprite-exclamation',
                style: 'position: absolute; top: 0; right: 0; data-qwidth="150"; data-qtip="' + me.snippets.shipping.different + '";'
            };
            var headerSpec = {
                tag: 'span',
                html: me.snippets.shipping.title,
                style: 'color: #ff0000;'
            };

            return helper.markup(headerSpec) + helper.markup(iconSpec);
        } else {
            return me.snippets.shipping.title;
        }
    },

    /**
     * Creates the XTemplate for an address information panel, including department and salutation.
     *
     * @return [Ext.XTemplate] generated Ext.XTemplate
     */
    createAddressTemplate: function () {
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="customer-info-pnl">',
                    '<div class="base-info">',
                        '<p>',
                            '<span>{company}</span>',
                        '</p>',
                        '<p>',
                            '<span>{department}</span>',
                        '</p>',
                        '<p>',
                            '<tpl if="salutationSnippet"><span>{salutationSnippet}</span>&nbsp;</tpl>',
                            '<tpl if="title"><span>{title}</span><br /></tpl>',
                            '<span>{firstName}</span>&nbsp;',
                            '<span>{lastName}</span>',
                        '</p>',
                        '<p>',
                            '<span>{street}</span>&nbsp;',
                        '</p>',
                        '<tpl if="additionalAddressLine1">',
                            '<p>',
                                '<span>{additionalAddressLine1}</span>',
                            '</p>',
                        '</tpl>',
                        '<tpl if="additionalAddressLine2">',
                            '<p>',
                                '<span>{additionalAddressLine2}</span>',
                            '</p>',
                        '</tpl>',
                        '<p>',
                            '<span>{zipCode}</span>&nbsp;',
                            '<span>{city}</span>',
                        '</p>',
                        '<tpl for="state">',
                            '<p>',
                            '<span>{name}</span>',
                            '</p>',
                        '</tpl>',
                        '<tpl for="country">',
                            '<p>',
                                '<span>{name}</span>',
                            '</p>',
                        '</tpl>',
                    '</div>',
                '</div>',

            '</tpl>{/literal}'
        );
    },

    /**
     * Creates the Ext.panel.Panel for the payment information.
     */
    createPaymentContainer: function () {
        var me = this, payment;

        if (me.record && me.record.getPayment() instanceof Ext.data.Store && me.record.getPayment().first() instanceof Ext.data.Model) {
            payment = me.record.getPayment().first();

            return Ext.create('Ext.panel.Panel', {
                title: me.snippets.payment.title,
                bodyPadding: 10,
                flex: 1,
                margin: 0,
                items: [
                    {
                        xtype: 'container',
                        renderTpl: me.createPaymentTemplate(),
                        renderData: payment.raw
                    }
                ]
            });
        } else {
            return;
        }
    },

    /**
     * Creates the XTemplate for the billing information panel
     *
     * @return [Ext.XTemplate] generated Ext.XTemplate
     */
    createPaymentTemplate: function () {
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="customer-info-pnl">',
                    '<div class="base-info">',
                        '<p>',
                            '<span>{description}</span>',
                        '</p>',
                    '</div>',
                '</div>',
            '</tpl>{/literal}'
        );
    },

    /**
     * Creates the container for the detail form panel.
     * @return Ext.form.Panel
     */
    createDetailsContainer: function () {
        var me = this;

        me.detailsForm = Ext.create('Ext.form.Panel', {
            title: me.snippets.details.title,
            titleAlign: 'left',
            bodyPadding: 10,
            layout: 'anchor',
            defaults: {
                anchor: '100%'
            },
            margin: '10 0',
            items: [
                me.createInnerDetailContainer()
            ]
        });
        return me.detailsForm;
    },

    /**
     * Creates the outer container for the detail panel which
     * has a column layout to display the detail information in two columns.
     *
     * @return Ext.container.Container
     */
    createInnerDetailContainer: function () {
        var me = this;

        return Ext.create('Ext.container.Container', {
            layout: 'column',
            items: [
                me.createDetailElementContainer(me.createLeftDetailElements()),
                me.createDetailElementContainer(me.createRightDetailElements())
            ]
        });
    },

    /**
     * Creates the column container for the detail elements which displayed
     * in two columns.
     *
     * @param { Array } items - The container items.
     */
    createDetailElementContainer: function (items) {
        return Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            defaults: {
                xtype: 'displayfield',
                labelWidth: 155
            },
            items: items
        });
    },

    /**
     * Creates the elements for the left column container which displays the
     * fields in two columns.
     *
     * @return array - Contains the form fields
     */
    createLeftDetailElements: function () {
        var me = this, fields;
        fields = [
            { name: 'shop[name]', fieldLabel: me.snippets.details.shop},
            { name: 'locale[name]', fieldLabel: me.snippets.details.language},
            { name: 'orderTime', fieldLabel: me.snippets.details.orderTime},
            { name: 'number', fieldLabel: me.snippets.details.number},
            { name: 'currency', fieldLabel: me.snippets.details.currency},
            { name: 'invoiceAmount', fieldLabel: me.snippets.details.amount, renderer: me.renderInvoiceAmount},
            { name: 'dispatch[name]', fieldLabel: me.snippets.details.dispatch},
            { name: 'partnerId', fieldLabel: me.snippets.details.partnerId},
            { name: 'customerEmail', fieldLabel: me.snippets.details.customerEmail, allowHtml: true, renderer: me.renderCustomerEmail}
        ];
        if (me.record.get('currencyFactor') !== 1) {
            fields.push({
                name: 'invoiceAmountEuro',
                fieldLabel: me.snippets.details.amountEuro,
                renderer: me.renderInvoiceAmount
            });
        }
        return fields;
    },

    /**
     * Render function of the customerEmail display field
     * @param value
     * @return string
     */
    renderCustomerEmail: function (value) {
        return (Ext.isDefined(value)) ? Ext.String.format('<a href="mailto:[0]">[0]</a>', value) : value;
    },

    /**
     * Render function of the invoiceAmount display field
     * @param value
     * @return string
     */
    renderInvoiceAmount: function (value) {
        if (value === Ext.undefined) {
            return value;
        }
        return Ext.util.Format.currency(value);
    },

    /**
     * Creates the elements for the right column container which displays the
     * fields in two columns.
     *
     * @return Array - Contains the form fields
     */
    createRightDetailElements: function () {
        var me = this;

        return [
            { name: 'referer', fieldLabel: me.snippets.details.referer},
            { name: 'remoteAddressConverted', fieldLabel: me.snippets.details.remoteAddress},
            { name: 'deviceTypeHuman', fieldLabel: me.snippets.details.deviceType},
            { name: 'changed', fieldLabel: me.snippets.details.changed},
            me.createCustomerButton()
        ];
    },

    /**
     * Adds a link to the customer that placed the order
     *
     * @returns Ext.Button
     */
    createCustomerButton: function() {
        var me = this;
        return {
            disabled: !me.record.getCustomer().first(),
            action:'openCustomer',
            xtype: 'button',
            cls: 'primary',
            text: me.snippets.openCustomer,
            margin: '10 0 0 0',
            handler: function () {
                me.fireEvent('openCustomer', me.record);
            }
        }

    },

    /**
     * Creates the container for the editable fields which displayed
     * on bottom of the detail tab panel.
     *
     * @return Ext.form.Panel
     */
    createEditContainer: function () {
        var me = this;

        me.editForm = Ext.create('Ext.form.Panel', {
            title: me.snippets.edit.title,
            bodyPadding: 10,
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                labelWidth: 155
            },
            items: me.createEditElements()
        });
        return me.editForm;
    },

    /**
     * Creates the save and cancel button for the form panel.
     *
     * @return Array - Contains the cancel button and the save button
     */
    getEditFormButtons: function () {
        var me = this,
            buttons = [];

        buttons.push('->');
        var cancelButton = Ext.create('Ext.button.Button', {
            text: me.snippets.edit.cancel,
            scope: me,
            cls: 'secondary',
            handler: function () {
                me.record.reject();
                me.loadRecord(me.record);
                me.attributeForm.loadAttribute(me.record.get('id'));
            }
        });
        buttons.push(cancelButton);

        var saveButton = Ext.create('Ext.button.Button', {
            text: me.snippets.edit.save,
            action: 'save-order',
            cls: 'primary',
            handler: function () {
                me.editForm.getForm().updateRecord(me.record);
                me.fireEvent('saveOverview', me.record, {
                    callback: function (order) {
                        me.attributeForm.saveAttribute(me.record.get('id'));
                        me.fireEvent('updateForms', order, me.up('window'));
                    }
                });
            }
        });

        //Create a order? Then display only the form panel
        if (!me.record.get('id')) {
            buttons.push(saveButton);
        } else {
            /*{if {acl_is_allowed privilege=update}}*/
            buttons.push(saveButton);
            /*{/if}*/
        }
        return buttons;
    },

    /**
     * Creates the elements for the edit container which displayed
     * on bottom of the detail tab panel.
     *
     * @return array - Contains the different form fields
     */
    createEditElements: function () {
        var me = this;

        me.orderStatusStore.filterBy(function (item) {
            return item.get("id") > -1;
        });

        return [
            {
                xtype: 'datefield',
                name: 'clearedDate',
                submitFormat: 'd.m.Y',
                fieldLabel: me.snippets.edit.clearedDate
            },
            {
                xtype: 'textfield',
                name: 'trackingCode',
                fieldLabel: me.snippets.edit.trackingCode
            },
            {
                xtype: 'numberfield',
                decimalPrecision: 2,
                submitLocaleSeparator: false,
                name: 'invoiceShipping',
                fieldLabel: Ext.String.format(me.snippets.edit.shippingCost, me.record.get('currency'))
            },
            {
                xtype: 'numberfield',
                decimalPrecision: 2,
                submitLocaleSeparator: false,
                name: 'invoiceShippingNet',
                fieldLabel: Ext.String.format(me.snippets.edit.shippingCostNet, me.record.get('currency'))
            },
            {
                xtype: 'numberfield',
                decimalPrecision: 2,
                submitLocaleSeparator: false,
                name: 'invoiceShippingTaxRate',
                disabled: me.record.get('isProportionalCalculation'),
                fieldLabel: me.snippets.edit.shippingTaxRate
            },
            {
                xtype: 'combobox',
                queryMode: 'local',
                name: 'status',
                dataIndex: 'status',
                fieldLabel: me.snippets.edit.orderState,
                store: me.orderStatusStore,
                displayField: 'description',
                valueField: 'id'
            },
            {
                xtype: 'combobox',
                queryMode: 'local',
                name: 'cleared',
                fieldLabel: me.snippets.edit.paymentState,
                store: me.paymentStatusStore,
                displayField: 'description',
                valueField: 'id'
            }
        ];
    },

    /**
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function () {
        var me = this;
        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: me.getEditFormButtons()
        });
        return me.toolbar;
    },

    /**
     * @returns { Shopware.attribute.Form }
     */
    createAttributeForm: function () {
        var me = this;

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_order_attributes',
            name: 'order-attributes',
            title: '{s name="attribute_title"}{/s}',
            border: true,
            margin: '10 0',
            bodyPadding: 10,
            listeners: {
                'hide-attribute-field-set': function () {
                    me.attributeForm.hide();
                }
            }
        });
        return me.attributeForm;
    }
});
//{/block}
