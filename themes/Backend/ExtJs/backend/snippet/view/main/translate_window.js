/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

//{namespace name="backend/snippet/view/main"}

//{block name="backend/snippet/view/main/translate_window"}
Ext.define('Shopware.apps.Snippet.view.main.TranslateWindow', {
    extend: 'Enlight.app.Window',
    alias: 'widget.snippet-main-translateWindow',

    layout: 'fit',
    width: 860,
    height: 600,

    /**
     * Root snippet (the one the user clicked)
     *
     * @object
     */
    rootSnippet: {},

    /**
     * Shop/locale store
     *
     * @object
     */
    shopLocaleStore: {},

    /**
     * Snippet store
     *
     * @object
     */
    snippetStore: {},

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        titleTranslateWindow: '{s name="title_translate_window"}Translate snippet{/s}'
    },

    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.titleTranslateWindow;

        me.translationForm = Ext.create('Shopware.apps.Snippet.view.main.TranslateForm', {
            rootSnippet: me.rootSnippet,
            snippetStore: me.snippetStore,
            shopLocaleStore: me.shopLocaleStore
        });
        me.items = [me.translationForm];

        me.callParent(arguments);
    }
});
//{/block}
