/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Shopware_Paypal
 * @subpackage Paypal
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

//{namespace name=backend/payment_paypal/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/payment_paypal/view/main/action"}
Ext.define('Shopware.apps.PaymentPaypal.view.main.Action', {
    extend: 'Ext.window.Window',
    alias: 'widget.paypal-main-action',

    layout: 'fit',
    width: 400,
    height: 330,

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            title: '{s name=action/title}Payment action: {/s}' + me.paymentActionName,
            items: me.getItems(),
            buttons: me.getButtons()
        });

        me.callParent(arguments);

        me.show();
        me.down('form').getForm().setValues(me.detailData);
    },

    getItems: function() {
        var me = this, action = me.paymentAction;
        return [{
            xtype: 'form',
            layout: 'anchor',
            bodyPadding: 10,
            border: 0,
            autoScroll: true,
            defaults: {
                anchor: '100%',
                labelWidth: 160,
                xtype: 'textfield'
            },
            items: [{
                xtype: 'hidden',
                name: 'paymentAction',
                value: action
            }, {
                name: 'transactionId',
                readOnly: true,
                fieldLabel: '{s name=action/field/transaction_id}Transaction ID{/s}'
            }, {
                name: 'orderNumber',
                readOnly: true,
                fieldLabel: '{s name=action/field/order_number}Order number{/s}'
            }, {
                name: 'paymentCurrency',
                readOnly: true,
                hidden: action == 'void',
                fieldLabel: '{s name=action/field/currency}Currency{/s}'
            }, {
                xtype: 'base-element-number',
                name: 'paymentAmount',
                hidden: action == 'void',
                fieldLabel: '{s name=action/field/amount}Book amount{/s}'
            }, {
                xtype: 'base-element-boolean',
                name: 'paymentFull',
                hidden: action != 'refund',
                handler: function(btn, value) {
                    var field = me.down('[name=paymentAmount]');
                    field[value ? 'hide' : 'show']();
                },
                fieldLabel: '{s name=action/field/complete_amount}Complete amount{/s}'
            }, {
                xtype: 'base-element-boolean',
                name: 'paymentLast',
                hidden: action != 'capture' && action != 'book',
                fieldLabel: '{s name=action/field/last}Last capture{/s}'
            }, {
                name: 'note',
                xtype: 'textarea',
                hidden: action == 'auth',
                fieldLabel: '{s name=action/field/note}Note to the customer{/s}'
            }]
        }];
    },

    getButtons: function() {
        var me = this;
        return [{
            text: '{s name=action/abort_text}Abort{/s}',
            cls: 'secondary',
            handler: function(btn) {
                me.close();
            }
        }, {
            text: '{s name=action/execute_text}Execute{/s}',
            cls: 'primary',
            handler: function(btn) {
                var form = me.down('form');
                if(!form.getForm().isValid()) {
                    return;
                }
                me.close();
                Ext.MessageBox.wait('{s name=action/wait_message}Processing ...{/s}', '{s name=action/wait_title}Perform the payment action{/s}');
                form.getForm().submit({
                    url: '{url action=doAction}',
                    success: me.onActionSuccess,
                    failure: me.onActionFailure,
                    scope: this
                });
            }
        }];
    },

    onActionSuccess: function(form, action) {
        Ext.Msg.alert('{s name=action/success_title}Success{/s}', '{s name=action/success_message}The payment action completed successfully.{/s}');
    },

    onActionFailure: function(form, action) {
        switch (action.failureType) {
            case Ext.form.action.Action.CLIENT_INVALID:
                Ext.Msg.alert('Error', 'Form fields may not be submitted with invalid values');
                break;
            case Ext.form.action.Action.CONNECT_FAILURE:
                Ext.Msg.alert('Error', 'Ajax communication failed');
                break;
            default:
            case Ext.form.action.Action.SERVER_INVALID:
                Ext.Msg.alert('Failure', action.result.message);
                break;
        }
    }
});
//{/block}