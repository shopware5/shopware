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

//{namespace name=backend/emotion/view/components/article_slider_type}
Ext.define('Shopware.apps.Emotion.view.components.fields.ArticleSliderType', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.emotion-components-fields-article-slider-type',
    name: 'article_slider_type',

    /**
     * Snippets for the component
     * @object
     */
    snippets: {
        fields: {
            'article_slider_type': '{s name=article_slider_type/fields/article_slider_type}{/s}',
            'empty_text': '{s name=article_slider_type/fields/empty_text}{/s}'
        },
        store: {
            'selected_article': '{s name=article_slider_type/store/selected_article}{/s}',
            'selected_variant': '{s name=article_slider_type/store/selected_variant}{/s}',
            'newcomer': '{s name=article_slider_type/store/newcomer}{/s}',
            'topseller': '{s name=article_slider_type/store/topseller}{/s}',
            'price_asc': '{s name=article_slider_type/store/price_asc}{/s}',
            'price_desc': '{s name=article_slider_type/store/price_desc}{/s}',
            'product_stream': '{s name=article_slider_type/store/product_stream}{/s}',
            'random_product': '{s name=article_slider_type/store/random_product}{/s}'
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
            fieldLabel: me.snippets.fields.article_slider_type,
            displayField: 'display',
            valueField: 'value',
            queryMode: 'local',
            triggerAction: 'all',
            store: me.createStore()
        });

        me.callParent(arguments);
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
                value: 'price_asc',
                display: snippets.price_asc
            }, {
                value: 'price_desc',
                display: snippets.price_desc
            }, {
                value: 'product_stream',
                display: snippets.product_stream
            }, {
                value: 'random_product',
                display: snippets.random_product
            }]
        });
    }
});
