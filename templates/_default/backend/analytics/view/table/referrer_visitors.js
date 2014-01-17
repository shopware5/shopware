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
Ext.define('Shopware.apps.Analytics.view.table.ReferrerVisitors', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-referrer_visitors',
    shopColumnName: 'Besucher Zugriffsquellen',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                flex:1
            }
        };

        me.addEvents(
                /**
                 * Fired when the magnifier icon in the action column was clicked
                 * Loads the referrer search terms to the table
                 */
                'viewSearchTerms',

                /**
                 * Fires when application icon in the action column was clicked
                 * Loads the referrer urls to the table
                 */
                'viewSearchUrl'
        );

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this;

        return [{
            dataIndex: 'count',
            text: 'Anzahl'
        }, {
            dataIndex: 'referrer',
            flex: 2,
            text: 'Referrer'
        }, {
            xtype: 'actioncolumn',
            text: 'Optionen',
            items: [{
                action: 'viewSearchTerms',
                iconCls: 'sprite-magnifier',
                tooltip:  'View search terms from this referrer',
                handler: function(grid, rowIndex, colIndex) {
                    me.fireEvent('viewSearchTerms', grid, rowIndex, colIndex);
                }
            }, {
                action: 'viewSearchUrl',
                iconCls: 'sprite-application',
                tooltip:  'View search links',
                handler: function(grid, rowIndex, colIndex) {
                    me.fireEvent('viewSearchUrl', grid, rowIndex, colIndex);
                }
            }]
        }];
    }
});
//{/block}