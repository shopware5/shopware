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
 * @package    Customer
 * @subpackage Chart
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/chart/meta_chart"}
Ext.define('Shopware.apps.Customer.view.chart.MetaChart', {

    extend: 'Shopware.apps.Customer.view.chart.Chart',

    initComponent: function () {
        var me = this;
        me.store = Ext.create('Shopware.apps.Customer.store.MetaChart');
        me.callParent(arguments);
    },

    getFields: function () {
        return [
            { name: 'count_orders', title: '{s name=window/number_of_orders}Numer of orders{/s}' },
            { name: 'invoice_amount_avg', title: '{s name=window/order_avg}Ø Cart{/s}' },
            { name: 'invoice_amount_max', title: '{s name=window/max_order}Most expensive order{/s}' },
            { name: 'invoice_amount_min', title: '{s name=window/min_order}Least expensive order{/s}' },
            { name: 'invoice_amount_sum', title: '{s name=window/total_revenue}Total revenue{/s}' },
            { name: 'product_avg', title: '{s name=window/merchandise_value}Ø Merchandise value{/s}' }
        ];
    }
});
// {/block}
