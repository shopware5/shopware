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

//{namespace name=backend/article_list/main}
//{block name="backend/article_list/view/main/sidebar"}
Ext.define('Shopware.apps.ArticleList.view.main.Sidebar', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.multi-edit-sidebar',

    collapsible: true,

    /**
     * Set width
     */
    width: 210,

    layout: 'accordion',

    title: '{s name=categoriesAndFilters}Categories & Filters{/s}',


    initComponent: function () {
        var me = this;

        me.items = me.getPanels();

        me.callParent(arguments);
    },

    /**
     * Returns the three elements of the accordion layout
     */
    getPanels: function () {
        var me = this;

        return [
            { xtype: 'multi-edit-category-tree' },
            // Wrap the filter grid into a panel so that the
            // accordion elements a formatted properly
            { xtype: 'panel',
                title: '{s name=filter}Filter{/s}',
                layout: 'fit',
                items: [
                    {
                        xtype: 'multi-edit-navigation-grid'
                    }
                ] }
        /*{if {acl_is_allowed privilege=doMultiEdit}}*/
        ,{ xtype: 'multi-edit-menu' }
        /*{/if}*/
        ];
    }

});
//{/block}
