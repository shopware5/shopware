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

//{namespace name=backend/snippet/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/snippet/view/main/snippet_panel"}
Ext.define('Shopware.apps.Snippet.view.main.SnippetPanel', {
    extend: 'Ext.tab.Panel',
    alias: 'widget.snippet-main-snippetPanel',
    plain: true,

    /**
     * Initializes the component
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me    = this,
            items = [];

        me.shoplocaleStore.each(function(record) {
            items.push({
                title: record.get('displayName'),
                xtype: 'snippet-main-grid',
                store: me.snippetStore,
                shoplocale: record
            });
        });

        me.items = items;

        me.callParent(arguments);
    },

    /**
     * @param boolean - enabled
     * @return void
     */
    enableExpertMode: function(enabled) {
        var me = this;

        me.items.each(function(item) {
            item.enableExpertMode(enabled);
        });
    }
});
//{/block}
