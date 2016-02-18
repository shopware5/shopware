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
 * @package    CanceledOrder
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/canceled_order/view/main}

/**
 * Shopware UI - Detail view for the order tab, allows the user to send mails and vouchers
 */
//{block name="backend/canceled_order/view/tabs/order/detail"}
Ext.define('Shopware.apps.CanceledOrder.view.tabs.order.Detail', {
    extend: 'Ext.panel.Panel',
    collapsed: true,
    collapsible: true,
    title: '{s name=customerFeedback}Customer Feedback{/s}',
    region: 'east',
    width: 300,
    alias: 'widget.canceled-order-view-order-detail',

    /**
     * Init the main detail component, add components
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [ me.createContainer() ];
        me.addEvents( 'askForReason' );
        me.callParent(arguments);
    },

    /**
     * Creates the main container, sets layout and adds the components needed
     * @return Ext.container.Container
     */
    createContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            border: false,
            padding: 10,
            layout: {
                type: 'vbox',
                align : 'stretch',
                pack  : 'start'
            },
            items: [
                me.createInfoText(),
                me.createVoucherCombo(),
                me.createAskReasonButton()
            ]
        });
    },

    /**
     * Creates and returns a simple label which will later inform the user about the possible options (voucher / mail)
     * @return Ext.form.Label
     */
    createInfoText: function()  {
        var me = this;

        me.infoLabel = Ext.create('Ext.form.Label', {
            text: '{s name=detailsNoOrderSelected}No order selected{/s}',
            padding: '0 0 20 0'
        });
        return me.infoLabel;
    },

    /**
     * Creates and returns a button used to send 'ask for reason' mails
     * @return Ext.button.Button
     */
    createAskReasonButton: function() {
        var me = this;

        me.askReasonButton = Ext.create('Ext.button.Button', {
            text: '{s name=askForReason}Ask for reason{/s}',
            hidden: true,
            handler: function () {
                me.fireEvent('askForReason');
            }
                });
        return me.askReasonButton;
    },

    /**
     * Creates and returns the voucher combo. It will contain the available vouchers
     * @return Ext.form.ComboBox
     */
    createVoucherCombo: function() {
        var me = this;

        me.voucherCombo = Ext.create('Ext.form.ComboBox', {
            store: me.canceledOrderVoucher,
            fieldLabel: '{s name=sendVoucher}Send Voucher{/s}',
            displayField: 'value',
            valueField: 'id',
            emptyText: '{s name=selectVoucher}Select Voucher{/s}',
            editable: false,
            hidden: true,
            listeners: {
                select: function(combo, record) {
                    me.fireEvent('sendVoucher', combo, record);
                }
            }

        });

        return me.voucherCombo;
    }
});
//{/block}
