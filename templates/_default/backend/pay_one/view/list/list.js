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
 * @package    Order
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order list backend module
 * The order list view displays the data of the list store.
 * One row displays the head data of a order.
 */
//{block name="backend/order/view/list/list"}
Ext.define('Shopware.apps.PayOne.view.list.List-Override', {

    override: 'Shopware.apps.Order.view.list.List',

    createActionColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Action', {
            width:90,
            items:[
                me.createPayOneCaptureColumn(),
                me.createPayOneRefundColumn()
            ]
        });
    },

    createPayOneCaptureColumn: function() {
        var me = this;

        return {
            iconCls:'sprite-money--plus',
            action:'payoneCapture',
            tooltip:me.snippets.columns.creditAmount,
            /**
             * Add button handler to fire the deleteOrder event which is handled
             * in the list controller.
             */
            handler:function (view, rowIndex, colIndex, item) {
                var store = view.getStore(),
                    record = store.getAt(rowIndex);

                var id = record.get('id');
                var name = record.get('number') + " " + record.get('customerId');
                var ordernumber = record.get('number');
                var customer = record.get('customerId');
                var orderAmount = record.get('invoiceAmount');

                me.captureAmount(id, ordernumber, customer, orderAmount);
            }
        };
    },

    createPayOneRefundColumn: function() {
        var me = this;
        return {
            iconCls:'sprite-money--minus',
            action:'payoneRefund',
            tooltip:me.snippets.columns.drawAmount,
            /**
             * Add button handler to fire the showDetail event which is handled
             * in the list controller.
             */
            handler:function (view, rowIndex, colIndex, item) {
                var store = view.getStore(),
                        record = store.getAt(rowIndex);

                var id = record.get('id');
                var name = record.get('number') + " " + record.get('customerId');
                var ordernumber = record.get('number');
                var customer = record.get('customerId');
                var orderAmount = record.get('invoiceAmount');
                me.refundAmount(id, ordernumber, customer);
            }
        };
    },


    captureAmount : function(id, ordernumber, customer, amount){
        var title = 'Betrag';
        var message = 'Welcher Betrag soll eingezogen werden?';


        Ext.MessageBox.prompt(title, message, function(btn, inputAmount){
            if(btn == "ok") {
                Ext.Ajax.request({
                    // url: '../../../../shopware.php/sViewport,BuiswPaymentPayone/sAction,captureAmount',
//                    url: '../../../../backend/BuiswPaymentPayone/captureAmount',
                    url: '{url controller="BuiswPaymentPayone" action=captureAmount}',
                    params:{
                        oID:ordernumber,
                        amount:inputAmount,
                        action: 'capture'
                    },
                    success: function(response){
                        var result = Ext.JSON.decode(response.responseText), me = this;

                        if(result.error) {
                            alert(result.error);
                        } else {
                            message = 'Der Betrag wurde erfolgreich eingezogen!';
                            alert(message);
                        }
                    },
                    failure: function(){
                        message = "FEHLER! Verbindung fehlgeschlagen!";
                        alert(message);
                    }
                });
            }
        }, '', '', amount);
    },


    refundAmount : function(id, ordernumber, customer){
        var title = 'Betrag';
        var message = 'Welcher Betrag soll gutgeschrieben werden?';

        Ext.MessageBox.prompt(title, message, function(btn, inputAmount){
            if(btn == "ok") {
                Ext.Ajax.request({
                    // url: '../../../../shopware.php/sViewport,BuiswPaymentPayone/sAction,refundAmount',
//                    url: '../../../../backend/BuiswPaymentPayone/refundAmount',
                    url: '{url controller="BuiswPaymentPayone" action="refundAmount"}',
                    params:{
                        oID:ordernumber,
                        amount:inputAmount,
                        action: 'refund'
                    },
                    success: function(response){
                        var result = Ext.JSON.decode(response.responseText),
                            me = this;

                        if(result.error) {
                            alert(result.error);
                        } else {
                            message = 'Der Betrag wurde erfolgreich eingezogen!';
                            alert(message);
                        }
                    },
                    failure: function(){
                        message = "FEHLER! Verindung fehlgeschlagen!";
                        alert(message);
                    }
                });
            }
        });
    },




});
//{/block}

