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
 * @package    Bundle
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Bundle price list
 */
//{namespace name="backend/bundle/bundle/view/main"}
//{block name="backend/bundle/view/list/prices"}
Ext.define('Shopware.apps.Bundle.view.list.Prices', {
    extend: 'Ext.grid.Panel',
    border: false,
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'bundle-price-list',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.bundle-price-list',

    bundle: null,

    /**
     * Called when the component will be initialed.
     */
    initComponent: function() {
        var me = this;
        me.columns = me.createColumns();
        me.callParent(arguments);
    },

    /**
     * Creates the grid columns.
     */
    createColumns: function() {
        var me = this;

        return [
            me.createCustomerGroupColumn(),
            me.createPriceColumn()
        ];
    },

    /**
     * Creates the customer group column for the price grid.
     * @return Object
     */
    createCustomerGroupColumn: function() {
        var me = this;

        return {
            header: '{s name=prices/customer_group_column}Customergroup name{/s}',
            flex: 1,
            dataIndex: 'customerGroup',
            renderer: me.customerGroupRenderer
        }
    },

    /**
     * Creates the price value column for the price grid.
     * @return Object
     */
    createPriceColumn: function() {
        var me = this, title;

        if (me.bundle && me.bundle.get('discountType') == 'abs') {
            title = '{s name=prices/price_column_buy}End price{/s}';
        } else {
            title = '{s name=prices/price_column_percentage}Percentage discount{/s}';
        }
        return {
            header: title,
            flex: 1,
            dataIndex: 'price',
            renderer: me.priceColumnRenderer
        }
    },

    /**
     * Column renderer function for the customer group column
     * @param value
     * @param metaData
     * @param record
     */
    customerGroupRenderer: function(value, metaData, record) {
        var customerGroup = null;

        if (record && record.getCustomerGroup() instanceof Ext.data.Store
                && record.getCustomerGroup().first() instanceof Ext.data.Model) {
            customerGroup = record.getCustomerGroup().first().get('name');
        }
        return customerGroup;
    },

    /**
     * Column renderer function for the price value column.
     * @param value
     * @param metaData
     * @param record
     */
    priceColumnRenderer: function(value, metaData, record) {
        var format = Ext.util.Format.number(value, '0.00') + '';
        return format.replace('.', ',');
    }

});

//{/block}
