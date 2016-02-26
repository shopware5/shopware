//{namespace name="backend/customer/view/detail"}
//{block name="backend/customer/view/detail/debit" append}
Ext.define('Shopware.apps.Customer.view.detail.PaymentMethods', {
    override: 'Shopware.apps.Customer.view.detail.Debit',

    initComponent: function () {
        var me = this;

        me.snippets.sepaIban = '{s namespace="backend/customer/view/detail" name=sepa/iban}IBAN{/s}';
        me.snippets.sepaBic = '{s namespace="backend/customer/view/detail" name=sepa/bic}BIC{/s}';
        me.snippets.sepaUseBillingData = '{s namespace="backend/customer/view/detail" name=sepa/use_billing_data}Use billing data{/s}';

        me.callParent(arguments);

        if (me.record.getPaymentData().first() == Ext.undefined) {
            return;
        }
        if (me.record.getPaymentData().first().get('name') === 'sepa') {
            me.fieldContainer.show();
            me.accountHolderField.hide();
            me.accountNumberField.hide();
            me.bankCodeField.hide();
            me.ibanField.show();
            me.bicField.show();
        }
        if (me.record.getPaymentData().first().get('name') === 'debit') {
            me.fieldContainer.show();
            me.accountNumberField.show();
            me.accountHolderField.show();
            me.bankCodeField.show();
            me.ibanField.hide();
            me.bicField.hide();
        }
    },

    createDebitFormLeft: function () {
        var me = this;

        me.accountNumberField = Ext.create('Ext.form.field.Text', {
            name: 'paymentData[accountNumber]',
            alias: 'accountNumber',
            labelStyle: 'font-weight: 700;',
            style: {
                margin: '0 0 10px'
            },
            labelWidth: 150,
            minWidth: 250,
            fieldLabel: me.snippets.account
        });
        me.accountHolderField = Ext.create('Ext.form.field.Text', {
            name: 'paymentData[accountHolder]',
            alias: 'holder',
            labelStyle: 'font-weight: 700;',
            style: {
                margin: '0 0 10px'
            },
            labelWidth: 150,
            minWidth: 250,
            fieldLabel: me.snippets.accountHolder
        });
        me.ibanField = Ext.create('Ext.form.field.Text', {
            name: 'paymentData[iban]',
            alias: 'sepaIban',
            labelStyle: 'font-weight: 700;',
            labelWidth: 150,
            minWidth: 250,
            fieldLabel: me.snippets.sepaIban
        });
        me.useBillingDataField = Ext.create('Ext.form.field.Checkbox', {
            name: 'paymentData[useBillingData]',
            alias: 'sepaUseBillingData',
            labelStyle: 'font-weight: 700;',
            labelWidth: 150,
            minWidth: 250,
            inputValue: true,
            uncheckedValue: false,
            fieldLabel: me.snippets.sepaUseBillingData
        });

        return [ me.accountNumberField, me.accountHolderField, me.ibanField, me.useBillingDataField ];
    },

    createDebitFormRight: function () {
        var me = this;

        me.bankNameField = Ext.create('Ext.form.field.Text', {
            name: 'paymentData[bankName]',
            alias: 'bankName',
            labelWidth: 100,
            minWidth: 250,
            labelStyle: 'font-weight: 700;',
            style: {
                margin: '0 0 10px'
            },
            xtype: 'textfield',
            fieldLabel: me.snippets.bankName
        });
        me.bankCodeField = Ext.create('Ext.form.field.Text', {
            name: 'paymentData[bankCode]',
            alias: 'bankCode',
            labelWidth: 100,
            minWidth: 250,
            labelStyle: 'font-weight: 700;',
            style: {
                margin: '0 0 10px'
            },
            xtype: 'textfield',
            fieldLabel: me.snippets.bankCode
        });
        me.bicField = Ext.create('Ext.form.field.Text', {
            name: 'paymentData[bic]',
            alias: 'sepaBic',
            labelWidth: 100,
            minWidth: 250,
            labelStyle: 'font-weight: 700;',
            style: {
                margin: '0 0 10px'
            },
            xtype: 'textfield',
            fieldLabel: me.snippets.sepaBic
        });

        return [ me.bankNameField, me.bankCodeField, me.bicField ];
    }
});
//{/block}


