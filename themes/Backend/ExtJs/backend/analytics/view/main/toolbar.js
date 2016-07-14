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
 * Analytics Main Toolbar Class
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/main/toolbar"}
Ext.define('Shopware.apps.Analytics.view.main.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.analytics-toolbar',
    ui: 'shopware-ui',

    initComponent: function () {
        var me = this,
            today = new Date();

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'combobox',
                    iconCls: 'sprite-gear',
                    name: 'shop_selection',
                    queryMode: 'remote',
                    fieldLabel: '{s name=toolbar/shop_name}Shop{/s}',
                    store: me.shopStore,
                    multiSelect: true,
                    displayField: 'name',
                    valueField: 'id'
                },
                {
                    xtype: 'datefield',
                    fieldLabel: '{s name=toolbar/from_date}From{/s}',
                    labelWidth: 50,
                    width: 150,
                    name: 'from_date',
                    value: new Date(today.getFullYear(), today.getMonth() - 1, today.getDate()),
                    maxValue: today
                },
                {
                    xtype: 'datefield',
                    fieldLabel: '{s name=toolbar/to_date}To{/s}',
                    name: 'to_date',
                    width: 130,
                    labelWidth: 30,
                    value: today,
                    maxValue: today
                },
                { xtype: 'tbspacer' },
                {
                    xtype: 'button',
                    iconCls: 'sprite-arrow-circle-135',
                    text: '{s name=toolbar/update}Update{/s}',
                    name: 'refresh',
                    handler: function () {
                        me.fireEvent('refreshView')
                    }
                },
                {
                    xtype: 'button',
                    text: '{s name=toolbar/export}Export{/s}',
                    name: 'export',
                    iconCls: 'sprite-drive-download',
                    handler: function () {
                        me.fireEvent('exportCSV');
                    }
                },
                { xtype: 'tbfill' },
                {
                    showText: true,
                    xtype: 'cycle',
                    prependText: '{s name=toolbar/view}Display as{/s} ',
                    action: 'layout',
                    menu: {
                        items: [
                            {
                                text: '{s name=toolbar/view_chart}Chart{/s}',
                                layout: 'chart',
                                iconCls: 'sprite-chart'
                            },
                            {
                                text: '{s name=toolbar/view_table}Table{/s}',
                                layout: 'table',
                                iconCls: 'sprite-table',
                                checked: true
                            }
                        ]
                    }
                }
            ]
        });

        me.callParent(arguments);
    }
});
//{/block}
