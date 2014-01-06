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
 * @package    Analytics
 * @subpackage Visitors
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/conversion"}
Ext.define('Shopware.apps.Analytics.view.table.Conversion', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-conversion',
    shopColumnConversion: "{s name=table/conversion/shop}Conversion: [0]{/s}",
    initComponent: function() {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                align: 'right',
                flex: 1,
                sortable: false
            }
        };

        me.initStoreIndices('conversion', me.shopColumnConversion);

        me.callParent(arguments);
    },

    getColumns: function() {
        return [{
            xtype: 'datecolumn',
            dataIndex: 'date',
            text: '{s name=table/conversion/date}Date{/s}'
        }, {
            dataIndex: 'totalVisits',
            text: '{s name=table/conversion/totalVisits}Total visits{/s}'
        }, {
            dataIndex: 'totalOrders',
            text: '{s name=table/conversion/totalOrders}Total orders{/s}'
        }, {
            dataIndex: 'totalConversion',
            text: '{s name=table/conversion/conversion}Conversion{/s}'
        }];
    }

});
//{/block}