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
 * @package    Payment
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/payment/payment}

/**
 * Shopware UI - Tree to select the payment
 *
 * todo@all: Documentation
 *
 */
//{block name="backend/payment/view/payment/tree"}
Ext.define('Shopware.apps.Payment.view.payment.Tree', {
    extend : 'Ext.tree.Panel',
    alias : 'widget.payment-main-tree',
    autoShow : true,
    region: 'west',
    name:  'tree',
    flex: 0.3,
    rootVisible: false,
    useArrows: false,
    lines: false,
    title: '{s name=tree_panel_title}Available payments{/s}',
    store: 'Payments',

    /**
     * This function is called, when the component is initiated
     */
    initComponent: function(){
        var me = this;
        me.registerEvents();

        var buttons = [];
        /*{if {acl_is_allowed privilege=create}}*/
        buttons.push({
            xtype: 'tbspacer',
            width: 5
        });

        buttons.push(Ext.create('Ext.button.Button', {
            text: '{s name=button_new}New{/s}',
            iconCls: 'sprite-plus-circle',
            action: 'create',
            cls: 'small secondary',
            handler: function(){
                me.fireEvent('createPayment', this)
            }
        }));
        /*{/if}*/

        /*{if {acl_is_allowed privilege=delete}}*/
        buttons.push('->');
        buttons.push(Ext.create('Ext.button.Button', {
            text: '{s name=button_delete}Delete{/s}',
            iconCls: 'sprite-minus-circle',
            action: 'delete',
            name: 'delete',
            cls: 'small secondary',
            disabled: true,
            handler: function(){
                me.fireEvent('deletePayment', me, this)
            }
        }));
        buttons.push({
            xtype: 'tbspacer',
            width: 5
        });
        /*{/if}*/

        me.toolBar = Ext.create('Ext.toolbar.Toolbar', {
            name: 'treeToolBar',
            dock: 'bottom',
            items: buttons
        });

        me.dockedItems = me.toolBar;

        me.callParent(arguments);
    },

    /**
     * This function registers the special events
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * This event is fired, when the user presses the "new"-button
             * @param this Contains the button
             * @event createPayment
            */
            'createPayment',
            /**
             * This event is fired, when the user wants to delete a payment
             * @param me Contains the tree
             * @param this Contains the button
             * @event deletePayment
             */
            'deletePayment'
        );
    }
});
//{/block}
