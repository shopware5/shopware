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
//{block name="backend/order/view/detail/debit"}
Ext.define('Shopware.apps.Order.view.detail.Debit', {
    /**
     * Define that the debit field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.form.FieldSet',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-debit-field-set',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'debit-field-set',
    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title:'{s name=debit/title}Payment data{/s}',
        payment:'{s name=debit/current_payment}Payment method{/s}',
        account:'{s name=debit/account}Account{/s}',
        accountHolder:'{s name=debit/account_holder}Account holder{/s}',
        bankName:'{s name=debit/bank_name}Bank name{/s}',
        bankCode:'{s name=debit/bank_code}Bank code{/s}'
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
    initComponent:function () {
        var me = this;

        me.registerEvents();

        me.title = me.snippets.title;

        me.items = me.createItems();

        me.callParent(arguments);

        if ( me.record.get('paymentId') !== 2 ) {
            me.fieldContainer.hide();
        }
    },

    createItems: function() {
        var me = this;

        me.topContainer = Ext.create('Ext.container.Container', {
            layout: 'anchor',
            items:me.createTopElements()
        });

        me.fieldContainer = Ext.create('Ext.container.Container', {
            layout:'column',
            defaults: { columnWidth: .5 },
            items:me.createDebitForm()
        });
        me.debitContainer = me.fieldContainer;

        return [ me.topContainer, me.fieldContainer ];
    },

    /**
     * Registers the "changePayment" event which is handled in the detail controller
     * and will be fired when the user change the payment combo box.
     * @return void
     */
    registerEvents:function () {
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
    createTopElements:function () {
        return [ this.createPaymentCombo() ];
    },

    createPaymentCombo: function() {
        var me = this;

        me.paymentCombo = Ext.create('Ext.form.field.ComboBox', {
            name:'paymentId',
            triggerAction:'all',
            fieldLabel:me.snippets.payment,
            store: me.paymentsStore,
            valueField:'id',
            displayField:'description',
            allowBlank:false,
            required:true,
            anchor:'97.5%',
            labelWidth: 155,
            minWidth:250,
            labelStyle: 'font-weight: 700;',
            editable:false,
            queryMode: 'local',
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
                        //highlight all inactive payment methods of the bound list
                        var record = me.paymentCombo.getStore().findRecord('id', id);
                        return !record.get('active');
                    }
                }
            ),
            listeners: {
                afterrender: function(field) {
                    //initial call
                    me.highlightPaymentComboBox(field);
                    this.on('change', function(field) {
                        me.highlightPaymentComboBox(field);
                    });
                },
                change:function (field, newValue) {
                    me.fireEvent('changePayment', newValue, me.fieldContainer);
                }
            }
        });

        return me.paymentCombo;
    },

    /**
     * highlights the inactive payment methods of the payment comboBox
     *
     * @param field
     */
    highlightPaymentComboBox: function (field) {
        var store = field.getStore();
            var selectedRecord = store.findRecord('id', field.getValue()),
            input = Ext.get(field.getEl().down('.x-form-field'));

        if (!selectedRecord || !selectedRecord.get('active')) {
            input.setStyle({ 'color': '#A94442', 'background': '#F2DEDE' });
        }
        else {
            input.setStyle({ 'background': '', 'color': '' });
        }
    },

    /**
     * Creates the both containers for the field set
     * to display the form fields in two columns.
     *
     * @return [Array] Contains the left and right container
     */
    createDebitForm:function () {
        var leftContainer, rightContainer, me = this;

        leftContainer = Ext.create('Ext.container.Container', {
            layout:'anchor',
            cls: Ext.baseCSSPrefix + 'field-set-container',
            defaults:{
                anchor:'95%',
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px'
                },
                labelWidth: 155,
                minWidth:250,
                xtype:'textfield'
            },
            items:me.createLeftElements()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            layout:'anchor',
            cls: Ext.baseCSSPrefix + 'field-set-container',
            defaults:{
                anchor:'100%',
                labelWidth:155,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px'
                },
                xtype:'textfield'
            },
            items:me.createRightElements()
        });

        return [ leftContainer, rightContainer ];
    },

    /**
     * Creates the form elements for the left container.
     *
     * @return [Array] Contains the account name and account holder
     */
    createLeftElements:function () {
        var me = this;
        return [{
            name:'debit[account]',
            alias:'account',
            fieldLabel:me.snippets.account
        }, {
            name:'debit[accountHolder]',
            alias:'holder',
            fieldLabel:me.snippets.accountHolder
        }];
    },

    /**
     * Creates the form elements for the right container.
     *
     * @return [Array] Contains the bank name and code
     */
    createRightElements:function () {
        var me = this;
        return [{
            name:'debit[bankName]',
            alias:'bankName',
            fieldLabel:me.snippets.bankName
        }, {
            name:'debit[bankCode]',
            alias:'bankCode',
            fieldLabel:me.snippets.bankCode
        }];
    }
});
//{/block}
