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
 * Shopware UI - Grid for Subshop-Selection
 *
 * todo@all: Documentation
 *
 */
//{block name="backend/payment/view/payment/subshoplist"}
Ext.define('Shopware.apps.Payment.view.payment.SubshopList', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.grid.Panel',

    ui: 'shopware-ui',

    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('payment-main-subshoplist')
    * @string
    */
    alias: 'widget.payment-main-subshoplist',
    /**
    * The window uses a border layout, so we need to set
    * a region for the grid panel
    * @string
    */
    region: 'center',

    border: 0,
    /**
    * The view needs to be scrollable
    * @string
    */
    autoScroll: true,

    overflowX: 'hidden',

    /**
     * This function is called, when the component is initiated
     * It creates the columns and the selection-model for the grid and sets the store
     */
    initComponent: function(){
        var me = this;
        me.columns = me.getColumns();
        me.store = Ext.create('Shopware.apps.Base.store.Shop');
        me.store.clearFilter();
        me.selModel = me.getGridSelModel();
        me.callParent(arguments);
    },

    /**
     * This function creates the columns of the grid
     * @return Array
     */
    getColumns: function(){
        var columns =  [{
            header: '{s name=column_subShopSelection_name}Name{/s}',
            flex: 1,
            dataIndex: 'name'
        }];
        return columns;
    },

    /**
     * This function creates the selection-model for the grid
     * @return Ext.selection.CheckboxModel
     */
    getGridSelModel: function(){
        return Ext.create('Ext.selection.CheckboxModel');
    }
});
//{/block}
