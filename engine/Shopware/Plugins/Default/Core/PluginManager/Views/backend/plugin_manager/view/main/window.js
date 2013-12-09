/**
 * Shopware 4
 * Copyright © shopware AG
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
 * @package    Order
 * @subpackage View
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/view/main/window"}
Ext.define('Shopware.apps.PluginManager.view.main.Window', {
    /**
     * Define that the plugin manager main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'plugin-manager-main-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.plugin-manager-main-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'fit',
    /**
     * Define window width
     * @integer
     */
    width:1000,
    /**
     * Define window height
     * @integer
     */
    height:'90%',
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,
    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-plugin-manager-main-window',

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=window_title}Plugin Manager 2.0{/s}',

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.tabPanel = me.createTabPanel();
        me.items = me.tabPanel;
        me.callParent(arguments);
    },

    /**
     * Creates a tab panel which holds off the different sections
     * of the detail page.
     *
     * @public
     * @return [object] Ext.tab.Panel
     */
    createTabPanel: function() {
        var me = this;

        me.managerContainer = Ext.create('Ext.container.Container', {
            layout: 'card',
            autoScroll: true,
            region: 'center',
            items: [{
                xtype: 'plugin-manager-manager-grid',
                border: 0,
                pluginStore: me.pluginStore
            }]
        });

        /** {if $storeApiAvailable} */
        me.storeContainer = Ext.create('Ext.container.Container', {
            layout: 'card',
            autoScroll: true,
            region: 'center',
            name: 'store-card',
            items: [{
                xtype: 'plugin-manager-store-view',
                topSellerStore: me.topSellerStore,
                communityStore: me.communityStore,
                categoryStore: me.categoryStore
            }]
        });
        /** {/if} */

        return Ext.create('Ext.tab.Panel', {
            xtype: 'tabpanel',
            plain: true,
            name: 'main-tab',
            items: [{
                xtype: 'panel',
                layout: 'border',
                name: 'manager',
                initialTitle: 'manager',
                title: '{s name=tabs/manager}Extensions / Purchases{/s}',
                items: [{
                    xtype: 'plugin-manager-manager-navigation',
                    region: 'west',
                    width: 220,
                    updatesStore: me.updatesStore
                }, me.managerContainer ]
            },
            /** {if $storeApiAvailable} */
            {
                xtype: 'panel',
                layout: 'border',
                name: 'store',
                initialTitle: 'store',
                title: '{s name=tabs/store}Community Store{/s}',
                items: [{
                    xtype: 'plugin-manager-store-navigation',
                    region: 'west',
                    width: 220,
                    categoryStore: me.categoryStore,
                    updatesStore: me.updatesStore
                }, me.storeContainer ]
            }/** {/if} */]
        });
    }
});
//{/block}
