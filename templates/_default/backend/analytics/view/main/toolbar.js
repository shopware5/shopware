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
 * @subpackage Toolbar
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/main/toolbar"}
Ext.define('Shopware.apps.Analytics.view.main.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.analytics-toolbar',
    ui: 'shopware-ui',

    initComponent: function() {
        var me = this,
            today = new Date();

        Ext.applyIf(me, {
            items: [{
                xtype: 'combobox',
                iconCls: 'sprite-gear',
                name: 'shop_selection',
                queryMode: 'remote',
                fieldLabel: 'shop',
                store: me.shopStore,
                displayField: 'name',
                valueField: 'id'
            },
            {
                xtype: 'datefield',
                fieldLabel: 'From',
                labelWidth: 50,
                width:150,
                name: 'from_date',
                value: new Date(today.getFullYear(), today.getMonth() - 1, today.getDate()),
                maxValue: today
            },
            {
                xtype: 'datefield',
                fieldLabel: 'To',
                name: 'to_date',
                width:130,
                labelWidth: 30,
                value: today,
                maxValue: today
            },
            { xtype: 'tbspacer' },

            { xtype: 'tbfill' },
            {
                showText: true,
                xtype: 'cycle',
                prependText: '{s name=toolbar/view}Display as{/s} ',
                action: 'layout',
                menu: {
                    items: [{
                        text: '{s name=toolbar/view_chart}Chart{/s}',
                        layout: 'chart',
                        iconCls: 'sprite-chart'
                    },{
                        text: '{s name=toolbar/view_table}Table{/s}',
                        layout: 'table',
                        iconCls: 'sprite-table',
                        checked: true
                    }]
                }
            }]
        });

        me.callParent(arguments);
    }
});
//{/block}