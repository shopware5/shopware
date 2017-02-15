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
 * @package    UserManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/user_manager/view/main}

/**
 * Shopware UI - Media Manager Main Window
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/user_manager/view/main/window"}
Ext.define('Shopware.apps.UserManager.view.main.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window/title}Backend user administration{/s}',
    alias: 'widget.usermanager-main-window',
    border: false,
    autoShow: true,
    layout: 'border',
    height: '90%',
    width: 1200,
    stateful: true,
    stateId: 'user-manager-main-window',


    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        /* {if {acl_is_allowed privilege=read}} */
        me.appContent = Ext.create('Ext.container.Container', {
            unstyled: true,
            region: 'center',
            border: false,
            layout: 'border',
            autoScroll: true,
            listeners: {
                afterrender: function(con) {
                    con.getEl().setStyle('overflow', 'hidden');

                    window.setTimeout(function() {
                        con.getEl().setStyle('overflow', 'auto');
                    }, 10);
                }
            },
            items: [{
                xtype: 'usermanager-user-list',
                userStore: me.userStore
            }]
        });
        /* {/if} */
        me.items = [{
            xtype: 'usermanager-main-navigation',
            region: 'west'
        }, me.appContent];

        me.callParent(arguments);
    }
});
//{/block}
