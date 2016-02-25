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
 * Shopware Payment Methods plugin - Order list backend module.
 */
//{block name="backend/order/controller/detail" append}
Ext.define('Shopware.apps.Order.PaymentMethods.controller.Detail', {
    override: 'Shopware.apps.Order.controller.Detail',

    onChangePayment: function (value, oldDebitContainer) {
        var me = this;
        var window = me.getDetailWindow();
        var store = window.paymentsStore;
        var record = store.getById(value);
        var paymentFieldSet = window.down('order-debit-field-set');

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
                paymentFieldSet.accountNumberField.hide();
                paymentFieldSet.accountHolderField.hide();
                paymentFieldSet.bankCodeField.hide();
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
                paymentFieldSet.fieldContainer.getEl().show();
                paymentFieldSet.accountNumberField.show();
                paymentFieldSet.accountHolderField.show();
                paymentFieldSet.bankCodeField.show();
                paymentFieldSet.ibanField.hide();
                paymentFieldSet.bicField.hide();
                break;
            default:
                me.callParent(arguments);
        }
    }
});

//{/block}
