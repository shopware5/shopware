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
 * Shopware UI - User Manager Navigation
 *
 * This file represents the navigation of the module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/user_manager/view/main/navigation"}
Ext.define('Shopware.apps.UserManager.view.main.Navigation', {
    extend: 'Ext.panel.Panel',
    region: 'west',
    alias: 'widget.usermanager-main-navigation',
    layout: 'accordion',
    autoScroll: true,
    border: false,
    items: [],
    width: 250,
    defaults: {
        bodyPadding: 10
    },
    /**
     * Initialize the navigation bar and add navigation items
     *
     * @return void
     */
    initComponent: function() {

        // Panel with a button to push
        /* {if {acl_is_allowed privilege=read}} */
        this.generalPnl = Ext.create('Ext.panel.Panel', {
            title: '{s name=navigation/navigation_user_title}Users and roles{/s}',
            layout: 'anchor',
            defaults: {
                margin: '0 0 5',
                align: 'stretch'
            },
            items: [{
                xtype: 'button',
                text: '{s name=navigation/navigation_userlist}List of users{/s}',
                action: 'open-user-view',
                iconCls: 'sprite-user--list',
                anchor: '100%',
                cls: 'small secondary'
            }, {
                xtype: 'button',
                text: '{s name=navigation/navigation_rolelist}List of roles{/s}',
                action: 'open-roles-view',
                iconCls: 'sprite-users',
                anchor: '100%',
                cls: 'small secondary'
            },
            {
                xtype: 'button',
                text: '{s name=navigation/navigation_rights_assignment}Edit rules & permissions{/s}',
                action: 'open-rules-view',
                iconCls: 'sprite-key--pencil',
                anchor: '100%',
                cls: 'small secondary'
            }
            ]
        });

        this.items = [ this.generalPnl];
        /* {/if} */
        this.callParent(arguments);
    }
});
//{/block}
