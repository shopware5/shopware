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
        'select_article': '{s name=select_article}Select article(s){/s}',
        'article_administration': '{s name=article_administration}Article administration{/s}',
        'name': '{s name=name}Article name{/s}',
        'ordernumber': '{s name=ordernumber}Order number{/s}',
        'actions': '{s name=actions}Action(s){/s}',

        article_slider_max_number: '{s name=article_slider_max_number}Maximum number of articles{/s}',
        article_slider_title: '{s name=article_slider_title}Title{/s}',
        article_slider_arrows: '{s name=article_slider_arrows}Display arrows{/s}',
        article_slider_numbers: {
            fieldLabel: '{s name=article_slider_numbers}Display numbers{/s}',
            supportText: '{s name=article_slider_numbers_support}Only supported in Emotion templates{/s}'
        },
        article_slider_scrollspeed: '{s name=article_slider_scrollspeed}Scroll speed{/s}',
        article_slider_category: '{s name=article_slider_category}Filter by category{/s}',

        article_slider_rotation: '{s name=article_slider_rotation}Rotate automatically{/s}',
        article_slider_rotatespeed: '{s name=article_slider_rotatespeed}Rotation speed{/s}'
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
        me.add(me.createArticleFieldset());
        me.setDefaultValues();
        me.getGridData();

        me.articleType = me.down('emotion-components-fields-article-slider-type');
        me.categoryFilter = me.down('emotion-components-fields-category-selection');
        me.streamSelection = me.down('productstreamselection');

        me.streamSelection.allowBlank = true;
        me.categoryFilter.allowBlank = true;
        me.streamSelection.hide();

        if(!me.articleType.getValue()) {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.articleFieldset.hide();
        }
        if(me.articleType.getValue() === 'selected_article') {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.articleFieldset.show();
            me.rotateSpeed.show().enable();
            me.rotation.show().enable();
            me.streamSelection.allowBlank = true;
            me.categoryFilter.allowBlank = true;
        } else if (me.articleType.getValue() == 'product_stream') {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.articleFieldset.hide();
            me.rotateSpeed.show().enable();
            me.rotation.show().enable();
            me.streamSelection.show();
            me.streamSelection.allowBlank = false;
            me.categoryFilter.allowBlank = true;
        } else {
            me.maxCountField.show();
            me.categoryFilter.show();
            me.articleFieldset.hide();
            me.rotateSpeed.hide().disable();
            me.rotation.hide().disable();
            me.streamSelection.allowBlank = true;
            me.categoryFilter.allowBlank = false;
        }
        me.streamSelection.validate();
        me.articleType.on('change', me.onChange, me);

        me.refreshHiddenValue();
    },

    onChange: function(field, newValue) {
        var me = this;

        me.streamSelection.allowBlank = true;
        me.streamSelection.hide();

        if (newValue == 'selected_article') {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.articleFieldset.show();
            me.rotateSpeed.show().enable();
            me.rotation.show().enable();
            me.streamSelection.allowBlank = true;
            me.categoryFilter.allowBlank = true;
        } else if (newValue == 'product_stream') {
            me.maxCountField.hide();
            me.categoryFilter.hide();
            me.articleFieldset.hide();
            me.rotateSpeed.show().enable();
            me.rotation.show().enable();
            me.streamSelection.show();
            me.streamSelection.allowBlank = false;
            me.categoryFilter.allowBlank = true;
        } else {
            me.maxCountField.show();
            me.categoryFilter.show();
            me.articleFieldset.hide();
            me.rotateSpeed.hide().disable();
            me.rotation.hide().disable();
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
    },

    /**
     * Creates the fieldset which holds the article administration. The method
     * also creates the article store and registers the drag and drop plugin
     * for the grid.
     *
     * @public
     * @return [object] Ext.form.FieldSet
     */
    createArticleFieldset: function() {
        var me = this;

        me.articleSearch = Ext.create('Shopware.form.field.ArticleSearch', {
            layout: 'anchor',
            anchor: '100%',
            multiSelect: false,
            returnValue: 'name',
            hiddenReturnValue: 'number',
            listeners: {
                scope: me,
                valueselect: me.onAddArticleToGrid
            }
        });

        me.articleStore = Ext.create('Ext.data.Store', {
            fields: [ 'position', 'name', 'ordernumber', 'articleId' ]
        });

        me.ddGridPlugin = Ext.create('Ext.grid.plugin.DragDrop');

        me.articleGrid = Ext.create('Ext.grid.Panel', {
            columns: me.createColumns(),
            autoScroll: true,
            store: me.articleStore,
            height: 300,
            viewConfig: {
                plugins: [ me.ddGridPlugin ],
                listeners: {
                    scope: me,
                    drop: me.onRepositionArticle
                }
            }
        });

        return me.articleFieldset = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.article_administration,
            layout:  'anchor',
            defaults: { anchor: '100%' },
            items: [ me.articleSearch, me.articleGrid ]
        });
    },

    /**
     * Helper method which creates the column model
     * for the article administration grid panel.
     *
     * @public
     * @return [array] computed columns
     */
    createColumns: function() {
        var me = this, snippets = me.snippets;

        return [{
            header: '&#009868;',
            width: 24,
            hideable: false,
            renderer : me.renderSorthandleColumn
        }, {
            dataIndex: 'name',
            header: snippets.name,
            flex: 1
        }, {
            dataIndex: 'ordernumber',
            header: snippets.ordernumber,
            flex: 1
        }, {
            xtype: 'actioncolumn',
            header: snippets.actions,
            width: 60,
            items: [{
                iconCls: 'sprite-minus-circle',
                action: 'delete-article',
                scope: me,
                handler: me.onDeleteArticle
            }]
        }];
    },

    /**
     * Event listener method which will be triggered when one (or more)
     * article are added to the article slider.
     *
     * Creates new models based on the selected articles and
     * assigns them to the article store.
     *
     * @public
     * @event selectMedia
     * @param [object] field - Shopware.MediaManager.MediaSelection
     * @param [array] records - array of the selected media
     */
    onAddArticleToGrid: function(field, returnVal, hiddenVal, record) {
        var me = this, store = me.articleStore;

        var model = Ext.create('Shopware.apps.Emotion.model.ArticleSlider', {
            position: store.getCount(),
            name: returnVal,
            ordernumber: hiddenVal,
            articleId: record.get('id')
        });
        store.add(model);

        field.searchField.setValue();
        me.refreshHiddenValue();

    },

    /**
     * Event listener method which will be triggered when the user
     * deletes a article from article administration grid panel.
     *
     * Removes the article from the article store.
     *
     * @event click#actioncolumn
     * @param [object] grid - Ext.grid.Panel
     * @param [integer] rowIndex - Index of the clicked row
     * @param [integer] colIndex - Index of the clicked column
     * @param [object] item - DOM node of the clicked row
     * @param [object] eOpts - additional event parameters
     * @param [object] record - Associated model of the clicked row
     */
    onDeleteArticle: function(grid, rowIndex, colIndex, item, eOpts, record) {
        var me = this;
        var store = grid.getStore();
        store.remove(record);
        me.refreshHiddenValue();
    },

    /**
     * Event listener method which will be fired when the user
     * repositions a article through drag and drop.
     *
     * Sets the new position of the article in the article store
     * and saves the data to an hidden field.
     *
     * @public
     * @event drop
     * @return void
     */
    onRepositionArticle: function() {
        var me = this;

        var i = 0;
        me.articleStore.each(function(item) {
            item.set('position', i);
            i++;
        });
        me.refreshHiddenValue();
    },

    /**
     * Refreshes the mapping field in the model
     * which contains all articles in the grid.
     *
     * @public
     * @return void
     */
    refreshHiddenValue: function() {
        var me = this,
            store = me.articleStore,
            cache = [];

        store.each(function(item) {
            cache.push(item.data);
        });
        var record = me.getSettings('record');
        record.set('mapping', cache);
    },

    /**
     * Refactor the mapping field in the global record
     * which contains all article in the grid.
     *
     * Adds all articles to the article administration grid
     * when the user opens the component.
     *
     * @return void
     */
    getGridData: function() {
        var me = this,
            elementStore = me.getSettings('record').get('data'), articleSlider;

        Ext.each(elementStore, function(element) {
            if(element.key === 'selected_articles') {
                articleSlider = element;
                return false;
            }
        });

        if(articleSlider && articleSlider.value) {
            Ext.each(articleSlider.value, function(item) {
                me.articleStore.add(Ext.create('Shopware.apps.Emotion.model.ArticleSlider', item));
            });
        }
    },

    /**
     * Renderer for sorthandle-column
     *
     * @param [string] value
     */
    renderSorthandleColumn: function() {
        return '<div style="cursor: move;">&#009868;</div>';
    }
});
//{/block}
