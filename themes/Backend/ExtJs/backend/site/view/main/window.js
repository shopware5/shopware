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
 * @package    Site
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Site main Window View
 *
 * This file contains the layout for the main window.
 */

//{namespace name=backend/site/site}

//{block name="backend/site/view/main/window"}
Ext.define('Shopware.apps.Site.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.site-mainWindow',
    layout: 'border',
    width: 1200,
    height: '90%',
    autoShow: true,
    resizable: true,
    maximizable: true,
    minimizable: true,
    stateful: true,
    stateId: 'site',

    initComponent: function() {
        var me = this;

        // Set the title
        me.title = '{s name=mainWindowTitle}Sites{/s}';

        // Get all items for this window
        me.items = me.getItems();

        // Get the upper toolbar
        me.tbar = me.getUpperToolbar();

        // Call parent
        me.callParent(arguments);
    },

    getItems: function() {
        var me = this;

        return [
            {
                xtype: 'site-tree',
                region: 'west',
                store: me.nodeStore,
                flex: 0.25
            },
            {
                xtype: 'site-form',
                region: 'center',
                groupStore: me.groupStore,
                selectedStore: me.selectedStore,
                shopStore: me.shopStore
            }
        ]
    },

    getUpperToolbar: function() {
        var me = this,
            buttons = [];

        /*{if {acl_is_allowed privilege=createSite}}*/
        buttons.push(Ext.create("Ext.button.Button",{
            text: '{s name=mainWindowCreateSiteButton}New site{/s}',
            action: 'onCreateSite',
            iconCls: 'sprite-blue-document--plus'
        }));
        /*{/if}*/

        /*{if {acl_is_allowed privilege=deleteSite}}*/
        buttons.push(Ext.create("Ext.button.Button",{
            text: '{s name=mainWindowDeleteSiteButton}Delete site{/s}',
            action: 'onDeleteSite',
            iconCls: 'sprite-blue-document--minus',
            disabled: true
        }));
        /*{/if}*/

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            region: 'north',
            items: buttons
        });
    }
});
//{/block}
