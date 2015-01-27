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
//{block name="backend/snippet/view/main/edit_form"}
Ext.define('Shopware.apps.Snippet.view.main.EditForm', {
    extend: 'Enlight.app.Window',
    alias: 'widget.snippet-main-editForm',

    layout: 'fit',
    width: 860,
    height: 600,

    /**
     * Array containing the records
     *
     * @array
     */
    selectedSnippets: [],

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        titleEditWindow: '{s name=title_edit_window}Edit Snippets{/s}'
    },

    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.titleEditWindow;

        me.items = [{
            xtype: 'snippet-main-form',
            snippets: me.selectedSnippets
        }];

        me.callParent(arguments);
    }
});
//{/block}
