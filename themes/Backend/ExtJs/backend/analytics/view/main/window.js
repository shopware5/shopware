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
 * Analytics Main Window Class
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/main/window"}
Ext.define('Shopware.apps.Analytics.view.main.Window', {
    extend: 'Enlight.app.Window',
    cls: Ext.baseCSSPrefix + 'analytics',
    layout: 'border',
    title: '{s name=title}Statistics{/s}',
    width: '90%',
    height: '90%',
    stateId: 'shopware-statistics-main-window',

    initComponent: function () {
        var me = this;

        me.items = [
            {
                xtype: 'analytics-panel',
                region: 'center',
                shopStore: me.shopStore
            },
            {
                xtype: 'analytics-navigation',
                region: 'west',
                collapsible: true,
                store: me.navigationStore
            }
        ];

        me.callParent(arguments);
    }
});
//{/block}
