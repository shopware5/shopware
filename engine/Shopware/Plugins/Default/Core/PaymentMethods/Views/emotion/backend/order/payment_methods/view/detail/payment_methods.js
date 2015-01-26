//{namespace name="backend/order/main"}
//{block name="backend/order/view/detail/debit" append}
Ext.define('Shopware.apps.Order.view.detail.PaymentMethods', {
    override: 'Shopware.apps.Order.view.detail.Debit',

    initComponent: function () {
        var me = this;

        me.snippets.sepaIban = '{s namespace="backend/order/main" name=sepa/iban}IBAN{/s}';
        me.snippets.sepaBic = '{s namespace="backend/order/main" name=sepa/bic}BIC{/s}';

        me.callParent(arguments);

        if (Ext.isEmpty(me.record.getPayment().first())) {
            return;
        }

        if (me.record.getPayment().first().get('name') === 'sepa') {
            me.fieldContainer.show();
            me.accountHolderField.hide();
            me.accountNumberField.hide();
            me.bankCodeField.hide();
            me.ibanField.show();
            me.bicField.show();
        }
        if (me.record.getPayment().first().get('name') === 'debit') {
            me.fieldContainer.show();
            me.accountNumberField.show();
            me.accountHolderField.show();
            me.bankCodeField.show();
            me.ibanField.hide();
            me.bicField.hide();
        }
    },

    createLeftElements: function () {
        var me = this;

        me.accountNumberField = Ext.create('Ext.form.field.Text', {
            name: 'paymentInstances[accountNumber]',
            alias: 'accountNumber',
            readOnly: true,
            anchor: '95%',
            labelStyle: 'font-weight: 700;',
            style: {
                margin: '0 0 10px'
            },
            labelWidth: 120,
            minWidth: 250,
            fieldLabel: me.snippets.account
        });
        me.accountHolderField = Ext.create('Ext.form.field.Text', {
            name: 'paymentInstances[accountHolder]',
            alias: 'holder',
            readOnly: true,
            anchor: '95%',
            labelStyle: 'font-weight: 700;',
            style: {
                margin: '0 0 10px'
            },
            labelWidth: 120,
            minWidth: 250,
            fieldLabel: me.snippets.accountHolder
        });
        me.ibanField = Ext.create('Ext.form.field.Text', {
            name: 'paymentInstances[iban]',
            alias: 'sepaIban',
            readOnly: true,
            anchor: '95%',
            labelStyle: 'font-weight: 700;',
            style: {
                margin: '0 0 10px'
            },
            labelWidth: 120,
            minWidth: 250,
            fieldLabel: me.snippets.sepaIban
        });

        return [ me.accountNumberField, me.accountHolderField, me.ibanField ];
    },

    createRightElements: function () {
        var me = this;

        me.bankNameField = Ext.create('Ext.form.field.Text', {
            name: 'paymentInstances[bankName]',
            alias: 'bankName',
            readOnly: true,
            anchor: '95%',
            labelWidth: 120,
            minWidth: 250,
            labelStyle: 'font-weight: 700;',
            style: {
                margin: '0 0 10px'
            },
            xtype: 'textfield',
            fieldLabel: me.snippets.bankName
        });
        me.bankCodeField = Ext.create('Ext.form.field.Text', {
            name: 'paymentInstances[bankCode]',
            alias: 'bankCode',
            readOnly: true,
            anchor: '95%',
            labelWidth: 120,
            minWidth: 250,
            labelStyle: 'font-weight: 700;',
            style: {
                margin: '0 0 10px'
            },
            xtype: 'textfield',
            fieldLabel: me.snippets.bankCode
        });
        me.bicField = Ext.create('Ext.form.field.Text', {
            name: 'paymentInstances[bic]',
            alias: 'sepaBic',
            readOnly: true,
            anchor: '95%',
            labelWidth: 120,
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


