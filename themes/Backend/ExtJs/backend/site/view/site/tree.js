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
 * Shopware UI - Site site Tree View
 *
 * This file contains the layout of the navigation tree.
 */

//{namespace name=backend/site/site}

//{block name="backend/site/view/site/tree"}
Ext.define('Shopware.apps.Site.view.site.Tree', {
    extend: 'Ext.tree.Panel',
    alias : 'widget.site-tree',
    rootVisible: false,
    animate: false,

    initComponent: function() {
        var me = this;
        me.bbar = me.getBottomToolbar();
        me.callParent(arguments);
    },

    getBottomToolbar: function() {
        var buttons = [];
        /*{if {acl_is_allowed privilege=createGroup}}*/
        buttons.push(Ext.create("Ext.button.Button",{
            text: '{s name=treeCreateGroupButton}Add group{/s}',
            iconCls: 'sprite-blue-folder--plus',
            cls: 'small secondary',
            action: 'onCreateGroup'
        }));
        /*{/if}*/
        /*{if {acl_is_allowed privilege=deleteGroup}}*/
        buttons.push('->');
        buttons.push(Ext.create("Ext.button.Button",{
            text: '{s name=treeDeleteGroupButton}Delete group{/s}',
            action: 'onDeleteGroup',
            cls: 'small secondary',
            iconCls: 'sprite-blue-folder--minus',
            disabled: true
        }));
        /*{/if}*/

        return buttons
    }
});
//{/block}
