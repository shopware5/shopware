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
 * @package    Article
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page - Sidebar
 * The similar component contains the configuration elements for the similar article relations.
 * The accessory component extends this component, because these both components has the same logic and elements.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/detail/sidebar/similar"}
Ext.define('Shopware.apps.Article.view.detail.sidebar.Similar', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.form.Panel',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-sidebar-similar',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-sidebar-similar',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title:'{s name=detail/sidebar/similar/title}Similar articles{/s}',
        notice:'{s name=detail/sidebar/similar/notice}At this point you have the option of linking other articles with the current article. The linked articles are automatically displayed on the article detail page.{/s}',
        articleSearch:'{s name=detail/sidebar/similar/article_search}Article{/s}',
        assignment: {
            field: '{s name=detail/sidebar/similar/assignment_field}Assignment{/s}',
            box: '{s name=detail/sidebar/similar/assignment_box}Assign each other{/s}'
        },
        button:'{s name=detail/sidebar/similar/button}Add article{/s}',
        gridTitle:'{s name=detail/sidebar/similar/grid_title}Assigned similar articles{/s}',
        name:'{s name=detail/sidebar/similar/name}Article name{/s}',
        edit:'{s name=detail/sidebar/similar/edit}Edit entry{/s}',
        delete:'{s name=detail/sidebar/similar/delete}Remove entry{/s}'
    },

    bodyPadding: 10,
    autoScroll: true,

    /**
     * Helper property which contains the name of the add event which fired when the user
     * clicks the button of the form panel
     */
    addEvent: 'addSimilarArticle',

    /**
     * Helper property which contains the name of the remove event which fired when the user
     * clicks the action column of the grid panel
     */
    removeEvent: 'removeSimilarArticle',

    listingName: 'similar-listing',

    /**
	 * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
	 *
	 * @return void
	 */
    initComponent:function () {
        var me = this;
        me.title = me.snippets.title;
        me.items = me.createElements();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
    	this.addEvents(
    		/**
    		 * Event will be fired when the user want to add a similar article
    		 *
    		 * @event
    		 */
    		'addSimilarArticle',

            /**
             * Event will be fired when the user want to remove an assigned similar article
             *
             * @event
             */
            'removeSimilarArticle'
    	);
    },

    /**
     * Creates the elements for the similar article panel.
     * @return array
     */
    createElements: function() {
        var me = this;

        me.noticeContainer = me.createNoticeContainer();
        me.formPanel = me.createFormPanel();
        me.articleGrid = me.createGrid();

        return [ me.noticeContainer, me.formPanel, me.articleGrid ];
    },

    /**
     * Creates the notice container for the similar articles panel.
     * @return Ext.container.Container
     */
    createNoticeContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            cls: Ext.baseCSSPrefix + 'global-notice-text',
            html: me.snippets.notice
        });
    },

    /**
     * Creates the form field set for the similar article panel. The form panel is used to
     * edit or add new similar articles to the article on the detail page.
     * @return Ext.form.FieldSet
     */
    createFormPanel: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            padding: 10,
            defaults: {
                labelWidth: 120,
                anchor: '100%'
            },
            items: me.createFormItems()
        });
    },

    /**
     * Creates the form items.
     * @return
     */
    createFormItems: function() {
        var me = this;
        me.articleSearch = me.createArticleSearch();

        return [
            me.articleSearch,
            {
                xtype: 'checkbox',
                name: 'cross',
                fieldLabel: me.snippets.assignment.field,
                boxLabel: me.snippets.assignment.box,
                inputValue: true,
                uncheckedValue: false
            },
            me.createAddButton()
        ]
    },

    /**
     * Creates the add button for the form Panel.
     * @return
     */
    createAddButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            cls: 'small secondary',
            text: me.snippets.button,
            handler: function() {
                me.fireEvent(me.addEvent, me, me.articleGrid, me.articleSearch);
            }
        });
    },

    /**
     * Creates the article live suggest search field.
     * @return Shopware.form.field.ArticleSearch
     */
    createArticleSearch: function() {
        var me = this;

        return Ext.create('Shopware.form.field.ArticleSearch', {
            name: 'number',
            fieldLabel: me.snippets.articleSearch,
            returnValue: 'name',
            hiddenReturnValue: 'number',
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
     * Creates the grid panel for the already assigned similar articles. The panel has
     * an edit and delete action column to remove or edit the assigned similar articles.
     * @return Ext.grid.Panel
     */
    createGrid: function() {
        var me = this;

        return Ext.create('Ext.grid.Panel', {
            title: me.snippets.gridTitle,
            cls: Ext.baseCSSPrefix + 'free-standing-grid',
            store: me.gridStore,
            name: me.listingName,
            height: 150,
            maxHeight: 150,
            minHeight: 150,
            columns: [
                {
                    header: me.snippets.articleSearch,
                    dataIndex: 'number',
                    flex: 1
                }, {
                    header: me.snippets.name,
                    dataIndex: 'name',
                    flex: 1
                }, {
                    xtype: 'actioncolumn',
                    width: 30,
                    items: [
                        {
                            iconCls: 'sprite-minus-circle-frame',
                            tooltip: me.snippets.delete,
                            handler: function (view, rowIndex, colIndex, item, opts, record) {
                                me.fireEvent(me.removeEvent, view, record);
                            }
                        }
                    ]
                }
            ]
        });
    }
});
//{/block}
