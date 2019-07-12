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
    layout: 'fit',
    height: '90%',
    width: '85%',
    stateful: true,
    stateId: 'user-manager-main-window',
    snippets:{
        navigation: {
            userList: '{s name=navigation/navigation_usermanager}{/s}',
            roleList: '{s name=navigation/navigation_rolemanager}{/s}',
            rulesTree: '{s name=navigation/navigation_rights_assignment}{/s}'
        }
    },


    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [{
            xtype: 'tabpanel',
            items: [{
                xtype: 'usermanager-user-list',
                title: me.snippets.navigation.userList,
                store: me.userStore
            }, {
                xtype: 'usermanager-roles-list',
                title: me.snippets.navigation.roleList,
                store: me.roleStore
            }, {
                xtype: 'user-manager-rules-tree',
                title: me.snippets.navigation.rulesTree,
            }]
        }];

        me.callParent(arguments);
    }
});
//{/block}
