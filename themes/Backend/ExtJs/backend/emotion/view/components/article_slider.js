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
 * @package    Emotion
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{block name="backend/emotion/view/components/article_slider"}
//{namespace name=backend/emotion/view/components/article_slider}
Ext.define('Shopware.apps.Emotion.view.components.ArticleSlider', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-article-slider',

    /**
     * Snippets for the component.
     * @object
     */
    snippets: {
        'article_administration': '{s name=article_administration}Article administration{/s}',

        article_slider_max_number: '{s name=article_slider_max_number}Maximum number of articles{/s}',
        article_slider_title: '{s name=article_slider_title}Title{/s}',
        article_slider_arrows: '{s name=article_slider_arrows}Display arrows{/s}',
        article_slider_scrollspeed: '{s name=article_slider_scrollspeed}Scroll speed{/s}',
        article_slider_category: '{s name=article_slider_category}Filter by category{/s}',

        article_slider_rotation: '{s name=article_slider_rotation}Rotate automatically{/s}',
        article_slider_rotatespeed: '{s name=article_slider_rotatespeed}Rotation speed{/s}',

        no_border: {
            fieldLabel: '{s name="noBorder/label" namespace="backend/emotion/view/components/article"}{/s}',
            supportText: '{s name="noBorder/supportText" namespace="backend/emotion/view/components/article"}{/s}'
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
        me.callParent(arguments);

        me.setDefaultValues();

        me.articleType = me.down('emotion-components-fields-article-slider-type');
        me.categoryFilter = me.down('emotion-components-fields-category-selection');
        me.streamSelection = me.down('productstreamselection');

        me.streamSelection.allowBlank = true;
        me.categoryFilter.allowBlank = true;
        me.streamSelection.hide();

        if(!me.articleType.getValue()) {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.articleGrid.hide();
            me.variantGrid.hide();
        }
        if(me.articleType.getValue() === 'selected_article') {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.variantGrid.hide();
            me.articleGrid.show();
            me.streamSelection.allowBlank = true;
            me.categoryFilter.allowBlank = true;
        } else if(me.articleType.getValue() === 'selected_variant') {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.articleGrid.hide();
            me.variantGrid.show();
            me.streamSelection.allowBlank = true;
            me.categoryFilter.allowBlank = true;
        } else if (me.articleType.getValue() == 'product_stream') {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.articleGrid.hide();
            me.variantGrid.hide();
            me.streamSelection.show();
            me.streamSelection.allowBlank = false;
            me.categoryFilter.allowBlank = true;
        } else {
            me.maxCountField.show();
            me.categoryFilter.show();
            me.articleGrid.hide();
            me.variantGrid.hide();
            me.streamSelection.allowBlank = true;
            me.categoryFilter.allowBlank = false;
        }
        me.streamSelection.validate();
        me.articleType.on('change', me.onChange, me);
    },

    /**
     * overrides the default behaviour for the product selection to use the Shopware component
     *
     * @override
     */
    pushItemToElements: function(item, items) {
        var me = this,
            factory = Ext.create('Shopware.attribute.SelectionFactory');

        if (item.name === 'selected_articles') {
            me.articleGrid = Ext.create('Shopware.form.field.ProductGrid', {
                name: item.name,
                fieldId: item.fieldId,
                store: factory.createEntitySearchStore("Shopware\\Models\\Article\\Article"),
                searchStore: factory.createEntitySearchStore("Shopware\\Models\\Article\\Article"),
                fieldLabel: me.snippets.article_administration,
                labelWidth: 170
            });

            items.push(me.articleGrid);

            return items;
        } else if (item.name === 'selected_variants') {
            me.variantGrid = Ext.create('Shopware.form.field.ProductGrid', {
                name: item.name,
                fieldId: item.fieldId,
                store: factory.createEntitySearchStore("Shopware\\Models\\Article\\Detail"),
                searchStore: factory.createEntitySearchStore("Shopware\\Models\\Article\\Detail"),
                fieldLabel: me.snippets.article_administration,
                labelWidth: 170
            });

            items.push(me.variantGrid);

            return items;
        }

        return me.callParent(arguments);
    },

    onChange: function(field, newValue) {
        var me = this;

        me.streamSelection.allowBlank = true;
        me.streamSelection.hide();

        if (newValue == 'selected_article') {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.variantGrid.hide();
            me.articleGrid.show();
            me.streamSelection.allowBlank = true;
            me.categoryFilter.allowBlank = true;
        } else if (newValue == 'selected_variant') {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.articleGrid.hide();
            me.variantGrid.show();
            me.streamSelection.allowBlank = true;
            me.categoryFilter.allowBlank = true;
        } else if (newValue == 'product_stream') {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.articleGrid.hide();
            me.variantGrid.hide();
            me.streamSelection.show();
            me.streamSelection.allowBlank = false;
            me.categoryFilter.allowBlank = true;
        } else {
            me.maxCountField.show();
            me.categoryFilter.show();
            me.articleGrid.hide();
            me.variantGrid.hide();
            me.streamSelection.allowBlank = true;
            me.categoryFilter.allowBlank = false;
        }
        me.streamSelection.validate();
    },

    /**
     * Sets default values if the article slider
     * wasn't saved previously.
     *
     * @public
     * @return void
     */
    setDefaultValues: function() {
        var me = this,
            numberfields =  me.query('numberfield'),
            checkboxes = me.query('checkbox');

        Ext.each(numberfields, function(field) {
            if(field.getName() === 'article_slider_max_number') {
                me.maxCountField = field;
                if(!field.getValue()) {
                    field.setValue(25);
                }
            }

            if(field.getName() === 'article_slider_rotatespeed') {
                me.rotateSpeed = field;
            }

            if(!field.getValue()) {
                field.setValue(500);
            }
        });

        Ext.each(checkboxes, function(field) {
            if(field.getName() === 'article_slider_rotation') {
                me.rotation = field;
            }
        });
    }
});
//{/block}
