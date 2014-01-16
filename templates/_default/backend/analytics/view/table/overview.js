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
 * // todo@all add snippets
 *
 * @category   Shopware
 * @package    Analytics
 * @subpackage Overview
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/overview"}
Ext.define('Shopware.apps.Analytics.view.table.Overview', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-overview',
    shopColumnName: 'Overview',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                align:'right',
                flex:1
            }
        };

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        return [{
            xtype: 'datecolumn',
            dataIndex: 'date',
            text: 'Datum'
        }, {
            dataIndex: 'orderCount',
            text: 'Bestellungen'
        }, {
            dataIndex: 'totalConversion',
            text: 'Conversion Rate'
        }, {
            dataIndex: 'revenue',
            text: 'Umsatz'
        }, {
            dataIndex: 'cancelledOrders',
            text: 'Abgebr. Warenkörbe'
        }, {
            dataIndex: 'newCustomers',
            text: 'Neukunden'
        }, {
            dataIndex: 'visitors',
            text: 'Besucher'
        }, {
            dataIndex: 'clicks',
            text: 'Seitenaufrufe'
        }];
    }
});
//{/block}