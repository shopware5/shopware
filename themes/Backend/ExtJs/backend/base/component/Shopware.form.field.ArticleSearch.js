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
 * @package    Base
 * @subpackage Component
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/base/article_search}

/**
 * Shopware UI - Article Live Suggest Search
 *
 * Inspired by senchalabs's search component:
 * https://github.com/senchalabs/jsduck/blob/master/template/app/view/search/Container.js
 */

//{block name="backend/base/Shopware.form.field.ArticleSearch"}
Ext.define('Shopware.form.field.ArticleSearch',
/** @lends Ext.container.Container# */
{
    /**
     * Extends the default container to provide an
     * container for the different search components.
     * @string
     */
    extend: 'Ext.container.Container',

    /**
     * The Ext.container.Container.layout for the form panel's immediate child items.
     * @string
     */
    layout: 'anchor',

    /**
     * Defines alternate names for this class
     * @array
     */
    alternateClassName: [ 'Shopware.form.ArticleSearch', 'Shopware.ArticleSearch', 'Shopware.form.field.ProductSearch' ],

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     * @array
     */
    alias: [ 'widget.articlesearch', 'widget.articlesearchfield', 'widget.productsearchfield' ],

    /**
     * Basic CSS class for the component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'search-article-live',

    /**
     * List of classes that have to be loaded before instantiating this class
     * @array
     */
    requires: [
        'Ext.form.field.Trigger',
        'Ext.view.View',
        'Ext.form.field.Hidden',
        'Ext.XTemplate',
        'Shopware.apps.Base.store.Article',
        'Shopware.apps.Base.model.Article',
        'Ext.grid.Panel'
    ],

    /**
     * Default return value which will be set into the search field
     * if the user clicks on an entry in the drop down menu.
     * @string
     */
    returnValue: 'name',

    /**
     * Return value which will be set into an hidden input field
     * if the user clicks on an entry in the drop down menu.
     * @string
     */
    hiddenReturnValue: 'number',

    returnRecord: null,

    /**
     * Name attribute of the search field.
     * @string
     */
    searchFieldName: 'live-article-search',

    /**
     * Name attribute of the hidden field.
     * @string
     */
    hiddenFieldName: 'hidden-article-search',

    /**
     * Store for the drop down menu.
     *
     * @default null
     * @object
     */
    dropDownStore: null,

    /**
     * Store which holds the articles
     *
     * @default null
     * @object
     */
    store: null,

    /**
     * Offset for the drop down menu based on the position of the search field.
     * @array
     */
    dropDownOffset: [ 105, 8 ],

    /**
     * Change buffer size for the search field.
     * @integer
     */
    searchBuffer: 500,

    /**
     * True to allow selection of more than one item at a time, false to allow selection of only a single item at a time.
     * @boolean
     */
    multiSelect: false,

    /**
     * Store for selected articles.
     * @object
     */
    multiSelectStore: Ext.create('Ext.data.Store', {
        model: 'Shopware.apps.Base.model.Article'
    }),

    /**
     * Grid for the selected articles.
     *
     * @default null
     * @object
     */
    mulitSelectGrid: null,

    /**
     * Height of the selected article grid.
     * @integer
     */
    gridHeight: 200,

    /**
     * Position of the grid toolbar. "top" or "bottom" are available.
     * @string
     */
    gridToolbarDock: 'bottom',

    /**
     * Text for the confirm button in the multi select toolbar.
     * @string
     */
    confirmButtonText: '{s name=save_assigned_articles}Save assigned articles{/s}',

    /**
     * Text for the confirm button in the multi select toolbar.
     * @string
     */
    cancelButtonText: '{s name=reset_articles}Reset articles{/s}',

    /**
     * Separator for the returned values
     *
     * @string
     */
    separator: ';',

    /**
     * Contains the search scopes for the article search component.
     * Supports the following options:
     *  - Contains "articles" => search for normal articles
     *  - Contains "variants" => search for variant articles - DEPRECATED
     *  - Contains "configurator" => search for configurator articles
     *
     * Deprecated: "variants", to search for variant articles you can configure the store like this:
     *
     * Ext.create('Shopware.form.field.ArticleSearch', {
     *      name: 'articleNumber',
     *      returnValue: 'number',
     *      hiddenReturnValue: 'name',
     *      store: Ext.create('Shopware.apps.Base.store.Variant'),
     *      getValue: function() {
     *          return this.getSearchField().getValue();
     *      },
     *      setValue: function(value) {
     *          this.getSearchField().setValue(value);
     *      }
     * }
     *
     * Example: ['articles','variants'] => Selects normal articles and variant articles
     * Default: ['articles','variants','configurator']
     *
     * @array
     */
    searchScope: ['articles','variants','configurator'],

    /**
     * Form field configuration
     * @object
     */
    formFieldConfig: {},

    /**
     * @cfg { string } emptyText
     * Empty text for the search field
     */
    emptyText: '{s name=search_default_text}Search...{/s}',

    snippets: {
        assignedArticles: '{s name=assigned_articles}Assigned articles{/s}',
        articleName: '{s name=article_name}Article name{/s}',
        orderNumber: '{s name=ordernumber}Order number{/s}',
        dropDownTitle: '{s name=search_result/article}Article{/s}'
    },

    /**
     * Initializes the Live Article Search component
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();

        //maps the article store to the store attribute
        if (!(me.store instanceof Ext.data.Store)) {
            if (!(me.articleStore instanceof Ext.data.Store)) {
                me.store = Ext.create('Shopware.apps.Base.store.Article');
            } else {
                me.store = me.articleStore;
            }
        }

        //gets the dropDownStore name because the DropDownStore and the ArticleStore must be the same
        var dropDownStoreName = me.store.$className;

        // We need to filter the store on loading to prevent to show the first article in the store on startup
        me.dropDownStore = Ext.create(dropDownStoreName, {
            listeners: {
                single: true,
                load: function() {
                    me.loadArticleStore(me.store);
                }
            }
        });

        //article store passed to the component?
        if (Ext.isObject(me.store) && me.store.data.items.length > 0 ) {
            me.loadArticleStore(me.store);
        }

        if (Ext.isArray(me.searchScope) && me.searchScope.length > 0) {
            me.dropDownStore.getProxy().extraParams = {
                articles: Ext.Array.contains(me.searchScope, 'articles'),
                variants: Ext.Array.contains(me.searchScope, 'variants'),
                configurator: Ext.Array.contains(me.searchScope, 'configurator')
            };
        }

        me.hiddenField = me.createHiddenField();
        me.searchField = me.createSearchField();
        me.dropDownMenu = me.createDropDownMenu();
        me.items = [ me.hiddenField, me.searchField, me.dropDownMenu ];

        // Create an store and a grid for the selected articles
        if(!me.multiSelect) {
            delete me.multiSelectStore;
        } else {
            me.multiSelectGrid = me.createMultiSelectGrid();
            me.items.push(me.multiSelectGrid);
        }

        // Are we're having an store to preselect articles?
        if(me.articleStore && me.multiSelect) {
            me.multiSelectGrid.show();
        }
        me.dropDownStore.on('datachanged', me.onSearchFinish, me);

        me.callParent(arguments);
    },

    /**
     * Declares new events which will be fired by this component.
     *
     * @private
     * @return void
     */
    registerEvents: function() {
        this.addEvents(

            /**
             * Event will be fired when the user clicks the trigger button.
             *
             * @event reset
             * @param [object] this - Shopware.form.field.ArticleSearch
             * @param [object] triggerBtn - pressed Trigger button
             */
            'reset',

            /**
             * Will be fired when the search was successfull.
             *
             * @event search
             * @param [object] this - Shopware.form.field.ArticleSearch
             * @param [array] records - Array of the founded records.
             */
            'search',

            /**
             * Will be fired when the user selects an article in the drop down menu.
             *
             * @event valueselect
             * @param [object] this - Shopware.form.field.ArticleSearch
             * @param [string] value - Value of the Ext.form.field.Trigger
             * @param [string] hiddenValue - Value of the Ext.form.field.Hidden
             * @param [object] record - Selected record
             * @param [object] eOpts - Additional event parameters
             */
            'valueselect',

            /**
             * Will be fired when the user deletes an article from the
             * multiselect store. Note that is event will only be fired when
             * "multiSelect" is true.
             *
             * @event deleteArticle
             * @param [object] this - Shopware.form.field.ArticleSearch
             * @param [object] record - deleted record
             * @param [object] this.multiSelectStore - Ext.data.Store which buffers the selected articles
             * @param [object] grid - the associated Ext.grid.Panel
             */
            'deleteArticle',

            /**
             * Will be fired when the user clicks the "apply assigment" button
             * in the multi select toolbar. Note that is event will only be fired when
             * "multiSelect" is true.
             *
             * @event
             */
            'applyAssignment'
        );
    },

    /**
     * Creates the hidden field for the live search.
     *
     * @private
     * @return [object] input - created Ext.form.field.Hidden
     */
    createHiddenField: function() {
        var me = this,
            input = Ext.create('Ext.form.field.Hidden', {
            name: me.hiddenFieldName
        });
        return input;
    },

    /**
     * Helper method which returns the hidden field
     */
    getHiddenField: function() {
        return this.hiddenField || (this.hiddenField = this.createHiddenField());
    },

    /**
     * Creates the searchfield for the live search.
     *
     * @private
     * @return [object] input -  created Ext.form.field.Trigger
     */
    createSearchField: function() {
        var me = this;

        var fieldConfig = Ext.apply({
            componentLayout: 'textfield',
            triggerCls: 'reset',
            emptyText: me.emptyText,
            fieldLabel: (me.fieldLabel || undefined),
            cls:  Ext.baseCSSPrefix + 'search-article-live-field',
            name: me.searchFieldName,
            enableKeyEvents: true,
            anchor: (me.anchor || undefined),
            onTriggerClick: function() {
                this.reset();
                this.focus();
                this.setHideTrigger(true);
                me.dropDownMenu.hide();
                me.fireEvent('reset', me, this);
            },
            hideTrigger: true,
            listeners: {
                scope: me,
                keyup: me.onSearchKeyUp,
                blur: me.onSearchBlur
            }
        }, me.formFieldConfig);

        var input = Ext.create('Ext.form.field.Trigger', fieldConfig);
        return input;
    },

    /**
     * Event listener function of the search field. Fired when the search field lost the focus.
     */
    onSearchBlur: function() {
        var me = this;
        Ext.defer(function() {
            if (me.dropDownMenu) {
                me.dropDownMenu.hide();
            }
        }, 1000);
    },

    /**
     * Helper method which returns the search field.
     *
     * @public
     * @return [object] this.searchField - the Ext.form.field.Trigger which reflects the search field.
     */
    getSearchField: function() {
        return this.searchField || (this.searchField = this.createSearchField())
    },

    /**
     * Creates the drop down menu which represents the
     * search result.
     *
     * @private
     * @return [object] view - created Ext.view.View
     */
    createDropDownMenu: function() {
        var me = this,
            view = Ext.create('Ext.view.View', {
            floating: true,
            autoShow: false,
            autoRender: true,
            hidden: true,
            shadow: false,
            width: 222,
            toFrontOnShow: true,
            focusOnToFront: false,
            store: me.dropDownStore,
            cls:  Ext.baseCSSPrefix + 'search-article-live-drop-down',
            overItemCls: Ext.baseCSSPrefix + 'drop-down-over',
            selectedItemCls: Ext.baseCSSPrefix + 'drop-down-over',
            trackOver: true,
            itemSelector: 'div.item',
            singleSelect: true,
            listeners: {
                scope: me,
                itemclick: function(view, record) {
                    me.onSelectArticle(view, record);
                }
            },
            tpl: me.createDropDownMenuTpl()
        });

        return view;
    },

    /**
     * Helper method which returns the drop down menu.
     *
     * @public
     * @return [object] this.dropDownMenu - the Ext.view.View which reflects the search result.
     */
    getDropDownMenu: function() {
        return this.dropDownMenu || (this.dropDownMenu = this.createDropDownMenu());
    },

    /**
     * Creates the template for the search result.
     *
     * @private
     * @return [object] created Ext.XTemplate
     */
    createDropDownMenuTpl: function() {
        var me = this;

        return new Ext.XTemplate(
            '<div class="header">',
                '<div class="header-inner">',
                    '<div class="arrow">&nbsp;</div>',
                    '<span class="title">',
                    me.snippets.dropDownTitle,
                    '</span>',
                '</div>',
            '</div>',
            '<div class="content">',
                '{literal}<tpl for=".">',
                    '<div class="item">',
                        '<strong class="name">{name}</strong>',
                        '<span class="ordernumber">{number}</span>',
                    '</div>',
                '</tpl>{/literal}',
            '</div>'
        );
    },

    /**
     * Creates an grid panel for the select articles, if "multiSelect" is true.
     *
     * @private
     * @return [object] grid - generated Ext.grid.Panel
     */
    createMultiSelectGrid: function() {
        var me = this, grid;

        me.multiSelectToolbar = me.createMultiSelectGridToolbar();

        var grid = Ext.create('Ext.grid.Panel', {
            store: me.multiSelectStore,
            title: me.snippets.assignedArticles,
            selModel: 'rowmodel',
            autoScroll: true,
            columns: me.createMultiSelectGridColumns(),
            hidden: (me.multiSelectStore.getCount() ? false : true),
            anchor: (me.anchor || undefined),
            height: me.gridHeight,
            dockedItems: [ me.multiSelectToolbar ]
        });

        return grid;
    },

    /**
     * Helper method which returns the drop down menu.
     *
     * @public
     * @return [object] this.dropDownMenu - the Ext.view.View which reflects the search result.
     */
    getMultiSelectGrid: function() {
        return this.multiSelectGrid || (this.multiSelectGrid = this.createMultiSelectGrid());
    },

    /**
     * Creates the column model for the multi select grid panel.
     *
     * @private
     * @return [array] column model
     */
    createMultiSelectGridColumns: function() {
        var me = this;

        return [{
            header: me.snippets.articleName,
            dataIndex: me.returnValue,
            flex: 2
        }, {
            header: me.snippets.orderNumber,
            dataIndex: me.hiddenReturnValue,
            flex: 1
        }, {
            xtype: 'actioncolumn',
            width: 30,
            items: [{
                iconCls: 'sprite-minus-circle',
                handler: function(grid, rowIndex) {
                    me.onDeleteArticle(rowIndex);
                }
            }]
        }];
    },

    /**
     * Creates a toolbar for the multi select grid.
     *
     * @private
     * @return [object] toolbar - generated Ext.toolbar.Toolbar
     */
    createMultiSelectGridToolbar: function() {
        var me = this, toolbar;

        toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: me.gridToolbarDock,
            ui: 'shopware-ui',
            items: ['->', {
                text: me.cancelButtonText,
                handler: function() {
                    me.getMultiSelectGrid().hide();
                    me.multiSelectStore.removeAll();
                }
            }, {
                text: me.confirmButtonText,
                handler: function() {

                    // Needs to call in an inline function to prevent the scope changing
                    me.getMultiSelectValues();
                }

            }]
        });

        return toolbar;
    },

    /**
     * Helper method which returns the grid toolbar.
     *
     * @public
     * @return [object] this.multiSelectToolbar - the Ext.toolbar.Toolbar which is rendered in the grid panel.
     */
    getMultiSelectGridToolbar: function() {
        return this.multiSelectToolbar || (this.multiSelectToolbar = this.createMultiSelectGridToolbar());
    },

    /**
     * Event listener method which will be fired if
     * the user types into the search field.
     *
     * Shows the trigger button and starts the search.
     *
     * @event keyup
     * @param [object] el - Ext.form.field.Trigger which has fired the event
     * @param [object] event - Ext.EventObject
     * @return void
     */
    onSearchKeyUp: function(el, event) {
        var me = this;

        el.setHideTrigger(el.getValue().length === 0);
        clearTimeout(me.searchTimeout);

        // Check if we've a value and the user did press the ESC key
        if(event.keyCode === Ext.EventObject.ESC || !el.value) {
            event.preventDefault();
            el.setValue('');
            me.dropDownStore.filters.clear();
            me.getDropDownMenu().hide();
            return false;
        }

        var dropdown = me.getDropDownMenu(),
            selModel = dropdown.getSelectionModel(),
            record = selModel.getLastSelected(),
            curIndex = me.dropDownStore.indexOf(record),
            lastIndex = me.dropDownStore.getCount() - 1;


        // Keyboard up pressed
        if(event.keyCode === Ext.EventObject.UP) {
            if(curIndex === undefined) {
                selModel.select(0);
            } else {
                selModel.select(curIndex === 0 ? lastIndex : (curIndex - 1));
            }
        }

        // Keyboard down pressed
        else if(event.keyCode === Ext.EventObject.DOWN) {
            if(curIndex == undefined) {
                selModel.select(0);
            } else {
                selModel.select(curIndex === lastIndex ? 0 : (curIndex + 1));
            }
        }

        // Keyboard enter pressed
        else if(event.keyCode === Ext.EventObject.ENTER) {
            event.preventDefault();
            record && me.onSelectArticle(null, record);
        }

        // No special key was pressed, start searching...
        else {
            me.searchTimeout = setTimeout(function() {
                me.dropDownStore.filters.clear();
                me.dropDownStore.filter('free', '%' + el.value + '%');
            }, me.searchBuffer);
        }
    },

    /**
     * Event listener method which will be fired when the store is successfully
     * filtered.
     *
     * Refreshes the search result drop down menu and aligns it properly.
     *
     * @public
     * @event datachanged
     * @param [object] store - passed Shopware.apps.Base.store.Article
     * @return void
     */
    onSearchFinish: function(store) {
        var records = store.data.items,
            me = this;

        if(records.length === 0) {
            me.getDropDownMenu().hide();
        } else {
            me.fireEvent('search', me, records);
            me.getDropDownMenu().show();
            me.getDropDownMenu().alignTo(me.getSearchField().getEl(), 'bl', me.dropDownOffset);
            me.getDropDownMenu().getSelectionModel().select(0);
        }
    },

    /**
     * Event listener method which will be fired when the user selects
     * an article in the drop down menu.
     *
     * @event itemclick
     * @public
     * @return void
     */
    onSelectArticle: function(view, record) {
        var me = this;

        if(!me.multiSelect) {
            me.getSearchField().setValue(record.get(me.returnValue));
            me.getHiddenField().setValue(record.get(me.hiddenReturnValue));
            me.returnRecord = record;
            me.getDropDownMenu().hide();
        } else {
            if(me.getMultiSelectGrid().isHidden()) {
                me.getMultiSelectGrid().show();
            }
            delete record.internalId;
            me.multiSelectStore.add(record);
            me.getDropDownMenu().getSelectionModel().deselectAll();
        }
        me.fireEvent('valueselect', me, record.get(me.returnValue), record.get(me.hiddenReturnValue), record);
    },

    /**
     * Event listener method which will be fired when the user clicks on the "delete article"
     * grid column.
     *
     * @event click
     * @param [integer] rowIndex - Index of the clicked row
     */
    onDeleteArticle: function(rowIndex, silent) {
        var me = this,
            grid = me.getMultiSelectGrid(),
            store = me.multiSelectStore,
            record = store.getAt(rowIndex);

        silent = silent || false;

        store.remove(record);
        if(!store.getCount()) {
            grid.hide();
        }

        if(!silent) {
            me.fireEvent('deleteArticle', me, record, store, grid);
        }
    },

    /**
     * Helper method which will be triggered when the user clicks the "apply
     * assignment" button in the multi select grid toolbar.
     *
     * Note that this method sanitizes the selected articles and just fires an event.
     *
     * @private
     */
    getMultiSelectValues: function() {
        var me = this,
            store = me.multiSelectStore,
            records = store.data.items,
            returnValue = '',
            hiddenReturnValue = '';

        Ext.each(records, function(record) {
            returnValue += record.get(me.returnValue) + me.separator;
            hiddenReturnValue += record.get(me.hiddenReturnValue) + me.separator;
        });
        returnValue = returnValue.substring(0, returnValue.length - 1);
        hiddenReturnValue = hiddenReturnValue.substring(0, hiddenReturnValue.length - 1);

        me.fireEvent('applyAssignment', me, returnValue, hiddenReturnValue, records, store);
    },

    /**
     * Attempts to destroy and then remove a set of named properties of the passed object.
     *
     * @public
     * @return void
     */
    destroy: function() {
        Ext.destroyMembers(this,  'mulitSelectGrid',  'hiddenField', 'searchField', 'dropDownMenu', 'multiSelectToolbar');
    },

    /**
     * Helper method which loads a store into the grid if the parameter this.article is
     * passed to the constructor of this component.
     *
     * @public
     * @param [object] store - Ext.data.Store which contains preselected articles.
     * @return void
     */
    loadArticleStore: function(store) {
        var me = this;
        Ext.each(store.data.items, function(record) {
            delete record.internalId;
            me.multiSelectStore.add(record);
        });
        return true;
    },

    /**
     * Helper methiod which returns the multi select article store.
     *
     * @public
     * @return [object] store - Ext.data.Store
     */
    getArticleStore: function() {
        return this.multiSelectStore
    },

    /**
     * Resets the component
     */
    reset: function () {
        this.searchField.reset();
    }
});
//{/block}
