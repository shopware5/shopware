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
//{block name="backend/analytics/view/table/visitors"}
Ext.define('Shopware.apps.Analytics.view.table.Visitors', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-visitors',
    shopColumnVisits: "{s name=table/visits/visits}V: [0]{/s}",
    shopColumnImpressions: "{s name=table/visits/impressions}I: [0]{/s}",
    initComponent: function() {
           var me = this;

           me.columns = [{
                   xtype: 'datecolumn',
                   dataIndex: 'datum',
                   text: '{s name=table/visitors/date}Date{/s}',
                   width: 80
               },
               {
                   xtype: 'gridcolumn',
                   dataIndex: 'totalVisits',
                   text: '{s name=table/visitors/totalVisits}Total visits{/s}',
                   align: 'right',
                   width: 80
               },
               {
                   xtype: 'gridcolumn',
                   dataIndex: 'totalImpressions',
                   text: '{s name=table/visitors/totalImpressions}Total impressions{/s}',
                   align: 'right',
                   width: 80
               }
           ];
           me.shopStore.each(function(shop) {

               me.columns[me.columns.length] = {
                   xtype: 'gridcolumn',
                   dataIndex: 'visits' + shop.data.id,
                   text: Ext.String.format(me.shopColumnVisits, shop.data.name),
                   align: 'right',
                   width: 80,
                   sortable: false
               };
               me.columns[me.columns.length] = {
                 xtype: 'gridcolumn',
                 dataIndex: 'impressions' + shop.data.id,
                 text: Ext.String.format(me.shopColumnImpressions, shop.data.name),
                 align: 'right',
                 width: 80,
                 sortable: false
               };
           }, me);

           me.callParent(arguments);
       }

});
//{/block}