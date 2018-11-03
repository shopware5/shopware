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
            success: Ext.bind(me.onMenuLoaded, me)
        });

        me.callParent(arguments);
        me.items.add(Ext.create('Shopware.Search'));

        // Add event listener which sets the width of the toolbar to the viewport width
        Ext.EventManager.onWindowResize(function(width) {
            me.setWidth(width);
        });

        // Hides the menu's when the user enters the frame of a simplied module
        Shopware.app.Application.on('global-close-menu', function() {
            Ext.menu.Manager.hideAll();
        });

        Shopware.app.Application.on('reload-main-menu', function() {
            Ext.Ajax.request({
                url: '{url action=menu}',
                scope: me,
                success: function(response) {
                    me.removeAll();
                    me.add(Ext.create('Shopware.Search'));
                    me.insert(0, Ext.decode(response.responseText));
                    me.add({ xtype: 'tbfill' }, {
                        xtype: 'container',
                        cls  : 'x-main-logo-container',
                        width: 23,
                        height: 17
                    });
                }
            });
        });
    },

    onMenuLoaded: function(response) {
        var me = this;

        me.insert(0, Ext.decode(response.responseText));
        me.fireEvent('menu-created', me.items);

        /*{if {acl_is_allowed privilege=read resource=pluginmanager}}*/
        Ext.create('Shopware.notification.SubscriptionWarning').check();
        /*{/if}*/
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
