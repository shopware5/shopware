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

//{block name="backend/snippet/view/main/translate_form"}
Ext.define('Shopware.apps.Snippet.view.main.TranslateForm', {
    extend: 'Ext.form.Panel',
    alias: 'widget.snippet-main-translateForm',
    autoScroll: true,
    width: 860,
    height: 600,
    bodyPadding: 10,

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
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.buttons = me.getButtons();
        me.items = me.getItems();

        me.callParent(arguments);
    },

    /**
     * Creates items shown in form panel
     *
     * @return array
     */
    getItems: function() {
        var me            = this,
            formItems     = [],
            fieldSetItems = [];

        me.shopLocaleStore.each(function(locale) {
            var localeId = locale.get('localeId'),
                shopId = locale.get('shopId');

            var translationId = me.snippetStore.findBy(function(rec) {
                return (rec.get('localeId') == localeId && rec.get('shopId') == shopId);
            });

            var translation = me.snippetStore.getAt(translationId);

            if (translation == null) {
                var translations = me.snippetStore.add({
                    defaultValue: me.rootSnippet.get('defaultValue'),
                    localeId: localeId,
                    name: me.rootSnippet.get('name'),
                    namespace: me.rootSnippet.get('namespace'),
                    shopId: locale.get('shopId'),
                    value: ""
                });

                translation = translations[0];
            }

            fieldSetItems.push({
                fieldLabel: locale.get('displayName'),
                name: translation.internalId,
                emptyText: Ext.util.Format.htmlEncode(Ext.util.Format.stripTags(translation.get('defaultValue'))),
                value: translation.get('value')
            });
        });

        formItems.push({
            xtype: 'fieldset',
            title: me.rootSnippet.get('namespace') + ' - ' + me.rootSnippet.get('name'),
            defaultType: 'textarea',
            defaults: {
                layout: 'anchor',
                labelWidth: 200,
                anchor: '100%'
            },
            items: fieldSetItems
        });

        return formItems;
    },

    /**
     * Creates buttons shown in form panel
     *
     * @return array
     */
    getButtons: function() {
        var me = this;

        return [{
            cls: 'primary',
            text: '{s name=button_save_form}Save{/s}',
            action: 'save',
            formBind: true,
            handler: function() {
                me.fireEvent('save')
            }
        }];
    }
});
//{/block}
