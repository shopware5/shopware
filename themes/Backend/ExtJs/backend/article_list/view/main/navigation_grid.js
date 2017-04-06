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
 * shopware AG (c) 2013. All rights reserved.
 */

//{namespace name=backend/article_list/main}
//{block name="backend/article_list/view/main/navigation_grid"}
Ext.define('Shopware.apps.ArticleList.view.main.NavigationGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.multi-edit-navigation-grid',

    /**
     * Do not show the grid's column headers
     */
    hideHeaders: true,

    border: 0,

    snippets: {
        search: '{s name=search}Search{/s}'
    },

    initComponent: function () {
        var me = this;

        me.toolTipTemplate = "{s name=navigation/filter/tooltip}<b>Description:</b><br>[0]{/s}"; //<br><br><b>Abfrage:</b><br>[1]

        me.columns = me.getColumns();

        me.features = me.getFeatures();

        me.tbar = me.getToolbar();

        me.addEvents(
            'loadFilter',
            'deleteFilter',
            'toggleFavorite'
        );

        me.registerEventHandlers();

        me.callParent(arguments);
    },


    /**
     * Register events
     */
    registerEventHandlers: function () {
        var me = this;

        me.on('cellclick', function (grid, td, cellIndex, record, tr, rowIndex, e, eOpts) {
            if (cellIndex != 1) {
                return;
            }
            me.fireEvent('loadFilter', record);
        });
    },

    /**
     * Return the columns of the grid
     */
    getColumns: function () {
        var me = this;

        return [
            me.getFavoriteActionColumn(),
            me.getLabelColumn(),
            me.getOperationActionColumn()
        ];
    },

    /**
     *  Add grouping feature
     */
    getFeatures: function () {
        return [
            {
                ftype: 'grouping',
                groupHeaderTpl: '{literal}{name} ({rows.length}){/literal}'
            }
        ];
    },

    /**
     *  Get the column which shows the label of each filter
     */
    getLabelColumn: function () {
        var me = this;

        return {
            flex: 1,
            dataIndex: 'name',
            allowHtml: true,
            renderer: function (value, meta, record) {
                meta.tdAttr = 'data-qtip="' + Ext.String.format(me.toolTipTemplate, record.get('description').replace(/"/g, ''), record.get('filterString').replace(/"/g, '')) + '"';

                return value;
            }
        };
    },

    /**
     *  Get column which shows the button for (un)staring a filter
     */
    getFavoriteActionColumn: function () {
        var me = this;

        return {
            xtype: 'actioncolumn',
            width: 25,
            items: [
                {
                    iconCls: 'sprite-star-empty',
                    tooltip: '{s name=makeFavorite}Mark as favorite{/s}',
                    /*{if {acl_is_allowed privilege=editFilters}}*/
                    handler: function (view, rowIndex, colIndex, item) {
                        me.fireEvent('toggleFavorite', rowIndex);
                    },
                    /*{/if}*/
                    getClass: function (value, metaData, record) {
                        if (record.get('isFavorite')) {
                            return 'x-hide-display';
                        }
                    }
                },
                {
                    iconCls: 'sprite-star',
                    tooltip: '{s name=undoFavorite}Unmark as favorite{/s}',
                    /*{if {acl_is_allowed privilege=editFilters}}*/
                    handler: function (view, rowIndex, colIndex, item) {
                        me.fireEvent('toggleFavorite', rowIndex);
                    },
                    /*{/if}*/
                    getClass: function (value, metaData, record) {
                        if (!record.get('isFavorite')) {
                            return 'x-hide-display';
                        }
                    }
                }

            ]
        };
    },

    /**
     *  Get column for operations like edit / delete filter
     */
    getOperationActionColumn: function () {
        var me = this;

        return {
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: 50,
            items: [
                {
                    /*{if {acl_is_allowed privilege=editFilters}}*/
                    iconCls: 'sprite-pencil',
                    action: 'editFilter',
                    tooltip: '{s name=editFilter}Edit filter{/s}',
                    handler: function (view, rowIndex, colIndex, item) {
                        me.fireEvent('editFilter', rowIndex);
                    }
                    /*{/if}*/
                },
                {
                    /*{if {acl_is_allowed privilege=deleteFilters}}*/
                    iconCls: 'sprite-minus-circle-frame',
                    action: 'deleteFilter',
                    tooltip: '{s name=deleteFilter}Delete filter{/s}',
                    handler: function (view, rowIndex, colIndex, item, e) {
                        me.fireEvent('deleteFilter', rowIndex);
                    }
                    /*{/if}*/
                }
            ]
        };
    },

    /**
     * Creates the grid toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function () {
        var me = this, buttons = [];

        /*{if {acl_is_allowed privilege=createFilters}}*/
        buttons.push({
            xtype: 'button',
            text: '{s name=addFilter}Add filter{/s}',
            name: 'add',
            action: 'addFilter',
            cls: 'small secondary',
            flex: 1
//            iconCls: 'sprite-plus-circle-frame'
        });
        /*{/if}*/
        buttons.push({
            xtype: 'tbfill'
        });
        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: buttons
        });
    }
});
//{/block}
