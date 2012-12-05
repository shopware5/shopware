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
 * @package    NewsletterManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{block name=backend/newsletter_manager/view/components/article}
//{namespace name=backend/newsletter_manager/view/components/article}
Ext.define('Shopware.apps.NewsletterManager.view.components.Article', {
    extend: 'Shopware.apps.NewsletterManager.view.components.Base',
    alias: 'widget.newsletter-components-article',

    /**
     * Snippets for the component.
     * @object
     */
    snippets: {
        'add_article': '{s name=add_article}Add article{/s}',
        'article_administration': '{s name=article_administration}Article administration{/s}',
        'type': '{s name=type}Article type{/s}',
        'actions': '{s name=actions}Action(s){/s}',
        'ordernumber': '{s name=ordernumber}Article ordernumber{/s}',
        'name': '{s name=name}Article name{/s}'
    },

    /**
     * Initiliaze the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.typeStore = me.createTypeStore();
        me.articleNumberSearch = me.createArticleSearch('ordernumber', 'name', 'articleOrderNumber');
        me.articleNameSearch = me.createArticleSearch('name', 'ordernumber', 'articleName');

        me.callParent(arguments);

        me.setDefaultValues();
        me.add(me.createArticleFieldset());
        me.getGridData();
        me.refreshHiddenValue();
    },

    /**
     * Creates the simple type store. As only a few possible options are available,
     * its created 'in the fly'
     * @return Ext.data.ArrayStore
     */
    createTypeStore: function() {
        return new Ext.data.ArrayStore({
            fields: [ 'id', 'value', 'name' ],
            data: [
                [ 1, 'random', 'Zufall' ],
                [ 2, 'top', 'Topseller' ],
                [ 3, 'new', 'Neuheiten' ],
//                [ 4, 'suggest', 'Vorschlag für Kunden' ],
                [ 5, 'fix', 'Festgelegter Artikel' ]
            ]
        });
    },

    /**
     * Sets default values if the banner list
     * wasn't saved previously.
     *
     * @public
     * @return void
     */
    setDefaultValues: function() {
        var me = this,
            numberfields =  me.query('numberfield');

        Ext.each(numberfields, function(field) {
            if(!field.getValue()) {
                field.setValue(500);
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
        me.addArticleButton = Ext.create('Ext.Button', {
//            iconCls:'sprite-plus-circle-frame',
            action:'addArticle',
            anchor: '15%',
            cls: 'primary',
            text: me.snippets.add_article,
            margin: '0 0 20 0',
            handler: function() {
                me.onAddArticleToGrid(me.articleGrid, me.cellEditing);
            }
        });

        me.articleStore = Ext.create('Ext.data.Store', {
            fields: [ 'position', 'type', 'ordernumber', 'name' ]
        });

        me.ddGridPlugin = Ext.create('Ext.grid.plugin.DragDrop');

        me.cellEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2
        });

        me.setupRowEditingEvents();

        me.articleGrid = Ext.create('Ext.grid.Panel', {
            columns: me.createColumns(),
            autoScroll: true,
            store: me.articleStore,
            height: 200,
            plugins: [ me.cellEditing ],
            viewConfig: {
                plugins: [ me.ddGridPlugin ],
                listeners: {
                    scope: me,
                    drop: me.onRepositionArticle
                }
            },
            listeners: {
                scope: me,
                edit: function() {
                    me.refreshHiddenValue();
                }
            }
        });

        return me.articleFieldset = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.article_administration,
            layout: 'anchor',
            defaults: { anchor: '100%' },
            items: [ me.addArticleButton, me.articleGrid ]
        });
    },

    /**
     * Helper function to register and handle various events regarding the rowediting plugin
     */
    setupRowEditingEvents: function() {
        var me = this;

        //register listener on the before edit event to set the article name and number manually into the row editor.
        me.cellEditing.on('beforeedit', function(editor, e) {
            var columns = editor.editor.items.items;

            columns[1].setValue(e.record.get('type'));

            // Only save ordernumber/articlename to list, if selected article type is 'fix'
            if(e.record.get('type') == 'fix'){
                columns[2].setValue(e.record.get('ordernumber'));
                columns[3].setValue(e.record.get('name'));
            }
        });

        // hide the search fields when editing is finished
        me.cellEditing.on('edit', function(editor, e) {
            me.articleNumberSearch.getDropDownMenu().hide();
            me.articleNameSearch.getDropDownMenu().hide();

            if(e.newValues.type == 'fix') {
                e.record.set('name', e.newValues.name);
                e.record.set('ordernumber', e.newValues.ordernumber);
            }
            e.record.raw.isNew = false;
            me.articleNumberSearch.setValue('');
            me.articleNameSearch.setValue('');
        });

        // throw away the record, if editing is canceled and record was just created
        me.cellEditing.on('canceledit', function(grid, eOpts) {
            var record = eOpts.record,
                store = eOpts.store;

            if (!(record instanceof Ext.data.Model) || !(store instanceof Ext.data.Store)) {
                return;
            }
            if (record.raw.isNew) {
                store.remove(record);
            }
        });

        // set article's number and name when a article was selected
        me.articleNumberSearch.on('valueselect', function(field, value, hiddenValue, record) {
            var columns = me.cellEditing.editor.items.items;
            columns[2].setValue(record.get('number'));
            columns[3].setValue(record.get('name'));
        });
        me.articleNameSearch.on('valueselect', function(field, value, hiddenValue, record) {
            var columns = me.cellEditing.editor.items.items;
            columns[2].setValue(record.get('number'));
            columns[3].setValue(record.get('name'));
        });

        // hide and clear search fields when editing is canceled
        me.on('canceledit', function() {
            me.articleNumberSearch.getDropDownMenu().hide();
            me.articleNameSearch.getDropDownMenu().hide();
            me.articleNumberSearch.setValue('');
            me.articleNameSearch.setValue('');
        }, me);
    },

    /**
     * Helper method which creates the column model
     * for the article administration grid panel.
     *
     * @public
     * @return Array computed columns
     */
    createColumns: function() {
        var me = this, snippets = me.snippets;

        return [{
            header: '&#009868;',
            width: 24,
            hideable: false,
            renderer : me.renderSorthandleColumn
        }, {
            dataIndex: 'type',
            header: snippets.type,
            flex: 1,
            editor: {
                xtype: 'combobox',
                queryMode: 'local',
                allowBlank: false,
                valueField: 'value',
                displayField: 'name',
                store : me.typeStore,
                editable: false,
                listeners: {
                    change: function(combo, newValue, oldValue, eOpts) {
                        var editor = me.cellEditing,
                            columns = editor.editor.items.items,
                            hidden;

                        // If type is not 'fix' the is is not allowed to set article name/ordernumber
                        // So hide those fields
                        hidden =  newValue == 'fix';
                        columns[2].setVisible(hidden);
                        columns[3].setVisible(hidden);
                    }
                }
            },
            renderer: function(value, metaData, rowRecord, row, column, grid, view) {
                var me = this, record,
                    parent = me.up('newsletter-components-article');

                if (value === Ext.undefined) {
                    return value;
                }

                record =  parent.typeStore.findRecord('value', value);

                if (record instanceof Ext.data.Model) {
                    return record.get('name');
                } else {
                    return value;
                }
            }
        }, {
            dataIndex: 'ordernumber',
            header: snippets.ordernumber,
            flex: 2,
            editor: me.articleNumberSearch

        }, {
            dataIndex: 'name',
            header: snippets.name,
            flex: 2,
            editor: me.articleNameSearch
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
     * article are added to the article list.
     *
     * Creates new models based on the selected articles and
     * assigns them to the article store.
     *
     * @public
     */
    onAddArticleToGrid: function(grid, editor) {
        var me = this, store = me.articleStore;

        editor.cancelEdit();

        var count = store.getCount();
        var model = Ext.create('Shopware.apps.NewsletterManager.model.Article', {
            position: count,
            isNew: true,
            type: 'fix',
            name: '',
            ordernumber: ''
        });

        store.add(model);
        editor.startEdit(model, 1);

        // We need a defer due to early firing of the event
        Ext.defer(function() {
            me.refreshHiddenValue();
        }, 10);

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
     * Refactors the mapping field in the global record
     * which contains all article in the grid.
     *
     * Adds all articles to the article administration grid
     * when the user opens the component.
     *
     * @return void
     */
    getGridData: function() {
        var me = this,
            elementStore = me.getSettings('record').get('data'), articleList;

        Ext.each(elementStore, function(element) {
            if(element.key === 'article_data') {
                articleList = element;
                return false;
            }
        });

        if(articleList && articleList.value) {
            Ext.each(articleList.value, function(item) {
                me.articleStore.add(Ext.create('Shopware.apps.NewsletterManager.model.Article', item));
            });
        }
    },

    /**
     * Helper function to setup the article popup search
     * @param returnValue
     * @param hiddenReturnValue
     * @param name
     * @return Shopware.form.field.ArticleSearch
     */
    createArticleSearch: function(returnValue, hiddenReturnValue, name ) {
        return Ext.create('Shopware.form.field.ArticleSearch', {
            name: name,
            returnValue: returnValue,
            hiddenReturnValue: hiddenReturnValue,
            articleStore: Ext.create('Shopware.store.Article'),
            allowBlank: false,
            getValue: function() {
                return this.getSearchField().getValue();
            },
            setValue: function(value) {
                this.getSearchField().setValue(value);
            }
        });
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