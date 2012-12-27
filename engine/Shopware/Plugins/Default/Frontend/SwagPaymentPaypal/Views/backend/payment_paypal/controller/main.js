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

/**
 * Shopware Controller - Config backend module
 *
 * todo@all: Documentation
 */
//{block name="backend/payment_paypal/controller/main"}
Ext.define('Shopware.apps.PaymentPaypal.controller.Main', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'window', selector: 'paypal-main-window' },
        { ref: 'detail', selector: 'paypal-main-detail' },
        { ref: 'list', selector: 'paypal-main-list' },
        { ref: 'balance', selector: 'paypal-main-list field[name=balance]' },
        { ref: 'transactions', selector: 'paypal-main-detail grid' }
    ],

    stores: [
        'main.List', 'main.Balance', 'main.Detail'
    ],
    models: [
        'main.List', 'main.Balance', 'main.Detail', 'main.Transaction'
    ],
    views: [
        'main.Window', 'main.List', 'main.Detail', 'main.Action'
    ],

    /**
     * The main window instance
     * @object
     */
    mainWindow: null,

    init: function () {
        var me = this;

        // Init main window
        me.mainWindow = me.getView('main.Window').create({
            autoShow: true,
            scope: me
        });

        me.detailStore = me.getStore('main.Detail');

        me.getStore('main.Balance').load({
            callback: me.onLoadBalance,
            scope: this
        });

        // Register events
        me.control({
            'paypal-main-list': {
                selectionchange: me.onSelectionChange
            },
            'paypal-main-detail button': {
                click: me.onClickDetailButton
            },
            'paypal-main-list [name=searchfield]': {
                change: me.onSearchForm
            }
        });
    },

    onSearchForm: function(field, value) {
        var me = this;
        var store = me.getStore('main.List');
        if (value.length === 0 ) {
            store.load();
        } else {
            store.load({
                filters : [{
                    property: 'search',
                    value: '%' + value + '%'
                }]
            });
        }
    },

    onClickDetailButton: function(button) {
        var me = this,
            detail = me.getDetail(),
            detailData = detail.getForm().getFieldValues(),
            action;

        action = me.getView('main.Action').create({
            paymentAction: button.action,
            paymentActionName: button.text,
            detailData: detailData
        });

        action.on('destroy', function() {
            me.getList().getStore().load();
        })
    },

    onSelectionChange: function(table, records) {
        var me = this,
            formPanel = me.getDetail(),
            record = records.length ? records[0] : null;
        if(record) {
            formPanel.setLoading(true);
            formPanel.loadRecord(record);
            me.detailStore.load({
                filters : [{
                    property: 'transactionId',
                    value: record.get('transactionId')
                }],
                callback: me.onLoadDetail,
                scope: me
            });
            formPanel.enable();
        } else {
            formPanel.disable();
        }
    },

    onLoadBalance: function(records) {
        var me = this;
        if(records.length) {
            var record = records[0];
            me.getBalance().setValue(record.get('balanceFormat'));
        }
    },

    onLoadDetail: function(records) {
        var me = this,
            formPanel = me.getDetail(),
            detail = records.length ? records[0] : null,
            status, fields;
        if(!detail) {
            return;
        }
        formPanel.loadRecord(detail);
        me.getTransactions().reconfigure(
            detail.getTransactions()
        );
        status = detail.get('paymentStatus');
        pending = detail.get('pendingReason');
        fields = formPanel.query('button');
        Ext.each(fields, function(field) {
            field.hide();
        });
        switch(status) {
            case 'Expired':
                formPanel.down('[action=auth]').show();
                break;
            case 'Completed':
            case 'PartiallyRefunded':
            case 'Canceled-Reversal ':
                formPanel.down('[action=refund]').show();
                break;
            case 'Pending':
                if(pending == 'order') {
                    formPanel.down('[action=book]').show();
                } else {
                    formPanel.down('[action=capture]').show();
                }
                formPanel.down('[action=void]').show();
                break;
        }
        formPanel.setLoading(false);
    }
});
//{/block}
