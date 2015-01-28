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
 */

/**
 * Analytics ReferrerVisitors Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/referrer_visitors"}
Ext.define('Shopware.apps.Analytics.view.table.ReferrerVisitors', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-referrer_visitors',
    shopColumnName: '{s name=nav/visitor_source}Visitor access source{/s}',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                flex: 1,
                sortable: false
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

        return [
            {
                dataIndex: 'count',
                text: '{s name=table/referrer_visitors/count}Count{/s}'
            },
            {
                dataIndex: 'referrer',
                flex: 2,
                text: '{s name=table/referrer_visitors/referrer}Referrer{/s}'
            },
            {
                xtype: 'actioncolumn',
                text: '{s name=table/referrer_visitors/options}Optionen{/s}',
                items: [
                    {
                        action: 'viewSearchTerms',
                        iconCls: 'sprite-magnifier',
                        tooltip: '{s name=table/referrer_visitors/search_terms_tip}Display search terms of this referrer{/s}',
                        handler: function (grid, rowIndex, colIndex) {
                            me.fireEvent('viewSearchTerms', grid, rowIndex, colIndex);
                        }
                    },
                    {
                        action: 'viewSearchUrl',
                        iconCls: 'sprite-application',
                        tooltip: '{s name=table/referrer_visitors/search_links_tip}Display search links{/s}',
                        handler: function (grid, rowIndex, colIndex) {
                            me.fireEvent('viewSearchUrl', grid, rowIndex, colIndex);
                        }
                    }
                ]
            }
        ];
    }
});
//{/block}
