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
 *
 * @category   Shopware
 * @package    PluginManager
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/plugin_manager/view/detail/window"}
Ext.define('Shopware.apps.PluginManager.view.detail.Window', {
    extend: 'Enlight.app.Window',

    cls: 'plugin-manager-window detail-window',
    alias: 'widget.plugin-manager-detail-window',

    height: '90%',
    minWidth: 995,
    autoScroll: true,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    initComponent: function() {
        var me = this;

        me.detailContainer = Ext.create('Shopware.apps.PluginManager.view.detail.Container');

        me.items = [ me.detailContainer ];

        me.callParent(arguments);

        me.on('afterrender', function() {
            //fix to prevent scrolling after tab change
            me.setHeight(me.getEl().dom.clientHeight + 1);
        });

    },

    setActivePriceTab: function(priceName) {
        var me = this;

        if (!me.detailContainer.pricesContainer) {
            return;
        }
        var tabIndex = me.detailContainer.pricesContainer.tabIndex[priceName];
        me.detailContainer.pricesContainer.navigationClick(tabIndex);
    },

    loadRecord: function(plugin) {
        var me = this;

        me.setTitle(plugin.get('label'));

        me.plugin = plugin;
        me.detailContainer.loadRecord(plugin);
    }
});
//{/block}