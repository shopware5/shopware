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
 * @package    Shipping
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/view/edit/payment_means}*/

/**
 * Shopware UI - Shipping Costs
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/view/edit/payment_means"}
Ext.define('Shopware.apps.Shipping.view.edit.PaymentMeans', {
    /**
     * Based on Ext.panel.Panel
     */
    extend:'Ext.container.Container',
    /**
     * Alias for easy creation
     */
    alias:'widget.shipping-view-edit-payment-means',

    /**
     * Name of this view
     */
    name:'shipping-view-edit-payment-means',
    /**
     * Title as shown in the tab from the panel
     */
    title:'{s name=means_of_payment}Means of Payment{/s}',
    /**
     * Display the the contents of this tab immediately
     */
    autoShow:true,
    /**
     * Use the full height
     */
    height:'100%',
    /**
     * No borders
     */
    border:0,
    /**
     * Uses the column layout
     */
    layout:{
        type:'column',
        align:'stretch',
        padding:5
    },
    /**
     * Defaults
     */
    defaults:{
        columnWidth:1
    },
    /**
     * Stores the data dragged into the right hand side grid and the data already assigned to this dispatch
     */
    usedPaymentStore:null,
    /**
     * Contains all known means of payment
     */
    availablePayments :null,

    /**
     * Initialize the controller and defines the necessary default configuration
     */
    initComponent : function() {
        var me = this,
            ids = [];

        me.usedPaymentStore = me.record.getPayments();
        // Build a list of ids to filter them out of the available payments store - this is done
        // through php
        me.usedPaymentStore.each(function(element) {
            ids.push(element.get('id'));
        });

        me.availablePayments.filters.clear();
        me.availablePayments.filter('usedIds', ids);

        // Create the view
        me.items = me.getItems();
        me.callParent(arguments);
    },
    /**
     * Returns all needed items to the parent container
     *
     * @return Array
     */
    getItems:function () {
        var me = this;
        return [
            {
                xtype:'ddselector',
                fromStore:me.availablePayments,
                buttons:['add', 'remove'],
                gridHeight:200,
                fromColumns :[{
                    text: 'name',
                    flex: 1,
                    dataIndex: 'description'
                }],
                toColumns :[{
                    text: 'name',
                    flex: 1,
                    dataIndex: 'description'
                }],
                /**
                 * FromTitle which holds Title on the Left Side
                 *
                 * @string
                 */
                fromTitle:'{s name=tab/paymentmeans/from_title}Available{/s}',

                /**
                 * toTitle which holds Title on the Right Side
                 *
                 * @string
                 */
                toTitle:'{s name=tab/paymentmeans/to_title}Selected{/s}',
                dataIndex:'description',
                selectedItems:me.usedPaymentStore,
                fromFieldDockedItems:[]
            }
        ];
    }
});
//{/block}
