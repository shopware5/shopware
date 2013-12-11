/**
 * Shopware 4
 * Copyright © shopware AG
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
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/view/account/confirm"}
Ext.define('Shopware.apps.PluginManager.view.account.Confirm', {
    extend: 'Enlight.app.Window',
    alias: 'widget.plugin-manager-account-confirm',
    border: 0,
    autoShow: true,
    maximizable: false,
    minimizable: false,
    cls: Ext.baseCSSPrefix + 'plugin-manager-account-confirm',
    title: '{s name=account/confirm/title}Plugin manager - check order{/s}',

	snippets:{
		abort_buy: '{s name=account/confirm/abort_buy}Abort purchase{/s}',
		order_pay: '{s name=account/confirm/order_pay}Order now{/s}',
		order_number: '{s name=account/confirm/order_number}Order number{/s}',
		amount: '{s name=account/confirm/amount}Amount{/s}',
		price: '{s name=account/confirm/price}Price{/s}',
		full_price: '{s name=account/confirm/full_price}* All prices incl. VAT{/s}'
	},

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('confirmbuy');

        me.width = 450;
        me.height = 300;
        me.view = me.createView();
        me.items = [ me.view ];

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items: ['->', {
                text: me.snippets.abort_buy,
                cls: 'secondary',
                handler: function() {
                    me.destroy();
                }
            },
        /*{if {acl_is_allowed privilege=install}}*/{
                text: me.snippets.order_pay,
                cls: 'primary',
                handler: function() {
                    me.fireEvent('confirmbuy', me.record, me.detail);
                    me.destroy();
                }
            }
        /*{/if}*/]
        }];

        me.callParent(arguments);
    },

    createView: function() {
        var me = this;

        return Ext.create('Ext.view.View', {
            tpl: me.createViewTemplate(),
            data: [{
                name: me.record.get('name'),
                description: Ext.String.ellipsis(Ext.util.Format.stripTags(me.record.get('description')), 180),
                price: me.price,
                ordernumber: me.detail.get('ordernumber'),
                amount: 1
            }]
        });
    },

    createViewTemplate: function() {
		var me = this;

        return new Ext.XTemplate(
            '{literal}<tpl for="."><div class="outer-container">',
                '<h3>{name}</h3>',
                '<div class="detail">',
                    '<div class="ordernumber">'+me.snippets.order_number+': {ordernumber}</div>',
                    '<div class="amount">'+me.snippets.amount+': {amount}x</div>',

                    '<p>{description}</p>',
                '</div>',
                '<div class="price">',
                    '<p class="price-display">',
                        '<strong>'+me.snippets.price+':</strong>{price}&nbsp;&euro;*',
                        '<span class="tax-notice">'+me.snippets.full_price+'</span>',
                    '</p>',
                '</div>',
            '</div></tpl>{/literal}'
        );
    }
});
//{/block}
