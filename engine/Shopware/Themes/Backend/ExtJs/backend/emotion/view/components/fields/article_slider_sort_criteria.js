/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/emotion/view/components/article_slider_sort_criteria}
Ext.define('Shopware.apps.Emotion.view.components.fields.ArticleSliderSortCriteria', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.emotion-components-fields-article-slider-sort-criteria',
    name: 'article_slider_sort_criteria',

    /**
     * Snippets for the component
     * @object
     */
    snippets: {
        fields: {
            'article_slider_sort_criteria': '{s name=article_slider_sort_criteria/fields/article_slider_sort_criteria}Sorting criteria{/s}',
            'empty_text': '{s name=article_slider_sort_criteria/fields/empty_text}Please select...{/s}'
        },
        store: {
            'number': '{s name=article_slider_sort_criteria/store/number}Number{/s}',
            'release_date': '{s name=article_slider_sort_criteria/store/release_date}Release date{/s}',
            'sales': '{s name=article_slider_sort_criteria/store/sales}Sales{/s}',
            'price_asc': '{s name=article_slider_sort_criteria/store/price_asc}Price (low to high){/s}',
            'price_desc': '{s name=article_slider_sort_criteria/store/price_desc}Price (high to low){/s}'
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
            fieldLabel: me.snippets.fields.article_slider_sort_criteria,
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
     * @return [object] Ext.data.Store
     */
    createStore: function() {
        var me = this, snippets = me.snippets.store;

        return Ext.create('Ext.data.JsonStore', {
            fields: [ 'value', 'display' ],
            data: [{
                value: 'number',
                display: snippets.number
            }, {
                value: 'release_date',
                display: snippets.release_date
            }, {
                value: 'sales',
                display: snippets.sales
            }, {
                value: 'price_asc',
                display: snippets.price_asc
            }, {
                value: 'price_desc',
                display: snippets.price_desc
            }]
        });
    }
});