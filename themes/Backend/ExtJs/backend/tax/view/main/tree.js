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

//{namespace name=backend/tax/view/main}

/**
 * Shopware UI - Site site Tree View
 *
 * This file contains the layout of the navigation tree.
 */
//{block name="backend/tax/view/tree"}

Ext.define('Shopware.apps.Tax.view.main.Tree', {
    extend: 'Ext.tree.Panel',
    alias : 'widget.tax-tree',
    rootVisible: false,
    width: 250,

    /**
     * Define snippets for multilingual support
     */
    snippets: {
        treeCreateGroupButton: '{s name=ui/treeCreateAreaButton}Add area{/s}',
        treeDeleteGroupButton: '{s name=ui/treeDeleteArea}Delete area{/s}'
    },

    viewConfig: {
      toggleOnDblClick: false
    },

    initComponent: function() {
        var me = this;
        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'top',
            ui: 'shopware-ui',
            items: me.getTopToolbar()
        }];
        me.callParent(arguments);
    },

    getTopToolbar: function() {
        return [
            {
                xtype: 'button',
                text: '{s name="tax/tree_button/add"}Add group{/s}',
                action: 'onCreateGroup'
            }, '->',
            {
                xtype: 'button',
                text: '{s name="tax/tree_button/delete"}Delete group{/s}',
                action: 'onDeleteGroup',
                disabled: true
            }
        ]
    }
});
//{/block}
