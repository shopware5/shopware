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

/**
 *
 */
//{namespace name=backend/article_list/main}
//{block name="backend/article_list/controller/main"}
Ext.define('Shopware.apps.ArticleList.controller.Main', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'grid', selector: 'multi-edit-main-grid' },
        { ref: 'pagingToolBar', selector: 'multi-edit-main-grid pagingtoolbar' },
        { ref: 'navigationGrid', selector: 'multi-edit-navigation-grid' },
        { ref: 'categoryTree', selector: 'multi-edit-category-tree treepanel' }
    ],

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    lastSearchFilter: '',

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init: function () {
        var me = this,
            grammarLoaded = false,
            modelLoaded = false;

        me.loadDetailModelFields();

        me.control({
            'multi-edit-main-grid': {
                search: me.onSearch
            }
        });

        me.subApplication.on('grammarProcessed', function () {
            grammarLoaded = true;

            if (modelLoaded) {
                me.loadDefaultStore();
            }
        });

        me.subApplication.on('modelCreated', function () {
            modelLoaded = true;

            if (grammarLoaded) {
                me.loadDefaultStore();
            }
        });

        me.callParent();
    },

    /**
     * Will load the articleStore as soon as all preconditions are met
     */
    loadDefaultStore: function () {
        var me = this,
            selection,
            name;

        selection = me.getCategoryTree().getSelectionModel();
        selection.select(selection.getStore().first());

        name = me.getController('CategoryFilter').getFilterNameByConfig(false, false);

        me.getController('Suggest').loadFilter(
            'ISMAIN',
            name
        );
    },

    /**
     * Will load the detail model fields with an AJAX-request. Will then generate
     * the corresponding model on the fly => createDetailModel
     */
    loadDetailModelFields: function () {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="ArticleList" action = "columnConfig"}',
            params: {
                resource: 'product'
            },
            method: 'GET',
            success: function (response, request) {
                var result = Ext.JSON.decode(response.responseText);

                if (!result) {
                    me.showError(response.responseText);
                } else if (result.success) {
                    me.columnConfigArray = result.data;
                    me.columnConfigObject = me.getColumnConfigObject(me.columnConfigArray);
                    me.createDetailModel(result.data);
                    me.subApplication.fireEvent('modelCreated');
                    return;
                }
                me.showError(result.message);

            },
            failure: function (response, request) {
                if (response.responseText) {
                    me.showError(response.responseText);
                } else {
                    me.showError('{s name=unknownError}An unknown error occurred, please check your server logs{/s}');
                }
            }
        });
    },

    /**
     * Build a columnConfig object from an array in order to access the config by column name
     *
     * @param columnConfig
     * @returns Object
     */
    getColumnConfigObject: function (columnConfig) {
        var i, column, columnConfigLength = columnConfig.length,
            columnConfigObject = { };

        for (i = 0; i < columnConfigLength; i++) {
            column = columnConfig[i];
            columnConfigObject[column.alias] = column;
        }

        return columnConfigObject;
    },

    /**
     * Return the column config for a given column alias.
     *
     * @param columnAlias
     * @returns Object
     */
    getConfigForColumn: function (columnAlias) {
        var me = this;

        columnAlias = columnAlias.replace('.', '_');
        columnAlias = columnAlias.charAt(0).toUpperCase() + columnAlias.slice(1);

        return me.columnConfigObject[columnAlias];
    },

    /**
     * Creates the detail model dynamically.
     * @param data
     */
    createDetailModel: function (data) {
        var me = this,
                fields = [
            { name: 'imageSrc', type: 'string' },
            { name: 'hasConfigurator', type: 'boolean' },
            { name: 'hasCategories', type: 'boolean' },
            { name: 'Detail_additionalText_dynamic', type: 'string' }
        ];

        for (var i = 0; i < data.length; i++) {
            var column = data[i];
            if (!column.allowInGrid) {
                continue;
            }
            fields.push({
                name: column.alias,
                useNull: column.nullable
            });
        }

        Ext.define('Shopware.apps.ArticleList.model.Detail', {
            extend: 'Ext.data.Model',
            fields: fields,

            /**
             * Configure the data communication
             * @object
             */
            proxy: {
                /**
                 * Set proxy type to ajax
                 * @string
                 */
                type: 'ajax',

                /**
                 * Configure the url mapping for the different
                 * store operations based on
                 * @object
                 */
                api: {
                    create: '{url controller="ArticleList" action="saveSingleEntity"}',
                    update: '{url controller="ArticleList" action="saveSingleEntity"}',
                    destroy: '{url controller="ArticleList" action="deleteProduct"}'
                },

                /**
                 * Configure the data reader
                 * @object
                 */
                reader: {
                    type: 'json',
                    root: 'data',
                    totalProperty: 'total'
                }
            }
        });

        // Bind stores after the detailModel was defined!
        me.bindStores();
    },

    /**
     * Will create the main window and bind out stores to the corresponding grids
     */
    bindStores: function () {
        var me = this;

        me.mainWindow = me.getView('main.Window').create({
            columnConfig: me.columnConfigArray
        });
        me.subApplication.setAppWindow(me.mainWindow);

        var grid = me.getGrid(),
                navigationGrid = me.getNavigationGrid(),
                toolbar = me.getPagingToolBar();

        me.subApplication.articleGrid = grid;

        // Bind main grid store.
        me.subApplication.articleStore = me.getStore('Shopware.apps.ArticleList.store.Detail');
        grid.reconfigure(me.subApplication.articleStore);
        toolbar.bindStore(me.subApplication.articleStore);
        toolbar.down('combo').setValue(me.subApplication.articleStore.pageSize + ' {s name=pagingCombo/products}products{/s}');

        // Bind filter store (shown in the navigation)
        me.subApplication.filterStore = me.getStore('Shopware.apps.ArticleList.store.Filter').load();
        navigationGrid.bindStore(me.subApplication.filterStore);
    },

    /**
     * Convenience method to show a sticky growl message
     *
     * @param message
     */
    showError: function (message) {
        Shopware.Notification.createStickyGrowlMessage({
            title: '{s name=error}Error{/s}',
            text: message,
            log: true
        },
        'ArticleList');
    },

    /**
     * Triggered for a search
     *
     * @param searchTerm
     */
    onSearch: function (searchTerm) {
        var me = this,
                filter,
                result,
                currentFilter = me.subApplication.currentFilterString;

        if (me.lastSearchFilter.length > 0) {
            currentFilter = currentFilter.replace(me.lastSearchFilter, '');
        }

        if (searchTerm && searchTerm.length > 0) {
            searchTerm = me.filterSearchTerm(searchTerm);

            filter = '(article.name = "%[0]%" OR detail.number = "%[0]%" OR supplier.name = "%[0]%") AND ';
            filter = Ext.String.format(filter, searchTerm);
        } else {
            filter = '';
        }

        result = me.getController('Suggest').loadFilter(filter + currentFilter, null, {
            query: searchTerm
        });
        if (result) {
            me.lastSearchFilter = filter;
        }
    },

    /**
     * Removes quotes from searchTerm that will break the parser/lexer.
     *
     * @param searchTerm
     * @returns string
     */
    filterSearchTerm: function (searchTerm) {
        // remove leading "
        searchTerm = searchTerm.replace(/^"/, "");

        // remove trailing "
        searchTerm = searchTerm.replace(/"$/, "");

        // replace " by _ wildcard
        searchTerm = searchTerm.replace(/"/g, "_");

        return searchTerm;
    }
});
//{/block}
