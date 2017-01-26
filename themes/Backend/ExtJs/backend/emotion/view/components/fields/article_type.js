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
//{namespace name=backend/emotion/view/components/article}
Ext.define('Shopware.apps.Emotion.view.components.fields.ArticleType', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.emotion-components-fields-article-type',
    name: 'article_type',

    /**
     * Snippets for the component
     * @object
     */
    snippets: {
        fields: {
            'article_type': '{s name=article/fields/article_type}{/s}',
            'empty_text': '{s name=article/fields/empty_text}{/s}'
        },
        store: {
            'selected_article': '{s name=article/store/selected_article}{/s}',
            'selected_variant': '{s name=article/store/selected_variant}{/s}',
            'newcomer': '{s name=article/store/newcomer}{/s}',
            'topseller': '{s name=article/store/topseller}{/s}',
            'random_article': '{s name=article/store/random_article}{/s}'
        }
    },

    /**
     * Initialize the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        Ext.apply(me, {
            emptyText: me.snippets.fields.empty_text,
            fieldLabel: me.snippets.fields.article_type,
            displayField: 'display',
            valueField: 'value',
            queryMode: 'local',
            triggerAction: 'all',
            store: me.createStore()
        });

        me.callParent(arguments);
        me.on('change', me.onArticleSelectChange, me);
    },

    /**
     * Event listeners which triggers when the user changes the value
     * of the select field.
     *
     * @public
     * @event change
     * @param { Ext.form.field.ComboBox } field
     * @param { string } value - The selected value
     */
    onArticleSelectChange: function(field, value) {
        var me = this;

        // Terminate the article search field
        if (!me.articleSearch) {
            me.articleSearch = me.up('fieldset').down('emotion-components-fields-article');
        }

        // Terminate the article search field
        if (!me.variantSearch) {
            me.variantSearch = me.up('fieldset').down('emotion-components-fields-variant');
        }

        // Show/hide article search field based on selected entry
        me.articleSearch.setVisible(value === 'selected_article');

        // Show/hide variant search field based on selected entry
        me.variantSearch.setVisible(value === 'selected_variant');
    },

    /**
     * Creates a local store which will be used
     * for the combo box. We don't need that data.
     *
     * @public
     * @return { Ext.data.JsonStore }
     */
    createStore: function() {
        var me = this, snippets = me.snippets.store;

        return Ext.create('Ext.data.JsonStore', {
            fields: ['value', 'display'],
            data: [{
                value: 'selected_article',
                display: snippets.selected_article
            }, {
                value: 'selected_variant',
                display: snippets.selected_variant
            }, {
                value: 'newcomer',
                display: snippets.newcomer
            }, {
                value: 'topseller',
                display: snippets.topseller
            }, {
                value: 'random_article',
                display: snippets.random_article
            }]
        });
    }
});
