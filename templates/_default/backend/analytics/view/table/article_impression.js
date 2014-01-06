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
 * @subpackage Visitors
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/article_impression"}
Ext.define('Shopware.apps.Analytics.view.table.ArticleImpression', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-article_impression',
    shopColumnName: "{s name=table/article_impression/shop}Impressions: [0]{/s}",
    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                align: 'right',
                flex:1
            }
        };

        me.initStoreIndices('amount', me.shopColumnName);

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
            text: '{s name=table/article_impression/date}Date{/s}'
        }, {
            xtype: 'actioncolumn',
            dataIndex: 'articleName',
            text: '{s name=table/article_impression/articleName}Article Name{/s}',
            renderer: function(val) {
                return val;
            },
            items: [{
                iconCls:'sprite-pencil',
                cls:'editBtn',
                tooltip:'{s name=table/article_impression/action_column/edit}Edit this Article{/s}',
                handler:function (view, rowIndex, colIndex, item, event, record) {
                    openNewModule('Shopware.apps.Article', {
                        action: 'detail',
                        params: {
                            articleId: record.get('articleId')
                        }
                    });
                }
            }]
        }, {
            dataIndex: 'totalAmount',
            text: '{s name=table/article_impression/impressions}Impressions{/s}'
        }];
    }
});
//{/block}