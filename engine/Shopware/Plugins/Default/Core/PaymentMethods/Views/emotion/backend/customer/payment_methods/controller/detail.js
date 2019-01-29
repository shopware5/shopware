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
 * @subpackage Model
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Payment Methods plugin - Customer backend module.
 */

// {block name="backend/customer/controller/detail" append}
Ext.define('Shopware.apps.Customer.PaymentMethods.controller.Detail', {
    override: 'Shopware.apps.Customer.controller.Detail',

    /**
     * Event will be fired when the user change the payment combo box which
     * is displayed on bottom of the detail page.
     *
     * @param [object] value     - the new value of the combo box
     * @param [object] container - The field container which contains the debit account fields
     * @return void
     */
    onChangePayment: function (value, container) {
        var me = this;
        var window = me.getDetailWindow();
        var store = window.paymentStore;
        var record = store.getById(value);
        var paymentFieldSet = window.down('customer-debit-field-set');

        switch (record.get('name').toLowerCase()) {
            case 'sepa':
                paymentFieldSet.fieldContainer.show();
                if (paymentFieldSet.fieldContainer.getEl()) {
                    paymentFieldSet.fieldContainer.getEl().fadeIn({
                        opacity: 1,
                        easing: 'easeOut',
                        duration: 500
                    });
                }
                paymentFieldSet.accountNumberField.hide().allowBlank = true;
                paymentFieldSet.accountHolderField.hide().allowBlank = true;
                paymentFieldSet.bankCodeField.hide().allowBlank = true;
                paymentFieldSet.bankNameField.allowBlank = true;
                paymentFieldSet.useBillingDataField.show();
                paymentFieldSet.ibanField.show();
                paymentFieldSet.bicField.show();
                break;
            case 'debit':
                paymentFieldSet.fieldContainer.show();
                if (paymentFieldSet.fieldContainer.getEl()) {
                    paymentFieldSet.fieldContainer.getEl().fadeIn({
                        opacity: 1,
                        easing: 'easeOut',
                        duration: 500
                    });
                }
                paymentFieldSet.fieldContainer.getEl().show().allowBlank = false;
                paymentFieldSet.accountNumberField.show().allowBlank = false;
                paymentFieldSet.accountHolderField.show().allowBlank = false;
                paymentFieldSet.bankCodeField.show().allowBlank = false;
                paymentFieldSet.bankNameField.allowBlank = false;
                paymentFieldSet.useBillingDataField.hide().allowBlank = true;
                paymentFieldSet.ibanField.hide().allowBlank = true;
                paymentFieldSet.bicField.hide().allowBlank = true;
                break;
            default:
                me.callParent(arguments);
        }
    },

    onSaveCustomer: function (btn) {
        var me = this;
        var window = me.getDetailWindow();
        var form = window.down('form');
        var record = form.getRecord();
        var values = form.getValues();
        var store = window.paymentStore;
        var paymentMean = store.getById(values['paymentId']);
        var paymentData = record.getPaymentData().first();

        if (form.getForm().isValid() && paymentData) {
            switch (paymentMean.get('name')) {
                case 'sepa':
                    values['paymentData[accountHolder]'] = '';
                    values['paymentData[accountNumber]'] = '';
                    values['paymentData[bankCode]'] = '';
                    break;
                case 'debit':
                    values['paymentData[bic]'] = '';
                    values['paymentData[iban]'] = '';
                    values['paymentData[useBillingData]'] = false;
                    break;
                default:
                    values['paymentData[accountHolder]'] = '';
                    values['paymentData[accountNumber]'] = '';
                    values['paymentData[bankCode]'] = '';
                    values['paymentData[bankName]'] = '';
                    values['paymentData[bic]'] = '';
                    values['paymentData[iban]'] = '';
                    values['paymentData[useBillingData]'] = false;
                    record['getPaymentDataStore'] = Ext.create('Ext.data.Store', {
                        model: 'Shopware.apps.Customer.model.PaymentData'
                    });

                    break;
            }

            form.getForm().setValues(values);
        }
        me.callParent(arguments);
    }
});
//

// {/block}
