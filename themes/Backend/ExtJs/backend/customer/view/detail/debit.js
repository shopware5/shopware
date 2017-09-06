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

// {namespace name=backend/customer/view/detail}

/**
 * Shopware UI - Customer detail page
 *
 * The debit field set contains the debit data of the customer
 * which is stored in the debit model and filled over the s_user_debit table
 */
// {block name="backend/customer/view/detail/debit"}
Ext.define('Shopware.apps.Customer.view.detail.Debit', {
    /**
     * Define that the debit field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend: 'Ext.form.FieldSet',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.customer-debit-field-set',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'debit-field-set',
    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        title: '{s name=debit/title}Payment data{/s}',
        payment: {
            label: '{s name=debit/current_payment}Current payment method{/s}',
            helpTitle: '{s name=debit/payment_help_title}Payment method{/s}',
            helpText: '{s name=debit/payment_help_text}If you change the payment on this menu item, the payment kind of the customer will not be considered by the risk management.{/s}'
        },
        account: '{s name=debit/account}Account{/s}',
        accountHolder: '{s name=debit/account_holder}Account holder{/s}',
        bankName: '{s name=debit/bank_name}Bank name{/s}',
        bankCode: '{s name=debit/bank_code}Bank code{/s}'
    },

    /**
     * Component event method which is fired when the component
     * is initials. The component is initials when the user
     * want to create a new customer or edit an existing customer
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.registerEvents();
        me.title = me.snippets.title;
        me.topContainer = Ext.create('Ext.container.Container', {
            layout: 'anchor',
            items: me.createDebitTopForm()
        });
        me.fieldContainer = Ext.create('Ext.container.Container', {
            layout: 'column',
            items: me.createDebitForm()
        });
        me.items = [ me.topContainer, me.fieldContainer ];

        me.callParent(arguments);

        if (me.record.get('paymentId') !== 2) {
            me.fieldContainer.hide();
        }
    },

    /**
     * Registers the "changePayment" event which is handled in the detail controller
     * and will be fired when the user change the payment combo box.
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
            /**
             * Event will be fired when the user change the payment combo box which
             * is displayed on bottom of the detail page.
             *
             * @event
             * @param [object] value     - the new value of the combo box
             * @param [object] container - The field container which contains the debit account fields
             *
             */
            'changePayment'
        );
    },

    /**
     * Creates the container which contains the combo box
     * for the payments.
     *
     * @return [Array] Container which contains the payment combo box
     */
    createDebitTopForm: function () {
        var me = this;

        return me.createDebitFormTopElements();
    },

    /**
     * Creates the container which contains the combo box
     * for the payments.
     *
     * @return [Array] Container which contains the payment combo box
     */
    createDebitFormTopElements: function () {
        var me = this;

        me.paymentCombo = Ext.create('Ext.form.field.ComboBox', {
            name: 'paymentId',
            queryMode: 'local',
            triggerAction: 'all',
            fieldLabel: me.snippets.payment.label,
            helpTitle: me.snippets.payment.helpTitle,
            helpText: me.snippets.payment.helpText,
            valueField: 'id',
            displayField: 'description',
            allowBlank: false,
            required: true,
            anchor: '100%',
            labelWidth: 155,
            minWidth: 250,
            editable: false,
            tpl: Ext.create('Ext.XTemplate',
                '<tpl for=".">',
                '<tpl if="this.doHighlight(id)">',
                '<div class="x-boundlist-item" style="background-color:#F2DEDE; color: #A94442">{literal}{description}{/literal}</div>',
                '<tpl else>',
                '<div class="x-boundlist-item">{literal}{description}{/literal}</div>',
                '</tpl>',
                '</tpl>',
                {
                    doHighlight: function (id) {
                        // highlight all inactive payment methods of the boundlist
                        var record = me.paymentCombo.getStore().findRecord('id', id);
                        return !record.get('active');
                    }
                }
            ),
            listeners: {
                change: function (field, newValue) {
                    var store = field.getStore(),
                        selectedRecord = store.findRecord('id', newValue),
                        input = Ext.get(field.getEl().down('.x-form-field'));

                    if (!selectedRecord) {
                        input.setStyle({ 'color': '#A94442', 'background': '#F2DEDE' });
                        return;
                    }
                    if (!selectedRecord.get('active')) {
                        input.setStyle({ 'color': '#A94442', 'background': '#F2DEDE' });
                    } else {
                        input.setStyle({ 'background': '', 'color': '' });
                    }
                    me.fireEvent('changePayment', newValue, me.fieldContainer);
                }
            }
        });

        return [
            {
                xtype: 'container',
                columnWidth: 0.5,
                border: false,
                layout: 'anchor',
                cls: Ext.baseCSSPrefix + 'field-set-container',
                items: [ me.paymentCombo ]
            }
        ];
    },

    /**
     * Creates the both containers for the field set
     * to display the form fields in two columns.
     *
     * @return [Array] Contains the left and right container
     */
    createDebitForm: function () {
        var leftContainer, rightContainer, me = this;

        leftContainer = Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            border: false,
            layout: 'anchor',
            cls: Ext.baseCSSPrefix + 'field-set-container',
            defaults: {
                anchor: '100%',
                labelWidth: 155,
                minWidth: 250,
                xtype: 'textfield'
            },
            items: me.createDebitFormLeft()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            border: false,
            layout: 'anchor',
            cls: Ext.baseCSSPrefix + 'field-set-container',
            defaults: {
                anchor: '100%',
                labelWidth: 155,
                xtype: 'textfield'
            },
            items: me.createDebitFormRight()
        });

        return [ leftContainer, rightContainer ];
    },

    /**
     * Creates the form elements for the left container.
     *
     * @return [Array] Contains the account name and account holder
     */
    createDebitFormLeft: function () {
        var me = this;
        return [{
            name: 'debit[account]',
            alias: 'account',
            fieldLabel: me.snippets.account
        }, {
            name: 'debit[accountHolder]',
            alias: 'holder',
            fieldLabel: me.snippets.accountHolder
        }];
    },

    /**
     * Creates the form elements for the right container.
     *
     * @return [Array] Contains the bank name and code
     */
    createDebitFormRight: function () {
        var me = this;
        return [{
            name: 'debit[bankName]',
            alias: 'bankName',
            fieldLabel: me.snippets.bankName
        },
        {
            name: 'debit[bankCode]',
            alias: 'bankCode',
            fieldLabel: me.snippets.bankCode
        }];
    }
});
// {/block}
