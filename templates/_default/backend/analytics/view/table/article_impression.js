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
    shopColumnConversion: "{s name=table/article_impression/shop}Impressions: [0]{/s}",
    initComponent: function() {
           var me = this;

           me.columns = [{
                   xtype: 'datecolumn',
                   dataIndex: 'date',
                   text: '{s name=table/article_impression/date}Date{/s}',
                   width: 80,
                   sortable:false
               },
               {
                   xtype: 'gridcolumn',
                   dataIndex: 'articleName',
                   text: '{s name=table/article_impression/articleName}Article Name{/s}',
                   align: 'right',
                   flex: 1,
                   sortable:false
               },
               {
                   xtype: 'gridcolumn',
                   dataIndex: 'impressions',
                   text: '{s name=table/article_impression/impressions}Impressions{/s}',
                   align: 'right',
                   width: 80,
                   sortable:false
               }
           ];
           me.subApplication.shopStore.each(function(shop) {

               me.columns[me.columns.length] = {
                   xtype: 'gridcolumn',
                   dataIndex: 'conversion' + shop.data.id,
                   text: Ext.String.format(me.shopColumnConversion, shop.data.name),
                   align: 'right',
                   width: 120,
                   sortable: false
               };

           }, me);

           me.callParent(arguments);
       }

});
//{/block}