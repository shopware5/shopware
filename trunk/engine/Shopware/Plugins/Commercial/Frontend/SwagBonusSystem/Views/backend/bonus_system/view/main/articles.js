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
 * @package    BonusSystem
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

//{namespace name=backend/bonus_system/view/main}
//{block name="backend/bonus_system/view/articles"}
Ext.define('Shopware.apps.BonusSystem.view.main.Articles', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.bonusSystem-main-articles',
    autoScroll: true,

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        toolbar: {
            addArticle: '{s name=articles/toolbar/add_article}Add article{/s}',
            delete: '{s name=articles/toolbar/delete}Delete selected articles{/s}'
        },
        tooltip: {
            openArticle: '{s name=articles/tooltip/open_article}Open article{/s}'
        },
        column: {
            ordernumber: '{s name=articles/column/ordernumber}Ordernumber{/s}',
            articleName: '{s name=articles/column/article_name}Article{/s}',
            requiredPoints: '{s name=articles/column/required_points}Required points{/s}',
            position: '{s name=articles/column/position}Position{/s}'
        }
    },

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();

        me.editor      = me.getRowEditorPlugin();
        me.plugins     = [ me.editor ];
        me.selModel    = me.getGridSelModel();
        me.columns     = me.getColumns();
        me.dockedItems = [ me.getToolbar(), me.getPagingbar() ];

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        var me = this;

        me.addEvents(
            /**
             * Event that will be fired when the user clicks the delete button in the toolbar
             *
             * @event deleteArticles
             * @param [array] records - The selected records
             */
            'deleteArticles',

            /**
             * @event saveArticle
             * @param [Ext.grid.Panel] view
             * @param [Ext.data.Model] record - The selected record
             */
            'saveArticle',

            /**
             * Fired when the user select an article in the suggest search.
             *
             * @event addArticle
             * @param [Ext.data.Model] recod -  The selected record
             */
            'addArticle',

            /**
             * Event will be fired when the user clicks the "open article" action column icon
             *
             * @event openArticle
             * @param [Ext.data.Model] - The record of the order position model
             */
            'openArticle'
        );
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel: function () {
        var me = this;

        var selModel = Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                selectionchange: function (sm, selections) {
                    me.deleteButton.setDisabled(selections.length == 0);
                }
            }
        });

        return selModel;
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this,
            actionColumItems = [];

        actionColumItems.push({
            iconCls: 'sprite-inbox',
            tooltip: me.snippets.tooltip.openArticle,
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.fireEvent('openArticle', record);
            }
        });

        var columns = [{
            header: me.snippets.column.ordernumber,
            dataIndex: 'ordernumber',
            flex: 1
        }, {
            header: me.snippets.column.articleName,
            dataIndex: 'articleName',
            flex: 1
        }, {
            header: me.snippets.column.requiredPoints,
            dataIndex: 'required_points',
            flex: 1,
            editor: {
                xtype: 'numberfield',
                allowDecimals: false,
                allowBlank: false,
                minValue: 0
            }
        }, {
            header: me.snippets.column.position,
            dataIndex: 'position',
            flex: 1,
            editor: {
                xtype: 'numberfield',
                allowDecimals: false,
                allowBlank: false,
                minValue: 0
            }
        }, {
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: actionColumItems.length * 26,
            items: actionColumItems
        }];

        return columns;
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this;

        me.deleteButton = Ext.create('Ext.Button', {
            iconCls:'sprite-minus-circle-frame',
            text: me.snippets.toolbar.delete,
            disabled: true,
            handler: function() {
                var selectionModel = me.getSelectionModel(),
                    records = selectionModel.getSelection();

                if (records.length > 0) {
                    me.fireEvent('deleteArticles', records);
                }
            }
        });

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui : 'shopware-ui',
            items: [
                me.deleteButton,
                { xtype: 'tbspacer', width: 12 },
                me.createToolBarArticleSearch(),
                '->'
            ]
        });

        return toolbar;
    },

    /**
     * Creates the article suggest search for the toolbar to add
     * a new article.
     * @return Shopware.form.field.ArticleSearch
     */
    createToolBarArticleSearch: function() {
        var me = this;

        //create the article search component
        me.articleSearch = Ext.create('Shopware.form.field.ArticleSearch', {
            name: 'number',
            fieldLabel: me.snippets.toolbar.addArticle,
            returnValue: 'name',
            hiddenReturnValue: 'number',
            width: 400,
            formFieldConfig: {
                labelWidth: 130,
                width: 400
            },
            searchScope: [ 'articles' ],
            listeners: {
                valueselect: function(field, name, number, record) {
                    if (record instanceof Ext.data.Model) {
                        me.fireEvent('addArticle', record);
                        me.articleSearch.getSearchField().reset();
                    }
                }
            }
        });

        return me.articleSearch;
    },

    /**
     * Creates pagingbar shown at the bottom of the grid
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function () {
        var pagingbar =  Ext.create('Ext.toolbar.Paging', {
            store: this.store,
            dock: 'bottom',
            displayInfo: true
        });

        return pagingbar;
    },

    /**
     * Creates rowEditor Plugin
     *
     * @return [Ext.grid.plugin.RowEditing]
     */
    getRowEditorPlugin: function() {
        var me = this;

        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            errorSummary: false,
            pluginId: 'myeditor',
            listeners: {
                edit: function(editor, e) {
                    me.fireEvent('saveArticle', me, e.record);
                }
            }
        });
    }
});
//{/block}
