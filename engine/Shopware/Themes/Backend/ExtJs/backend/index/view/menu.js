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

//{namespace name=backend/index/view/menu}

/**
 * Shopware Menu
 *
 * This component creates the main backend menu. The data for the items
 * array are placed in a global variable named "backendMenu".
 *
 * Note that this component are based on the Ext.toolbar.Toolbar instead
 * of Ext.menu.Menu.
 */
//{block name="backend/index/view/menu"}
Ext.define('Shopware.apps.Index.view.Menu', {
    extend:'Ext.toolbar.Toolbar',
    alias:'widget.mainmenu',
    alternateClassName:'Shopware.Menu',
    cls: 'shopware-menu',
    dock:'top',
    height:40,
    width: Ext.Element.getViewportWidth(),

    /**
     * Creates the menu and sets the component items
     */
    initComponent: function () {

        var me = this;

        Ext.Ajax.request({
            url: '{url action=menu}',
            async: false,
            success: function(response) {
                me.items = Ext.decode(response.responseText);
                me.fireEvent('menu-created', me.items);
                me.checkExpiredPlugin();
            }
        });

        me.callParent(arguments);
        me.items.add(Ext.create('Shopware.Search'));

        // Add event listener which sets the width of the toolbar to the viewport width
        Ext.EventManager.onWindowResize(function(width, height) {
            me.setWidth(width);
        });
    },

    /**
     * Check if any plugins are expired
     */
    checkExpiredPlugin: function() {
        var me = this;

        me.getExpiredPlugins(function(data) {
            var text = (Ext.Object.getSize(data) > 1) ? '{s name="licenses_expired_long"}{/s}:<br/>' : '{s name="license_expired_long"}{/s}:<br/>';

            Ext.each(data, function(data){
                var dateStr = Ext.util.Format.date(data.expireDate);
                var snippet = '{s name="license_expired_line_text"}{/s}<br/>';
                text += Ext.String.format(snippet, data.plugin, dateStr);
            });

            Shopware.Notification.createStickyGrowlMessage({
                title : (Ext.Object.getSize(data) > 1) ? '{s name="licenses_expired"}{/s}' : '{s name="license_expired"}{/s}',
                text  : text,
                width : 440,
                height: 300
            });
        });
    },

    getExpiredPlugins: function(callback) {
        Ext.Ajax.request({
            url: '{url controller="base" action="checkExpiredPlugin"}',
            async: false,
            success: function (response) {
                var responseData = Ext.decode(response.responseText);

                if (Ext.isEmpty(responseData.data)) {
                    return;
                }

                if (responseData.success == true) {
                    callback(responseData.data);
                }
            }
        });
    },

    afterRender: function() {
        var me = this;

        Shopware.app.Application.baseComponentIsReady(me);

        me.add({ xtype: 'tbfill' }, {
            xtype: 'container',
            cls  : 'x-main-logo-container',
            width: 23, height: 17
        });

        me.callParent(arguments);
    }
});
//{/block}
