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
//{block name="backend/analytics/view/table/referrer_revenue"}
Ext.define('Shopware.apps.Analytics.view.table.Customers', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-customers',
    shopColumnName: 'Anteil Neu-/Stammkunden',

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
            dataIndex: 'week',
            text: 'Kalenderwoche'
        }, {
            dataIndex: 'amountNewCustomers',
            text: 'Neukunden'
        }, {
            dataIndex: 'amountOldCustomers',
            text: 'Stammkunden'
        }, {
            dataIndex: 'maleAmount',
            text: 'Männl. Anteil'
        }, {
            dataIndex: 'femaleAmount',
            text: 'Weibl. Anteil'
        }];
    }
});
//{/block}