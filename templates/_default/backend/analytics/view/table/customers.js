/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 * @package    Analytics
 * @subpackage Overview
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/customers"}
Ext.define('Shopware.apps.Analytics.view.table.Customers', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-customers',
    shopColumnName: '{s name="nav/customers"}Portion New-/RegularCustomer{/s}',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                align: 'right',
                flex: 1
            }
        };

        me.initStoreIndices('amountNewCustomers', '{s name="table/customers/new_customers"}New customers{/s}: [0]');
        me.initStoreIndices('amountOldCustomers', '{s name="table/customers/regular_customers"}Regular customers{/s}: [0]');
        me.initStoreIndices('maleAmount', '{s name="table/customers/male_portion"}Percentage male{/s}: [0]');
        me.initStoreIndices('femaleAmount', '{s name="table/customers/female_portion"}Percentage female{/s}: [0]');

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        return [
            {
                dataIndex: 'week',
                text: '{s name="table/customers/calendar_week"}Calendar Week{/s}'
            },
            {
                dataIndex: 'amountNewCustomers',
                text: '{s name="table/customers/new_customers"}New customers{/s}'
            },
            {
                dataIndex: 'amountOldCustomers',
                text: '{s name="table/customers/regular_customers"}Regular customers{/s}'
            },
            {
                dataIndex: 'maleAmount',
                text: '{s name="table/customers/male_portion"}Percentage male{/s}'
            },
            {
                dataIndex: 'femaleAmount',
                text: '{s name="table/customers/female_portion"}Percentage female{/s}'
            }
        ];
    }
});
//{/block}