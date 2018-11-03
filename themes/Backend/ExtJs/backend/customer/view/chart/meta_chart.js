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

        me.store = Ext.create('Ext.data.Store', {
            fields: [
                { name: 'count_orders', type: 'int' },
                { name: 'invoice_amount_max', type: 'float' },
                { name: 'invoice_amount_min', type: 'float' },
                { name: 'product_avg', type: 'float' },
                { name: 'yearMonth', type: 'string' }
            ],
            proxy: {
                type: 'ajax',
                url: '{url controller="CustomerStream" action="loadChart"}',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        });

        me.callParent(arguments);
    },

    getFields: function () {
        return [
            { name: 'count_orders',       title: '{s name=count_orders}{/s}', currency: false },
            { name: 'invoice_amount_max', title: '{s name=max_order}{/s}', currency: true },
            { name: 'invoice_amount_min', title: '{s name=min_order}{/s}', currency: true },
            { name: 'product_avg',        title: '{s name=average_product_amount}{/s}', currency: true }
        ];
    }
});
// {/block}
